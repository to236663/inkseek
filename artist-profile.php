<?php
require_once __DIR__ . '/connect.php';
session_start();

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function aspect_ratio_class($aspectRatio)
{
    return match ((string)$aspectRatio) {
        '2:3' => 'ratio-2-3',
        '3:4' => 'ratio-3-4',
        default => 'ratio-1-1',
    };
}

// Get profile_id from URL or use logged-in user's profile
$profile_id = $_GET['profile_id'] ?? null;
$artist_id = null;
$account_id = null;
$is_own_profile = false;
$reviewError = '';
$openReviewOverlay = false;
$reviewDraftContent = '';
$reviewDraftRating = 0;
$isFollowing = false;

// If no profile_id, redirect unauthenticated users to login
if ($profile_id === null && (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true)) {
    header('Location: login.php');
    exit();
}

// Get artist account_id from profile_id
if ($profile_id) {
    $stmt = $mysqli->prepare("SELECT account_id FROM artist_profiles WHERE profile_id = ?");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $account_id = $row['account_id'] ?? null;
    $stmt->close();
} else {
    // If viewing own profile, check if user is artist
    if ($_SESSION['logged_in_access_level'] === 'artist') {
        $account_id = $_SESSION['logged_in_account_id'];
        $is_own_profile = true;

        // Get artist_profile_id from session
        $profile_id = $_SESSION['artist_profile_id'] ?? null;
    } else {
        header('Location: login.php');
        exit();
    }
}

$is_own_profile = isset($_SESSION['logged_in_account_id']) && $_SESSION['logged_in_account_id'] == $account_id;

$profileQuery = '';
if ($profile_id !== null && ctype_digit((string)$profile_id)) {
    $profileQuery = '?profile_id=' . (int)$profile_id;
}

