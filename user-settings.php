<?php
session_start();
require_once 'connect.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Something went wrong connecting to the database. Please try again later.');
}

$account_id = (int)($_SESSION['logged_in_account_id'] ?? 0);
$success = isset($_GET['saved']) && $_GET['saved'] === '1';
$error_message = '';

$account_stmt = $mysqli->prepare(
    'SELECT a.first_name, a.last_name, a.username, a.email, a.profile_image_path, a.role, ap.about
     FROM accounts a
     LEFT JOIN artist_profiles ap ON ap.account_id = a.account_id
     WHERE a.account_id = ?
     LIMIT 1'
);

if (!$account_stmt) {
    error_log('user-settings.php account query prepare failed: ' . $mysqli->error);
    http_response_code(500);
    exit('Something went wrong loading your profile. Please try again later.');
}

$account_stmt->bind_param('i', $account_id);
$account_stmt->execute();
$account_result = $account_stmt->get_result();
$account_row = $account_result->fetch_assoc();
$account_stmt->close();

if (!$account_row) {
    header('Location: logout.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $about = trim($_POST['about'] ?? '');

    if ($first_name === '' || $last_name === '' || $username === '' || $email === '') {
        $error_message = 'First name, last name, username, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $username_check = $mysqli->prepare('SELECT account_id FROM accounts WHERE username = ? AND account_id <> ? LIMIT 1');
        if (!$username_check) {
            error_log('user-settings.php username check prepare failed: ' . $mysqli->error);
            $error_message = 'Unable to validate username right now. Please try again.';
        }
        if ($error_message === '') {
            $username_check->bind_param('si', $username, $account_id);
            $username_check->execute();
            $username_exists = $username_check->get_result()->fetch_assoc();
            $username_check->close();
        }

        $email_exists = false;
        if ($error_message === '') {
            $email_check = $mysqli->prepare('SELECT account_id FROM accounts WHERE email = ? AND account_id <> ? LIMIT 1');
            if (!$email_check) {
                error_log('user-settings.php email check prepare failed: ' . $mysqli->error);
                $error_message = 'Unable to validate email right now. Please try again.';
            } else {
                $email_check->bind_param('si', $email, $account_id);
                $email_check->execute();
                $email_exists = $email_check->get_result()->fetch_assoc();
                $email_check->close();
            }
        }

        if ($error_message !== '') {
            // Keep validation/DB errors in $error_message for inline display.
        } elseif ($username_exists) {
            $error_message = 'That username is already taken. Please choose another.';
        } elseif ($email_exists) {
            $error_message = 'That email is already in use. Please choose another.';
        } else {
            $update_stmt = $mysqli->prepare('UPDATE accounts SET first_name = ?, last_name = ?, username = ?, email = ? WHERE account_id = ?');
            if (!$update_stmt) {
                error_log('user-settings.php account update prepare failed: ' . $mysqli->error);
                $error_message = 'Unable to save profile changes right now. Please try again.';
            } else {
                $update_stmt->bind_param('ssssi', $first_name, $last_name, $username, $email, $account_id);
                $update_stmt->execute();
                $update_stmt->close();
            }

            if ($error_message === '') {
                $about_value = $about === '' ? null : $about;
                $about_stmt = $mysqli->prepare(
                    'INSERT INTO artist_profiles (account_id, about)
                     VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE about = VALUES(about)'
                );

                if (!$about_stmt) {
                    error_log('user-settings.php about upsert prepare failed: ' . $mysqli->error);
                    $error_message = 'Unable to save your about section right now. Please try again.';
                } else {
                    $about_stmt->bind_param('is', $account_id, $about_value);
                    $about_stmt->execute();
                    $about_stmt->close();
                }
            }

            if ($error_message !== '') {
                $account_row['first_name'] = $first_name;
                $account_row['last_name'] = $last_name;
                $account_row['username'] = $username;
                $account_row['email'] = $email;
                $account_row['about'] = $about;
            } else {
                $_SESSION['logged_in_first_name'] = $first_name;
                $_SESSION['logged_in_last_name'] = $last_name;
                $_SESSION['logged_in_username'] = $username;
                $_SESSION['logged_in_email'] = $email;

                header('Location: user-settings.php?saved=1');
                exit();
            }
        }
    }

    // Reload with posted data when validation fails
    $account_row['first_name'] = $first_name;
    $account_row['last_name'] = $last_name;
    $account_row['username'] = $username;
    $account_row['email'] = $email;
    $account_row['about'] = $about;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/settings.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <div id="navbar-placeholder"></div>

    <div id="settings-container">
        <div id="settings-column">
            <h1>Edit Profile</h1>

            <div id="profile-picture-section">
                <div id="settings-profile-picture">
                    <img src="<?= e($account_row['profile_image_path'] ?: 'images/profile photos/User/UP_4.jpg') ?>" alt="Profile Picture">
                </div>
                <button id="edit-picture-btn" type="button">Edit Profile Picture</button>
            </div>

            <div id="settings-form">
                <form method="POST" action="user-settings.php">
                    <?php if ($success): ?>
                        <p class="success-msg">Changes saved successfully!</p>
                    <?php endif; ?>

                    <?php if ($error_message !== ''): ?>
                        <p class="error-msg"><?= e($error_message) ?></p>
                    <?php endif; ?>

                    <div class="name-row">
                        <div>
                            <label for="first_name">First name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= e($account_row['first_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="last_name">Last name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= e($account_row['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($account_row['username'] ?? '') ?>" required>

                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" value="<?= e($account_row['email'] ?? '') ?>" required>

                    <label for="about">About</label>
                    <input type="text" id="about" name="about" value="<?= e($account_row['about'] ?? '') ?>">

                    <button type="submit" id="save-changes-btn">Save Changes</button>
                </form>
            </div>

            <div id="account-actions">
                <form method="POST" action="account-actions.php" class="account-action-form">
                    <input type="hidden" name="action" value="convert_to_artist">
                    <input type="hidden" name="redirect" value="user-settings.php">
                    <button type="submit" class="action-link action-link-button">Convert to Artist Account</button>
                </form>

                <form method="POST" action="account-actions.php" class="account-action-form" onsubmit="return confirm('Delete your account and all associated data? This cannot be undone.');">
                    <input type="hidden" name="action" value="delete_account">
                    <input type="hidden" name="redirect" value="user-settings.php">
                    <button type="submit" class="action-link action-link-button">Delete Account</button>
                </form>
            </div>
        </div>
    </div>

    <div id="footer-placeholder"></div>
</body>

</html>
<?php
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>