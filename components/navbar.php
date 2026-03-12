<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$defaultProfileImage = '/images/profile photos/User/UP_4.jpg';
$navProfileImage = $defaultProfileImage;
$navUsername = (string)($_SESSION['logged_in_username'] ?? 'username');
$navAccessLevel = (string)($_SESSION['logged_in_access_level'] ?? 'user');
$artistProfileId = isset($_SESSION['artist_profile_id']) ? (int)$_SESSION['artist_profile_id'] : 0;
$loggedInAccountId = isset($_SESSION['logged_in_account_id']) ? (int)$_SESSION['logged_in_account_id'] : 0;

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $loggedInAccountId > 0) {
    require_once __DIR__ . '/../connect.php';

    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $accountStmt = $mysqli->prepare('SELECT username, role, profile_image_path FROM accounts WHERE account_id = ? LIMIT 1');

        if ($accountStmt) {
            $accountStmt->bind_param('i', $loggedInAccountId);
            $accountStmt->execute();
            $accountResult = $accountStmt->get_result();
            $accountRow = $accountResult ? $accountResult->fetch_assoc() : null;
            $accountStmt->close();

            if ($accountRow) {
                $_SESSION['logged_in_username'] = (string)$accountRow['username'];
                $_SESSION['logged_in_access_level'] = (string)$accountRow['role'];
                $_SESSION['logged_in_profile_image'] = (string)($accountRow['profile_image_path'] ?? '');

                $navUsername = (string)$accountRow['username'];
                $navAccessLevel = (string)$accountRow['role'];
            }
        }

        if ($navAccessLevel === 'artist' && $artistProfileId <= 0) {
            $profileStmt = $mysqli->prepare('SELECT profile_id FROM artist_profiles WHERE account_id = ? LIMIT 1');

            if ($profileStmt) {
                $profileStmt->bind_param('i', $loggedInAccountId);
                $profileStmt->execute();
                $profileResult = $profileStmt->get_result();
                $profileRow = $profileResult ? $profileResult->fetch_assoc() : null;
                $profileStmt->close();

                if ($profileRow) {
                    $artistProfileId = (int)$profileRow['profile_id'];
                    $_SESSION['artist_profile_id'] = $artistProfileId;
                }
            }
        }

        $mysqli->close();
    }
}

$sessionProfileImage = trim((string)($_SESSION['logged_in_profile_image'] ?? ''));
if ($sessionProfileImage !== '') {
    $navProfileImage = $sessionProfileImage;
}

$profileHref = 'user-profile.php';
if ($navAccessLevel === 'artist' && $artistProfileId > 0) {
    $profileHref = 'artist-profile.php?profile_id=' . $artistProfileId;
} elseif ($loggedInAccountId > 0) {
    $profileHref = 'user-profile.php?account_id=' . $loggedInAccountId;
}
?>
<div id="navbar">
    <a id="logo-link" href="index.html">
        <img id="bottle-logo" src="images/logos/inkseeklogomain.png" alt="ink bottle dripping">
    </a>
    <div id="center-buttons">
        <div id="center-buttons-group">
            <a class="navbutton" id="discover-button" href="discover.php">Discover</a>
            <a class="navbutton" id="about-us-button" href="about-us.html">About Us</a>
            <a class="navbutton" id="guides-button" href="guides.html">Guides</a>
        </div>
    </div>
    <div id="right-buttons">

        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <div id="logged-in-profile" class="right-buttons-group" style="display: flex;">
                <div class="profile-container">
                    <div class="profile-dropdown" onclick="toggleDropdown();">
                        <a id="profile-link" href="<?= htmlspecialchars($profileHref, ENT_QUOTES, 'UTF-8') ?>">
                            <img id="nav-profile-pic" class="nav-profile-pic"
                                src="<?= htmlspecialchars($navProfileImage, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Profile">
                        </a>
                        <div class="dropdown-arrow"></div>
                    </div>

                    <div id="dropdown-menu" class="dropdown-menu">
                        <div class="dropdown-header">
                            <p id="dropdown-username" class="dropdown-username">@<?= htmlspecialchars($navUsername, ENT_QUOTES, 'UTF-8') ?></p>
                            <p id="dropdown-account-type" class="dropdown-account-type">
                                <?= $navAccessLevel === 'artist' ? 'Artist Account' : 'Personal Account' ?>
                            </p>
                        </div>

                        <a href="artist-settings.php" class="dropdown-item">
                            <img src="images/favicons/settings.png" class="dropdown-icon-img" alt="Settings">
                            <span>Account Settings</span>
                        </a>
                        <a href="messages.php" class="dropdown-item">
                            <img src="images/favicons/messages.png" class="dropdown-icon-img" alt="Messages">
                            <span>Messages</span>
                        </a>
                        <a href="discover.php" class="dropdown-item">
                            <img src="images/favicons/search.png" class="dropdown-icon-img" alt="Search">
                            <span>Search</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <span>Log Out</span>
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div id="logged-out-buttons" class="right-buttons-group">
                <a class="navbutton" id="login" href="login.php">Log In</a>
                <a class="navbutton" id="create-account" href="create-account.php">Create Account</a>
            </div>
        <?php endif; ?>

    </div>
</div>