// Check if logged-in user is following this artist
if (
    !$is_own_profile
    && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true
    && isset($_SESSION['logged_in_account_id'])
    && $account_id
    && isset($mysqli) && $mysqli instanceof mysqli
) {
    $followCheckStmt = $mysqli->prepare(
        'SELECT 1 FROM account_follows WHERE follower_account_id = ? AND following_account_id = ? LIMIT 1'
    );
    if ($followCheckStmt) {
        $viewerId = (int)$_SESSION['logged_in_account_id'];
        $followTargetAccountId = (int)$account_id;
        $followCheckStmt->bind_param('ii', $viewerId, $followTargetAccountId);
        $followCheckStmt->execute();
        $isFollowing = $followCheckStmt->get_result()->fetch_row() !== null;
        $followCheckStmt->close();
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['toggle_follow'])
    && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true
    && isset($_SESSION['logged_in_account_id'])
) {
    $followerId = (int)$_SESSION['logged_in_account_id'];
    $followTargetId = filter_var(
        $_POST['target_account_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    if ($followTargetId && $followerId !== (int)$followTargetId && isset($mysqli) && $mysqli instanceof mysqli) {
        $followTargetIdInt = (int)$followTargetId;
        $ckStmt = $mysqli->prepare(
            'SELECT 1 FROM account_follows WHERE follower_account_id = ? AND following_account_id = ? LIMIT 1'
        );
        if ($ckStmt) {
            $ckStmt->bind_param('ii', $followerId, $followTargetIdInt);
            $ckStmt->execute();
            $alreadyFollowing = $ckStmt->get_result()->fetch_row() !== null;
            $ckStmt->close();
            if ($alreadyFollowing) {
                $muteStmt = $mysqli->prepare(
                    'DELETE FROM account_follows WHERE follower_account_id = ? AND following_account_id = ?'
                );
                if ($muteStmt) {
                    $muteStmt->bind_param('ii', $followerId, $followTargetIdInt);
                    $muteStmt->execute();
                    $muteStmt->close();
                }
            } else {
                $addStmt = $mysqli->prepare(
                    'INSERT IGNORE INTO account_follows (follower_account_id, following_account_id) VALUES (?, ?)'
                );
                if ($addStmt) {
                    $addStmt->bind_param('ii', $followerId, $followTargetIdInt);
                    $addStmt->execute();
                    $addStmt->close();
                }
            }
        }
    }
    header('Location: artist-profile.php' . $profileQuery);
    exit();
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['upload_portfolio'])
    && $is_own_profile
    && isset($_SESSION['logged_in'])
    && $_SESSION['logged_in'] === true
) {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $tags_input = trim((string)($_POST['tags'] ?? ''));
    $simulated_image_path = 'images/tattoos/uploadsim.jpg';

    if ($title !== '') {
        $insert_tattoo_stmt = $mysqli->prepare(
            "INSERT INTO tattoos (artist_id, image_path, title, description, aspect_ratio) VALUES (?, ?, ?, ?, ?)"
        );

        if ($insert_tattoo_stmt) {
            $aspect_ratio = '1:1';
            $insert_tattoo_stmt->bind_param(
                'issss',
                $account_id,
                $simulated_image_path,
                $title,
                $description,
                $aspect_ratio
            );

            if ($insert_tattoo_stmt->execute()) {
                $tattoo_id = (int)$insert_tattoo_stmt->insert_id;
                $raw_tags = array_filter(array_map('trim', explode(',', $tags_input)));
                $unique_tags = array_values(array_unique($raw_tags));

                foreach ($unique_tags as $tag_name) {
                    $upsert_tag_stmt = $mysqli->prepare(
                        "INSERT INTO style_tags (tag_name) VALUES (?) ON DUPLICATE KEY UPDATE tag_id = LAST_INSERT_ID(tag_id)"
                    );

                    if (!$upsert_tag_stmt) {
                        continue;
                    }

                    $upsert_tag_stmt->bind_param('s', $tag_name);

                    if ($upsert_tag_stmt->execute()) {
                        $style_tag_id = (int)$mysqli->insert_id;
                        $link_tag_stmt = $mysqli->prepare(
                            "INSERT IGNORE INTO tattoo_style_tag (tattoo_id, style_tag_id) VALUES (?, ?)"
                        );

                        if ($link_tag_stmt) {
                            $link_tag_stmt->bind_param('ii', $tattoo_id, $style_tag_id);
                            $link_tag_stmt->execute();
                            $link_tag_stmt->close();
                        }
                    }

                    $upsert_tag_stmt->close();
                }

                if (!isset($_SESSION['simulated_tattoo_ids']) || !is_array($_SESSION['simulated_tattoo_ids'])) {
                    $_SESSION['simulated_tattoo_ids'] = [];
                }

                $_SESSION['simulated_tattoo_ids'][] = $tattoo_id;
            }

            $insert_tattoo_stmt->close();
        }
    }

    header('Location: artist-profile.php');
    exit();
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submit_review'])
) {
    $reviewerId = isset($_SESSION['logged_in_account_id']) ? (int)$_SESSION['logged_in_account_id'] : 0;
    $artistIdFromPost = filter_var(
        $_POST['artist_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $targetArtistId = $artistIdFromPost ? (int)$artistIdFromPost : (int)$account_id;
    $rating = filter_var(
        $_POST['rating'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 5]]
    );
    $content = trim((string)($_POST['content'] ?? ''));
    $reviewDraftContent = (string)($_POST['content'] ?? '');
    $reviewDraftRating = $rating ? (int)$rating : 0;

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $reviewerId <= 0) {
        $reviewError = "Couldn't review artist";
        $openReviewOverlay = true;
    } elseif ($targetArtistId <= 0 || $reviewerId === $targetArtistId) {
        $reviewError = "Couldn't review artist";
        $openReviewOverlay = true;
    } elseif (!$rating) {
        $reviewError = "Couldn't review artist";
        $openReviewOverlay = true;
    } elseif ($content === '') {
        $reviewError = "Couldn't review artist";
        $openReviewOverlay = true;
    } elseif (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        $reviewError = "Couldn't review artist";
        $openReviewOverlay = true;
    } else {
        // Verify the target account exists and is an artist.
        $artistCheckStmt = $mysqli->prepare('SELECT account_id FROM accounts WHERE account_id = ? AND role = ? LIMIT 1');

        if (!$artistCheckStmt) {
            $reviewError = "Couldn't review artist";
            $openReviewOverlay = true;
        } else {
            $artistRole = 'artist';
            $artistCheckStmt->bind_param('is', $targetArtistId, $artistRole);
            $artistCheckStmt->execute();
            $artistExists = $artistCheckStmt->get_result()->fetch_row() !== null;
            $artistCheckStmt->close();

            if (!$artistExists) {
                $reviewError = "Couldn't review artist";
                $openReviewOverlay = true;
            }
        }
    }

    if ($reviewError === '') {
        $title = 'Artist Review';
        $findStmt = $mysqli->prepare('SELECT review_id FROM reviews WHERE reviewer_id = ? AND artist_id = ? LIMIT 1');

        if (!$findStmt) {
            $reviewError = "Couldn't review artist";
            $openReviewOverlay = true;
        } else {
            $findStmt->bind_param('ii', $reviewerId, $targetArtistId);
            $findStmt->execute();
            $existingReview = $findStmt->get_result()->fetch_assoc() ?: null;
            $findStmt->close();

            if ($existingReview) {
                $saveStmt = $mysqli->prepare(
                    'UPDATE reviews
                     SET title = ?, content = ?, rating = ?, updated_at = CURRENT_TIMESTAMP()
                     WHERE reviewer_id = ? AND artist_id = ?'
                );
                if ($saveStmt) {
                    $saveStmt->bind_param('ssiii', $title, $content, $rating, $reviewerId, $targetArtistId);
                }
            } else {
                $saveStmt = $mysqli->prepare(
                    'INSERT INTO reviews (reviewer_id, artist_id, title, content, rating)
                     VALUES (?, ?, ?, ?, ?)'
                );
                if ($saveStmt) {
                    $saveStmt->bind_param('iissi', $reviewerId, $targetArtistId, $title, $content, $rating);
                }
            }

            if (!$saveStmt) {
                $reviewError = "Couldn't review artist";
                $openReviewOverlay = true;
            } else {
                $ok = $saveStmt->execute();
                $saveStmt->close();

                if ($ok) {
                    header('Location: artist-profile.php' . $profileQuery . '#reviews-list');
                    exit();
                }

                $reviewError = "Couldn't review artist";
                $openReviewOverlay = true;
            }
        }
    }
}

