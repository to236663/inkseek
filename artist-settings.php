<?php
session_start();
require_once 'connect.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$account_id = $_SESSION['logged_in_account_id'];
$schedule = [];
$success = isset($_GET['saved']) && $_GET['saved'] === '1';
$error_message = '';

// Load account + profile data for this artist
$artist_stmt = $mysqli->prepare("SELECT
    a.first_name,
    a.last_name,
    a.username,
    a.profile_image_path,
    ap.profile_id,
    ap.about,
    ap.availability_status,
    ap.mon_start, ap.mon_end,
    ap.tue_start, ap.tue_end,
    ap.wed_start, ap.wed_end,
    ap.thu_start, ap.thu_end,
    ap.fri_start, ap.fri_end,
    ap.sat_start, ap.sat_end,
    ap.sun_start, ap.sun_end
    FROM accounts a
    INNER JOIN artist_profiles ap ON ap.account_id = a.account_id
    WHERE a.account_id = ?
    LIMIT 1");
$artist_stmt->bind_param("i", $account_id);
$artist_stmt->execute();
$artist_result = $artist_stmt->get_result();
$artist_row = $artist_result->fetch_assoc();
$artist_stmt->close();

if (!$artist_row) {
    header('Location: create-account-artist.php');
    exit();
}

$profile_id = (int)$artist_row['profile_id'];

// Get existing map data for this artist
$map_stmt = $mysqli->prepare("SELECT address, city, state, postal_code FROM map_data WHERE artist_profile_id = ? LIMIT 1");
$map_stmt->bind_param("i", $profile_id);
$map_stmt->execute();
$map_result = $map_stmt->get_result();
$map_row = $map_result->fetch_assoc();
$map_stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $status = $_POST['availability_status'] ?? 'available';

    // For each day, use posted times only when the day checkbox is active.
    $mon_start = !empty($_POST['mon_active']) && !empty($_POST['mon_start']) ? $_POST['mon_start'] : null;
    $mon_end   = !empty($_POST['mon_active']) && !empty($_POST['mon_end']) ? $_POST['mon_end'] : null;
    $tue_start = !empty($_POST['tue_active']) && !empty($_POST['tue_start']) ? $_POST['tue_start'] : null;
    $tue_end   = !empty($_POST['tue_active']) && !empty($_POST['tue_end']) ? $_POST['tue_end'] : null;
    $wed_start = !empty($_POST['wed_active']) && !empty($_POST['wed_start']) ? $_POST['wed_start'] : null;
    $wed_end   = !empty($_POST['wed_active']) && !empty($_POST['wed_end']) ? $_POST['wed_end'] : null;
    $thu_start = !empty($_POST['thu_active']) && !empty($_POST['thu_start']) ? $_POST['thu_start'] : null;
    $thu_end   = !empty($_POST['thu_active']) && !empty($_POST['thu_end']) ? $_POST['thu_end'] : null;
    $fri_start = !empty($_POST['fri_active']) && !empty($_POST['fri_start']) ? $_POST['fri_start'] : null;
    $fri_end   = !empty($_POST['fri_active']) && !empty($_POST['fri_end']) ? $_POST['fri_end'] : null;
    $sat_start = !empty($_POST['sat_active']) && !empty($_POST['sat_start']) ? $_POST['sat_start'] : null;
    $sat_end   = !empty($_POST['sat_active']) && !empty($_POST['sat_end']) ? $_POST['sat_end'] : null;
    $sun_start = !empty($_POST['sun_active']) && !empty($_POST['sun_start']) ? $_POST['sun_start'] : null;
    $sun_end   = !empty($_POST['sun_active']) && !empty($_POST['sun_end']) ? $_POST['sun_end'] : null;

    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');

    $about = $about === '' ? null : $about;
    $address = $address === '' ? null : $address;
    $city = $city === '' ? null : $city;
    $state = $state === '' ? null : $state;
    $postal_code = $postal_code === '' ? null : $postal_code;

    if ($first_name === '' || $last_name === '' || $username === '') {
        $error_message = 'First name, last name, and username are required.';
    } else {
        $mysqli->begin_transaction();

        try {
            $account_update = $mysqli->prepare("UPDATE accounts SET first_name = ?, last_name = ?, username = ? WHERE account_id = ?");
            $account_update->bind_param("sssi", $first_name, $last_name, $username, $account_id);
            $account_update->execute();
            $account_update->close();

            $profile_update = $mysqli->prepare("UPDATE artist_profiles SET
                about = ?,
                availability_status = ?,
                mon_start = ?, mon_end = ?,
                tue_start = ?, tue_end = ?,
                wed_start = ?, wed_end = ?,
                thu_start = ?, thu_end = ?,
                fri_start = ?, fri_end = ?,
                sat_start = ?, sat_end = ?,
                sun_start = ?, sun_end = ?
                WHERE profile_id = ?");

            $profile_update->bind_param(
                "ssssssssssssssssi",
                $about,
                $status,
                $mon_start,
                $mon_end,
                $tue_start,
                $tue_end,
                $wed_start,
                $wed_end,
                $thu_start,
                $thu_end,
                $fri_start,
                $fri_end,
                $sat_start,
                $sat_end,
                $sun_start,
                $sun_end,
                $profile_id
            );
            $profile_update->execute();
            $profile_update->close();

            if ($map_row) {
                $map_update = $mysqli->prepare("UPDATE map_data SET
                    address = ?, city = ?, state = ?, postal_code = ?, updated_at = NOW()
                    WHERE artist_profile_id = ?");
                $map_update->bind_param("ssssi", $address, $city, $state, $postal_code, $profile_id);
                $map_update->execute();
                $map_update->close();
            } elseif ($address !== null || $city !== null || $state !== null || $postal_code !== null) {
                $map_insert = $mysqli->prepare("INSERT INTO map_data
                    (artist_profile_id, address, city, state, postal_code, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $map_insert->bind_param("issss", $profile_id, $address, $city, $state, $postal_code);
                $map_insert->execute();
                $map_insert->close();
            }

            $mysqli->commit();

            $_SESSION['logged_in_username'] = $username;
            $_SESSION['logged_in_first_name'] = $first_name;
            $_SESSION['logged_in_last_name'] = $last_name;

            header('Location: artist-settings.php?saved=1');
            exit();
        } catch (Throwable $t) {
            $mysqli->rollback();
            $error_message = 'Unable to save changes right now. Please try again.';
        }
    }
}

// Reload account/profile/map state after POST for immediate rendering.
$artist_stmt = $mysqli->prepare("SELECT
    a.first_name,
    a.last_name,
    a.username,
    a.profile_image_path,
    ap.profile_id,
    ap.about,
    ap.availability_status,
    ap.mon_start, ap.mon_end,
    ap.tue_start, ap.tue_end,
    ap.wed_start, ap.wed_end,
    ap.thu_start, ap.thu_end,
    ap.fri_start, ap.fri_end,
    ap.sat_start, ap.sat_end,
    ap.sun_start, ap.sun_end
    FROM accounts a
    INNER JOIN artist_profiles ap ON ap.account_id = a.account_id
    WHERE a.account_id = ?
    LIMIT 1");
$artist_stmt->bind_param("i", $account_id);
$artist_stmt->execute();
$artist_result = $artist_stmt->get_result();
$artist_row = $artist_result->fetch_assoc();
$artist_stmt->close();

$profile_id = (int)$artist_row['profile_id'];

$map_stmt = $mysqli->prepare("SELECT address, city, state, postal_code FROM map_data WHERE artist_profile_id = ? LIMIT 1");
$map_stmt->bind_param("i", $profile_id);
$map_stmt->execute();
$map_result = $map_stmt->get_result();
$map_row = $map_result->fetch_assoc();
$map_stmt->close();

$availability_status = $artist_row['availability_status'] ?? 'available';

// Build $schedule array to match the HTML expectations
$days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
foreach ($days as $day) {
    if (!empty($artist_row[$day . '_start'])) {
        $schedule[$day] = [
            'start' => $artist_row[$day . '_start'],
            'end'   => $artist_row[$day . '_end']
        ];
    }
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
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Settings Container -->
    <div id="settings-container">
        <div id="settings-column">
            <h1>Edit Profile</h1>

            <!-- Profile Picture Section -->
            <div id="profile-picture-section">
                <div id="settings-profile-picture">
                    <img src="<?= e($artist_row['profile_image_path'] ?: 'images/profile photos/Artist/Profile_1.jpg') ?>" alt="Profile Picture">
                </div>
                <button id="edit-picture-btn">Edit Profile Picture</button>
            </div>

            <!-- Edit Form -->
            <div id="settings-form">
                <form method="POST" action="artist-settings.php">

                    <?php if ($success): ?>
                        <p class="success-msg">Changes saved successfully!</p>
                    <?php endif; ?>

                    <?php if ($error_message !== ''): ?>
                        <p class="error-msg"><?= e($error_message) ?></p>
                    <?php endif; ?>

                    <div class="name-row">
                        <div>
                            <label for="first_name">First name</label>
                            <input type="text" id="first_name" name="first_name" value="<?= e($artist_row['first_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label for="last_name">Last name</label>
                            <input type="text" id="last_name" name="last_name" value="<?= e($artist_row['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($artist_row['username'] ?? '') ?>" required>

                    <label for="about">About</label>
                    <textarea id="about" name="about" rows="5"><?= e($artist_row['about'] ?? '') ?></textarea>

                    <!-- Availability -->
                    <div id="availability-section">
                        <label for="availability-status">Availability Status</label>
                        <select id="availability-status" name="availability_status">
                            <option value="available" <?= $availability_status === 'available'   ? 'selected' : '' ?>>Available</option>
                            <option value="limited" <?= $availability_status === 'limited'     ? 'selected' : '' ?>>Limited Availability</option>
                            <option value="booked" <?= $availability_status === 'booked'      ? 'selected' : '' ?>>Fully Booked</option>
                            <option value="unavailable" <?= $availability_status === 'unavailable' ? 'selected' : '' ?>>Not Taking Clients</option>
                        </select>

                        <div id="weekly-schedule">
                            <h3>Weekly Hours</h3>
                            <div class="schedule-grid">

                                <div class="schedule-row <?= empty($schedule['mon']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="mon-active" name="mon_active" <?= !empty($schedule['mon']) ? 'checked' : '' ?>>
                                        <label for="mon-active">Monday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="mon-start" name="mon_start" value="<?= $schedule['mon']['start'] ?? '' ?>" <?= empty($schedule['mon']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="mon-end" name="mon_end" value="<?= $schedule['mon']['end'] ?? '' ?>" <?= empty($schedule['mon']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                                <div class="schedule-row <?= empty($schedule['tue']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="tue-active" name="tue_active" <?= !empty($schedule['tue']) ? 'checked' : '' ?>>
                                        <label for="tue-active">Tuesday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="tue-start" name="tue_start" value="<?= $schedule['tue']['start'] ?? '' ?>" <?= empty($schedule['tue']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="tue-end" name="tue_end" value="<?= $schedule['tue']['end'] ?? '' ?>" <?= empty($schedule['tue']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                                <div class="schedule-row <?= empty($schedule['wed']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="wed-active" name="wed_active" <?= !empty($schedule['wed']) ? 'checked' : '' ?>>
                                        <label for="wed-active">Wednesday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="wed-start" name="wed_start" value="<?= $schedule['wed']['start'] ?? '' ?>" <?= empty($schedule['wed']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="wed-end" name="wed_end" value="<?= $schedule['wed']['end'] ?? '' ?>" <?= empty($schedule['wed']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                                <div class="schedule-row <?= empty($schedule['thu']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="thu-active" name="thu_active" <?= !empty($schedule['thu']) ? 'checked' : '' ?>>
                                        <label for="thu-active">Thursday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="thu-start" name="thu_start" value="<?= $schedule['thu']['start'] ?? '' ?>" <?= empty($schedule['thu']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="thu-end" name="thu_end" value="<?= $schedule['thu']['end'] ?? '' ?>" <?= empty($schedule['thu']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                                <div class="schedule-row <?= empty($schedule['fri']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="fri-active" name="fri_active" <?= !empty($schedule['fri']) ? 'checked' : '' ?>>
                                        <label for="fri-active">Friday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="fri-start" name="fri_start" value="<?= $schedule['fri']['start'] ?? '' ?>" <?= empty($schedule['fri']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="fri-end" name="fri_end" value="<?= $schedule['fri']['end'] ?? '' ?>" <?= empty($schedule['fri']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                                <div class="schedule-row <?= empty($schedule['sat']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="sat-active" name="sat_active" <?= !empty($schedule['sat']) ? 'checked' : '' ?>>
                                        <label for="sat-active">Saturday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="sat-start" name="sat_start" value="<?= $schedule['sat']['start'] ?? '' ?>" <?= empty($schedule['sat']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="sat-end" name="sat_end" value="<?= $schedule['sat']['end'] ?? '' ?>" <?= empty($schedule['sat']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                                <div class="schedule-row <?= empty($schedule['sun']) ? 'closed' : '' ?>">
                                    <div class="day-toggle">
                                        <input type="checkbox" id="sun-active" name="sun_active" <?= !empty($schedule['sun']) ? 'checked' : '' ?>>
                                        <label for="sun-active">Sunday</label>
                                    </div>
                                    <div class="time-inputs">
                                        <input type="time" id="sun-start" name="sun_start" value="<?= $schedule['sun']['start'] ?? '' ?>" <?= empty($schedule['sun']) ? 'disabled' : '' ?>>
                                        <span class="time-separator">-</span>
                                        <input type="time" id="sun-end" name="sun_end" value="<?= $schedule['sun']['end'] ?? '' ?>" <?= empty($schedule['sun']) ? 'disabled' : '' ?>>
                                    </div>
                                    <span class="closed-label">Closed</span>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div id="location-section">
                        <h3>Studio Location</h3>

                        <label for="address">Street Address</label>
                        <br>
                        <input type="text" id="address" name="address"
                            value="<?= e($map_row['address'] ?? '') ?>">

                        <label for="city">City</label>
                        <br>
                        <input type="text" id="city" name="city"
                            value="<?= e($map_row['city'] ?? '') ?>">

                        <label for="state">State</label>
                        <br>
                        <input type="text" id="state" name="state"
                            value="<?= e($map_row['state'] ?? '') ?>">

                        <label for="postal_code">Postal Code</label>
                        <br>
                        <input type="text" id="postal_code" name="postal_code"
                            value="<?= e($map_row['postal_code'] ?? '') ?>">
                    </div>

                    <button type="submit" id="save-changes-btn">Save Changes</button>

                </form>
            </div>


            <!-- Account Actions -->
            <div id="account-actions">
                <form method="POST" action="account-actions.php" class="account-action-form">
                    <input type="hidden" name="action" value="convert_to_user">
                    <input type="hidden" name="redirect" value="artist-settings.php">
                    <button type="submit" class="action-link action-link-button">Convert to User Account</button>
                </form>

                <form method="POST" action="account-actions.php" class="account-action-form" onsubmit="return confirm('Delete your account and all associated data? This cannot be undone.');">
                    <input type="hidden" name="action" value="delete_account">
                    <input type="hidden" name="redirect" value="artist-settings.php">
                    <button type="submit" class="action-link action-link-button">Delete Account</button>
                </form>
            </div>

        </div><!-- end settings-column -->
    </div><!-- end settings-container -->

    <!-- Footer -->
    <div id="footer-placeholder"></div>

    <script>
        document.querySelectorAll('.schedule-row input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('.schedule-row');
                const timeInputs = row.querySelectorAll('input[type="time"]');
                if (this.checked) {
                    row.classList.remove('closed');
                    timeInputs.forEach(input => input.disabled = false);
                } else {
                    row.classList.add('closed');
                    timeInputs.forEach(input => input.disabled = true);
                }
            });
        });
    </script>

</body>

</html>

<?php $mysqli->close(); ?>