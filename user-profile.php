<?php
session_start();
require_once __DIR__ . '/connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function get_aspect_ratio_class($aspectRatio)
{
    $ratio = trim((string)$aspectRatio);
    if ($ratio === '1:1') {
        return ' ratio-1-1';
    }
    if ($ratio === '2:3') {
        return ' ratio-2-3';
    }
    if ($ratio === '3:4') {
        return ' ratio-3-4';
    }

    return '';
}

$defaultProfileImage = 'images/profile photos/User/UP_4.jpg';
$defaultAboutText = 'This user has not added an about section yet.';

$requestedAccountId = filter_input(
    INPUT_GET,
    'account_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$sessionAccountId = isset($_SESSION['logged_in_account_id']) ? (int)$_SESSION['logged_in_account_id'] : 0;
$accountId = $requestedAccountId ?: ($sessionAccountId > 0 ? $sessionAccountId : null);

$account = null;
$followingCount = 0;
$followerCount = 0;
$bookmarkedTattoos = [];

if ($accountId !== null && isset($mysqli) && $mysqli instanceof mysqli) {
    $accountStmt = $mysqli->prepare(
        'SELECT a.account_id, a.username, a.first_name, a.last_name, a.profile_image_path, ap.about
         FROM accounts a
         LEFT JOIN artist_profiles ap ON ap.account_id = a.account_id
         WHERE a.account_id = ?
         LIMIT 1'
    );

    if ($accountStmt) {
        $accountStmt->bind_param('i', $accountId);
        $accountStmt->execute();
        $accountResult = $accountStmt->get_result();
        $account = $accountResult ? $accountResult->fetch_assoc() : null;
        $accountStmt->close();
    }

    if ($account) {
        $followingsStmt = $mysqli->prepare('SELECT COUNT(*) AS count_total FROM account_follows WHERE follower_account_id = ?');
        if ($followingsStmt) {
            $followingsStmt->bind_param('i', $accountId);
            $followingsStmt->execute();
            $followingsResult = $followingsStmt->get_result();
            $followingsRow = $followingsResult ? $followingsResult->fetch_assoc() : null;
            $followingCount = isset($followingsRow['count_total']) ? (int)$followingsRow['count_total'] : 0;
            $followingsStmt->close();
        }

        $followersStmt = $mysqli->prepare('SELECT COUNT(*) AS count_total FROM account_follows WHERE following_account_id = ?');
        if ($followersStmt) {
            $followersStmt->bind_param('i', $accountId);
            $followersStmt->execute();
            $followersResult = $followersStmt->get_result();
            $followersRow = $followersResult ? $followersResult->fetch_assoc() : null;
            $followerCount = isset($followersRow['count_total']) ? (int)$followersRow['count_total'] : 0;
            $followersStmt->close();
        }

        $bookmarksStmt = $mysqli->prepare(
            'SELECT t.tattoo_id, t.image_path, t.title, t.aspect_ratio
             FROM bookmarks b
             INNER JOIN tattoos t ON t.tattoo_id = b.tattoo_id
             WHERE b.account_id = ?
             ORDER BY b.created_at DESC, t.tattoo_id DESC'
        );

        if ($bookmarksStmt) {
            $bookmarksStmt->bind_param('i', $accountId);
            $bookmarksStmt->execute();
            $bookmarksResult = $bookmarksStmt->get_result();
            if ($bookmarksResult) {
                while ($bookmarkRow = $bookmarksResult->fetch_assoc()) {
                    $bookmarkedTattoos[] = $bookmarkRow;
                }
            }
            $bookmarksStmt->close();
        }
    }
}

$username = $account ? (string)$account['username'] : 'unknown';
$realName = $account ? trim((string)$account['first_name'] . ' ' . (string)$account['last_name']) : 'Unknown User';
$profileImagePath = $defaultProfileImage;
$aboutText = $defaultAboutText;

if ($account) {
    $candidateImagePath = trim((string)($account['profile_image_path'] ?? ''));
    if ($candidateImagePath !== '') {
        $profileImagePath = $candidateImagePath;
    }

    $candidateAboutText = trim((string)($account['about'] ?? ''));
    if ($candidateAboutText !== '') {
        $aboutText = $candidateAboutText;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/user-profile.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Profile Content -->
    <div id="profile-container">
        <!-- Left Profile Column -->
        <div id="profile-sidebar">
            <div id="profile-info">
                <h2 id="username">@<?php echo e($username); ?></h2>
                <p id="realname"><?php echo e($realName); ?></p>

                <div id="stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo e($followingCount); ?></span>
                        <span class="stat-label">Following</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo e($followerCount); ?></span>
                        <span class="stat-label">Followers</span>
                    </div>
                </div>

                <div id="profile-picture">
                    <img src="<?php echo e($profileImagePath); ?>" alt="Profile Picture">
                </div>

                <div id="profile-buttons">
                    <button class="profile-btn" id="edit-profile-btn"
                        onclick="window.location.href='user-settings.php'">Edit Profile</button>
                    <button class="profile-btn" id="messages-btn"
                        onclick="window.location.href='messages.php'">Messages</button>
                </div>
            </div>

            <div id="about-section">
                <div id="about-header">
                    <h3>About</h3>
                </div>
                <div id="about-content">
                    <p><?php echo e($aboutText); ?></p>
                </div>
            </div>
        </div>

        <!-- Right Bookmarked Images Grid -->
        <div id="bookmarks-section">
            <h2 id="bookmarks-title">Bookmarked</h2>
            <div id="bookmarks-grid">
                <?php if ($account && !empty($bookmarkedTattoos)): ?>
                    <?php foreach ($bookmarkedTattoos as $bookmark): ?>
                        <div class="bookmark-item<?php echo get_aspect_ratio_class($bookmark['aspect_ratio'] ?? ''); ?>">
                            <a href="<?php echo 'tattoos.php?tattoo_id=' . (int)$bookmark['tattoo_id']; ?>">
                                <img src="<?php echo e($bookmark['image_path']); ?>" alt="<?php echo e($bookmark['title'] ?: 'Bookmarked tattoo'); ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($account): ?>
                    <p>This user has no bookmarks yet.</p>
                <?php else: ?>
                    <p>Profile not found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <div id="footer-placeholder"></div>
</body>

</html>
<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>