// Get artist details: accounts + artist_profiles
$artist = null;
if ($account_id) {
    $stmt = $mysqli->prepare("
        SELECT
            a.account_id, a.username, a.first_name, a.last_name, a.profile_image_path,
            ap.profile_id, ap.about, ap.hourly_rate, ap.min_rate, ap.day_rate,
            ap.availability_status,
            ap.mon_start, ap.mon_end, ap.tue_start, ap.tue_end,
            ap.wed_start, ap.wed_end, ap.thu_start, ap.thu_end,
            ap.fri_start, ap.fri_end, ap.sat_start, ap.sat_end,
            ap.sun_start, ap.sun_end
        FROM accounts a
        LEFT JOIN artist_profiles ap ON a.account_id = ap.account_id
        WHERE a.account_id = ?
    ");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artist = $result->fetch_assoc();
    $stmt->close();
} else {
    header('Location: login.php');
    exit();
}

// Get artist styles
$styles = [];
if ($account_id) {
    $stmt = $mysqli->prepare("
        SELECT st.tag_name FROM artist_style_tag ast
        JOIN style_tags st ON ast.style_tag_id = st.tag_id
        WHERE ast.artist_id = ?
    ");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $styles[] = $row['tag_name'];
    }
    $stmt->close();
}

// Get reviews and calculate average rating
$reviews = [];
$avg_rating = 0;
$review_count = 0;
if ($account_id) {
    $stmt = $mysqli->prepare("
        SELECT
            r.review_id, r.rating, r.content,
            a.username, a.profile_image_path
        FROM reviews r
        JOIN accounts a ON r.reviewer_id = a.account_id
        WHERE r.artist_id = ?
        ORDER BY r.review_id DESC
    ");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_rating = 0;
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
        $total_rating += (int)$row['rating'];
    }
    $review_count = count($reviews);
    $avg_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;
    $stmt->close();
}

// Get map data and location
$map_data = null;
if ($artist['profile_id']) {
    $stmt = $mysqli->prepare("SELECT * FROM map_data WHERE artist_profile_id = ?");
    $stmt->bind_param("i", $artist['profile_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $map_data = $result->fetch_assoc();
    $stmt->close();
}

// Get portfolio items
$portfolio_items = [];
if ($account_id) {
    $stmt = $mysqli->prepare(
        "SELECT tattoo_id, image_path, title, description, aspect_ratio
        FROM tattoos
        WHERE artist_id = ?
        ORDER BY (image_path = 'images/tattoos/uploadsim.jpg') DESC, tattoo_id DESC"
    );

    if ($stmt) {
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $portfolio_items[] = $row;
        }

        $stmt->close();
    }
}

// Get follower count
$follower_count = 0;
if ($account_id && isset($mysqli) && $mysqli instanceof mysqli) {
    $fcStmt = $mysqli->prepare('SELECT COUNT(*) FROM account_follows WHERE following_account_id = ?');
    if ($fcStmt) {
        $fcAccountId = (int)$account_id;
        $fcStmt->bind_param('i', $fcAccountId);
        $fcStmt->execute();
        $fcStmt->bind_result($follower_count);
        $fcStmt->fetch();
        $fcStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Profile - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/artist-profile.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Artist Profile Content -->
    <div id="artist-profile-container">
        <!-- Left Profile Column -->
        <div id="artist-sidebar">
            <div id="artist-info">
                <h2 id="artist-name">@<?= e($artist['username']) ?></h2>
                <p id="artist-real-name"><?= e($artist['first_name']) ?> <?= e($artist['last_name']) ?></p>

                <div id="rating">
                    <span class="rating-number"><?= $avg_rating ?> ★</span>
                    <p id="review-count"><?= $review_count ?> review<?= $review_count !== 1 ? 's' : '' ?></p>
                    <p id="follower-count"><?= (int)$follower_count ?> follower<?= $follower_count !== 1 ? 's' : '' ?></p>
                </div>

                <div id="artist-layout">
                    <div id="artist-picture">
                        <img src="<?= e($artist['profile_image_path'] ?? 'images/profile photos/User/UP_4.jpg') ?>" alt="<?= e($artist['username']) ?>">
                    </div>

                    <div id="artist-details">
                        <?php if ($map_data): ?>
                            <div class="detail-item">
                                <h4>Location</h4>
                                <p><span class="tag"><?= e($map_data['city']) ?>, <?= e($map_data['state']) ?></span></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($artist['hourly_rate'] || $artist['min_rate'] || $artist['day_rate']): ?>
                            <div class="detail-item rates-group">
                                <?php if ($artist['hourly_rate']): ?><h4>Hourly Rate</h4>
                                    <p><span class="tag">$<?= e($artist['hourly_rate']) ?></span></p><?php endif; ?>
                                <?php if ($artist['min_rate']): ?><h4>Minimum</h4>
                                    <p><span class="tag">$<?= e($artist['min_rate']) ?></span></p><?php endif; ?>
                                <?php if ($artist['day_rate']): ?><h4>Day Rate</h4>
                                    <p><span class="tag">$<?= e($artist['day_rate']) ?></span></p><?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($styles)): ?>
                            <div class="detail-item">
                                <h4>Style</h4>
                                <p><?php foreach ($styles as $style): ?><span class="tag"><?= e($style) ?></span><br><?php endforeach; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="action-buttons">
                    <?php if ($is_own_profile): ?>
                        <button class="action-btn" id="edit-profile-btn" onclick="window.location.href='artist-settings.php'">Edit Profile</button>
                    <?php elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <form method="post" action="artist-profile.php<?= e($profileQuery) ?>" style="flex:1; display:contents;">
                            <input type="hidden" name="toggle_follow" value="1">
                            <input type="hidden" name="target_account_id" value="<?= (int)$account_id ?>">
                            <button type="submit" class="action-btn" id="follow-btn"><?= $isFollowing ? 'Unfollow' : 'Follow' ?></button>
                        </form>
                        <button class="action-btn" id="message-btn" onclick="window.location.href='messages.php?conversation=<?= (int)$account_id ?>'">Message Artist</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- About Section -->
            <?php if ($artist['about']): ?>
                <div class="info-section">
                    <div class="section-header">
                        <h3>About</h3>
                    </div>
                    <div class="section-content about-content">
                        <p><?= e($artist['about']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Weekly Availability Section -->
            <div class="info-section">
                <div class="section-header">
                    <h3>Weekly Availability</h3>
                </div>
                <div class="section-content availability-content">
                    <?php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $day_keys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                    foreach ($days as $idx => $day_name) {
                        $start_key = $day_keys[$idx] . '_start';
                        $end_key = $day_keys[$idx] . '_end';
                        $start = $artist[$start_key] ?? null;
                        $end = $artist[$end_key] ?? null;
                        $hours = ($start && $end) ? e($start) . ' - ' . e($end) : '-';
                    ?>
                        <p><?= $day_name ?>: <?= $hours ?></p>
                    <?php } ?>
                </div>
            </div>

            <!-- Location Section -->
            <?php if ($map_data): ?>
                <div class="info-section">
                    <div class="section-header">
                        <h3>Location</h3>
                    </div>
                    <div class="section-content location-content">
                        <p id="artist-address">
                            <?= e($map_data['address']) ?><br>
                            <?= e($map_data['city']) ?>, <?= e($map_data['state']) ?> <?= e($map_data['postal_code']) ?>
                        </p>
                        <div id="artist-map"></div>

                        <script>
                            const address = "<?= e($map_data['address']) ?>, <?= e($map_data['city']) ?>, <?= e($map_data['state']) ?> <?= e($map_data['postal_code']) ?>";
                            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                                .then(res => res.json())
                                .then(data => {
                                    if (data.length > 0) {
                                        const lat = parseFloat(data[0].lat);
                                        const lon = parseFloat(data[0].lon);
                                        const map = L.map('artist-map').setView([lat, lon], 15);
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                        }).addTo(map);
                                        L.marker([lat, lon])
                                            .addTo(map)
                                            .bindPopup(`<b><?= e($map_data['address']) ?></b><br><?= e($map_data['city']) ?>, <?= e($map_data['state']) ?>`)
                                            .openPopup();
                                    } else {
                                        document.getElementById('artist-map').innerHTML = '<p>Could not load map for this address.</p>';
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('artist-map').innerHTML = '<p>Could not load map.</p>';
                                });
                        </script>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews Section -->
            <div class="info-section">
                <div class="section-header">
                    <h3>Reviews</h3>
                </div>
                <div class="section-content reviews-content">
                    <?php if (!$is_own_profile && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <button id="review-artist-btn">Review Artist</button>
                    <?php endif; ?>
                    <div id="reviews-list">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="review-user-info">
                                            <img src="<?= e($review['profile_image_path'] ?? 'images/profile photos/User/UP_4.jpg') ?>" alt="<?= e($review['username']) ?>" class="review-profile-pic">
                                            <span class="review-username"><?= e($review['username']) ?></span>
                                        </div>
                                        <span class="review-rating"><?= e($review['rating']) ?></span>
                                    </div>
                                    <p class="review-text"><?= e($review['content']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No reviews yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Portfolio Section -->
        <div id="portfolio-section">
            <div id="portfolio-header">
                <h2 id="portfolio-title">Portfolio</h2>
                <?php if ($is_own_profile): ?>
                    <button id="edit-portfolio-btn">Upload to Portfolio</button>
                <?php endif; ?>
            </div>
            <div id="portfolio-grid">
                <?php foreach ($portfolio_items as $item): ?>
                    <div class="portfolio-item <?= e(aspect_ratio_class($item['aspect_ratio'] ?? '1:1')) ?>">
                        <img src="<?= e($item['image_path']) ?>" alt="<?= e($item['title'] ?: 'Portfolio image') ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Portfolio Edit Overlay -->
        <div id="portfolio-overlay" class="overlay">
            <div id="portfolio-modal">
                <img src="images/favicons/cancel.png" id="close-portfolio-modal" class="close-btn" alt="Close">
                <h2>Edit Portfolio</h2>

                <div id="image-upload-area">
                    <div id="drop-zone">
                        <p>Browse or drop images here</p>
                        <input type="file" id="file-input" accept="image/*" multiple>
                    </div>
                </div>

                <form id="portfolio-form" method="post" action="artist-profile.php">
                    <input type="hidden" name="upload_portfolio" value="1">
                    <label for="image-title">Title</label>
                    <input type="text" id="image-title" name="title" placeholder="Enter image title">

                    <label for="image-description">Description</label>
                    <textarea id="image-description" name="description" rows="4"
                        placeholder="Enter image description"></textarea>

                    <label for="image-tags">Tags</label>
                    <input type="text" id="image-tags" name="tags" placeholder="Enter tags (comma separated)">

                    <button type="submit" id="upload-portfolio-btn">Upload</button>
                </form>
            </div>
        </div>

        <!-- Review Artist Overlay -->
        <div id="review-overlay" class="overlay">
            <div id="review-modal">
                <img src="images/favicons/cancel.png" id="close-review-modal" class="close-btn" alt="Close">
                <h2 style="font-family: 'BBH Hegarty', sans-serif; text-align: left; font-weight: normal;">Review Artist
                </h2>

                <form id="review-form-content" method="post" action="artist-profile.php<?= e($profileQuery) ?>" novalidate>
                    <input type="hidden" name="submit_review" value="1">
                    <input type="hidden" name="artist_id" value="<?= (int)$account_id ?>">

                    <div id="rating-select-container">
                        <label for="review-rating-select">Rating</label>
                        <select id="review-rating-select" name="rating">
                            <option value="">Select a rating</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $reviewDraftRating === $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div id="review-text-container">
                        <textarea id="review-text-input" name="content" placeholder="Add review..." rows="6"><?= e($reviewDraftContent) ?></textarea>
                    </div>

                    <?php if ($reviewError !== ''): ?>
                        <p id="review-error" style="color:#b84a4a; margin: 8px 0 0;"><?= e($reviewError) ?></p>
                    <?php else: ?>
                        <p id="review-error" style="color:#b84a4a; margin: 8px 0 0; display:none;"></p>
                    <?php endif; ?>

                    <button type="submit" id="submit-review-btn" class="review-submit-btn">Review Artist</button>
                </form>
            </div>
        </div>

    </div>

    <!-- Footer Section -->
    <div id="footer-placeholder"></div>

    <script>
        (function() {
            const overlay = document.getElementById('review-overlay');
            const openBtn = document.getElementById('review-artist-btn');
            const closeBtn = document.getElementById('close-review-modal');
            const reviewForm = document.getElementById('review-form-content');
            const reviewError = document.getElementById('review-error');

            if (openBtn && overlay) {
                openBtn.addEventListener('click', function() {
                    overlay.classList.add('active');
                });
            }

            if (closeBtn && overlay) {
                closeBtn.addEventListener('click', function() {
                    overlay.classList.remove('active');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        overlay.classList.remove('active');
                    }
                });
            }

            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    const ratingSelect = document.getElementById('review-rating-select');
                    const textInput = document.getElementById('review-text-input');
                    const hasRating = ratingSelect && ratingSelect.value;
                    const hasText = textInput && textInput.value.trim().length > 0;

                    if (!hasRating || !hasText) {
                        e.preventDefault();
                        const errEl = document.getElementById('review-error');
                        if (errEl) {
                            errEl.textContent = "Couldn't review artist";
                            errEl.style.display = '';
                        }
                    }
                });
            }

            <?php if ($openReviewOverlay): ?>
                if (overlay) {
                    overlay.classList.add('active');
                }
            <?php endif; ?>
        })();
    </script>
</body>

</html>
<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>