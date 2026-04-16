<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/connect.php';

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

$tattooId = 0;

if (isset($_GET['tattoo_id']) && ctype_digit((string)$_GET['tattoo_id'])) {
    $tattooId = (int)$_GET['tattoo_id'];
} else {
    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '');
    if (preg_match('#/(?:tattoo|tattoos)/(\d+)(?:/)?(?:\?.*)?$#', $requestUri, $matches) === 1) {
        $tattooId = (int)$matches[1];
    }
}

$selectedTattoo = null;
$styleTags = [];
$artistPortfolio = [];
$artistProfileHref = 'discover.php';
$loggedInAccountId = isset($_SESSION['logged_in_account_id']) ? (int)$_SESSION['logged_in_account_id'] : 0;
$loggedInAccessLevel = (string)($_SESSION['logged_in_access_level'] ?? '');
$canBookmark = isset($_SESSION['logged_in'])
    && $_SESSION['logged_in'] === true
    && $loggedInAccountId > 0
    && $loggedInAccessLevel === 'user';
$isBookmarked = false;

if ($db_ready && $mysqli instanceof mysqli && $tattooId > 0) {
    $tattooStmt = $mysqli->prepare(
        'SELECT
            t.tattoo_id,
            t.artist_id,
            t.image_path,
            t.title,
            t.description,
            t.aspect_ratio,
            a.username,
            a.profile_image_path,
            ap.profile_id
         FROM tattoos t
         LEFT JOIN accounts a ON a.account_id = t.artist_id
         LEFT JOIN artist_profiles ap ON ap.account_id = t.artist_id
         WHERE t.tattoo_id = ?
         LIMIT 1'
    );

    if ($tattooStmt) {
        $tattooStmt->bind_param('i', $tattooId);
        $tattooStmt->execute();
        $selectedTattoo = $tattooStmt->get_result()->fetch_assoc() ?: null;
        $tattooStmt->close();
    }

    if ($selectedTattoo) {
        $profileId = (int)($selectedTattoo['profile_id'] ?? 0);
        if ($profileId > 0) {
            $artistProfileHref = 'artist-profile.php?profile_id=' . $profileId;
        }

        $styleStmt = $mysqli->prepare(
            'SELECT st.tag_name
             FROM tattoo_style_tag tst
             INNER JOIN style_tags st ON st.tag_id = tst.style_tag_id
             WHERE tst.tattoo_id = ?
             ORDER BY st.tag_name ASC'
        );

        if ($styleStmt) {
            $styleStmt->bind_param('i', $tattooId);
            $styleStmt->execute();
            $styleResult = $styleStmt->get_result();

            while ($styleRow = $styleResult->fetch_assoc()) {
                $styleTags[] = $styleRow['tag_name'];
            }

            $styleStmt->close();
        }

        $artistId = (int)$selectedTattoo['artist_id'];
        if ($artistId > 0) {
            $portfolioStmt = $mysqli->prepare(
                'SELECT tattoo_id, image_path, title, aspect_ratio
                 FROM tattoos
                 WHERE artist_id = ? AND tattoo_id <> ?
                 ORDER BY tattoo_id DESC'
            );

            if ($portfolioStmt) {
                $portfolioStmt->bind_param('ii', $artistId, $tattooId);
                $portfolioStmt->execute();
                $portfolioResult = $portfolioStmt->get_result();

                while ($portfolioRow = $portfolioResult->fetch_assoc()) {
                    $artistPortfolio[] = $portfolioRow;
                }

                $portfolioStmt->close();
            }
        }

        if ($canBookmark) {
            $bookmarkStmt = $mysqli->prepare(
                'SELECT 1
                 FROM bookmarks
                 WHERE account_id = ? AND tattoo_id = ?
                 LIMIT 1'
            );

            if ($bookmarkStmt) {
                $bookmarkStmt->bind_param('ii', $loggedInAccountId, $tattooId);
                $bookmarkStmt->execute();
                $bookmarkResult = $bookmarkStmt->get_result();
                $isBookmarked = $bookmarkResult && $bookmarkResult->fetch_row() !== null;
                $bookmarkStmt->close();
            }
        }
    }
}

$pageTitle = $selectedTattoo && !empty($selectedTattoo['title'])
    ? $selectedTattoo['title'] . ' - Inkseek'
    : 'Tattoo View - Inkseek';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/image-view.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <div id="navbar-placeholder"></div>

    <div id="image-view-container">
        <?php if ($selectedTattoo): ?>
            <div id="left-column">
                <div id="image-display">
                    <img src="<?php echo e($selectedTattoo['image_path']); ?>"
                        alt="<?php echo e($selectedTattoo['title'] ?: 'Tattoo image'); ?>">
                </div>

                <div id="image-info">
                    <div id="title-row">
                        <h2 id="image-title"><?php echo e($selectedTattoo['title'] ?: 'Untitled Tattoo'); ?></h2>
                        <div id="action-icons">
                            <?php if ($canBookmark): ?>
                                <button
                                    type="button"
                                    id="bookmark-button"
                                    class="action-button"
                                    data-bookmark-endpoint="bookmark-actions.php"
                                    data-tattoo-id="<?php echo (int)$tattooId; ?>"
                                    data-bookmarked="<?php echo $isBookmarked ? 'true' : 'false'; ?>">
                                    <img
                                        id="bookmark-icon"
                                        src="<?php echo e($isBookmarked ? 'images/favicons/bookmark-clicked.png' : 'images/favicons/bookmark.png'); ?>"
                                        alt="Bookmark"
                                        class="action-icon">
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="<?php echo e($artistProfileHref); ?>" id="artist-link">
                        <img src="<?php echo e($selectedTattoo['profile_image_path'] ?: 'images/profile photos/User/UP_4.jpg'); ?>"
                            alt="<?php echo e($selectedTattoo['username'] ?: 'Artist'); ?>"
                            id="artist-thumbnail">
                        <span id="artist-username">@<?php echo e($selectedTattoo['username'] ?: 'artist'); ?></span>
                    </a>

                    <div id="style-tag">
                        <?php if (!empty($styleTags)): ?>
                            <?php foreach ($styleTags as $tagName): ?>
                                <span class="tag"><?php echo e($tagName); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div id="image-description">
                        <p><?php echo e($selectedTattoo['description'] ?: 'No description provided for this tattoo yet.'); ?></p>
                    </div>
                </div>
            </div>

            <div id="right-column">
                <a href="<?php echo e($artistProfileHref); ?>" id="artist-gallery-header">
                    <span>This artist</span>
                    <img src="images/favicons/Right arrow.png" class="arrow" alt="Arrow">
                </a>

                <div id="artist-gallery">
                    <?php if (!empty($artistPortfolio)): ?>
                        <?php foreach ($artistPortfolio as $portfolioItem): ?>
                            <div class="gallery-item <?php echo e(aspect_ratio_class($portfolioItem['aspect_ratio'] ?? '1:1')); ?>">
                                <a href="<?php echo e('tattoos.php?tattoo_id=' . (int)$portfolioItem['tattoo_id']); ?>">
                                    <img src="<?php echo e($portfolioItem['image_path']); ?>"
                                        alt="<?php echo e($portfolioItem['title'] ?: 'Artist work'); ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No other portfolio images available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div id="left-column">
                <h2 id="image-title">Tattoo not found</h2>
                <p>The requested tattoo could not be loaded.</p>
                <p><a href="discover.php">Return to Discover</a></p>
            </div>
        <?php endif; ?>
    </div>

    <div id="footer-placeholder"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookmarkButton = document.getElementById('bookmark-button');
            const bookmarkIcon = document.getElementById('bookmark-icon');
            if (!bookmarkButton || !bookmarkIcon) {
                return;
            }

            const inactiveIconSrc = 'images/favicons/bookmark.png';
            const activeIconSrc = 'images/favicons/bookmark-clicked.png';
            let isBookmarked = bookmarkButton.dataset.bookmarked === 'true';

            const setBookmarkedState = function(bookmarked) {
                isBookmarked = bookmarked;
                bookmarkButton.dataset.bookmarked = bookmarked ? 'true' : 'false';

                bookmarkIcon.src = bookmarked ? activeIconSrc : inactiveIconSrc;
            };

            setBookmarkedState(isBookmarked);

            bookmarkButton.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (bookmarkButton.disabled) {
                    return;
                }

                bookmarkButton.disabled = true;

                const action = isBookmarked ? 'remove_bookmark' : 'add_bookmark';

                try {
                    const response = await fetch(bookmarkButton.dataset.bookmarkEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            action: action,
                            tattoo_id: bookmarkButton.dataset.tattooId
                        }).toString()
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Could not update bookmark right now.');
                    }

                    setBookmarkedState(Boolean(data.bookmarked));
                } catch (error) {
                    alert(error.message || 'Could not update bookmark right now.');
                } finally {
                    bookmarkButton.disabled = false;
                }
            });
        });
    </script>
</body>

</html>
<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>