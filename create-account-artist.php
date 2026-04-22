<?php
require_once __DIR__ . '/connect.php';
session_start();

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die('error: could not connect to database');
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$form = [
    'first_name' => '',
    'last_name' => '',
    'username' => '',
    'email' => '',
    'about' => '',
    'availability_status' => 'available',
    'address' => '',
    'city' => '',
    'state' => '',
    'postal_code' => '',
    'mon_start' => '',
    'mon_end' => '',
    'tue_start' => '',
    'tue_end' => '',
    'wed_start' => '',
    'wed_end' => '',
    'thu_start' => '',
    'thu_end' => '',
    'fri_start' => '',
    'fri_end' => '',
    'sat_start' => '',
    'sat_end' => '',
    'sun_start' => '',
    'sun_end' => '',
];

$dayKeys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['first_name'] = trim((string)($_POST['first_name'] ?? ''));
    $form['last_name'] = trim((string)($_POST['last_name'] ?? ''));
    $form['username'] = trim((string)($_POST['username'] ?? ''));
    $form['email'] = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');
    $form['about'] = trim((string)($_POST['about'] ?? ''));
    $form['availability_status'] = trim((string)($_POST['availability_status'] ?? 'available'));
    $form['address'] = trim((string)($_POST['address'] ?? ''));
    $form['city'] = trim((string)($_POST['city'] ?? ''));
    $form['state'] = trim((string)($_POST['state'] ?? ''));
    $form['postal_code'] = trim((string)($_POST['postal_code'] ?? ''));

    foreach ($dayKeys as $day) {
        $startKey = $day . '_start';
        $endKey = $day . '_end';
        $form[$startKey] = trim((string)($_POST[$startKey] ?? ''));
        $form[$endKey] = trim((string)($_POST[$endKey] ?? ''));
    }

    $allowedStatuses = ['available', 'limited', 'booked', 'unavailable'];
    if (!in_array($form['availability_status'], $allowedStatuses, true)) {
        $form['availability_status'] = 'available';
    }

    if ($form['first_name'] === '' || $form['last_name'] === '' || $form['username'] === '' || $form['email'] === '') {
        $error = 'First name, last name, username, and email are required.';
    } elseif ($password === '' || $confirm_password === '') {
        $error = 'Password and confirm password are required.';
    } elseif ($confirm_password !== $password) {
        $error = 'Passwords do not match.';
    } else {
        $check_stmt = $mysqli->prepare('SELECT account_id FROM accounts WHERE username = ? LIMIT 1');
        $check_stmt->bind_param('s', $form['username']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $usernameTaken = $check_result->num_rows > 0;
        $check_stmt->close();

        if ($usernameTaken) {
            $error = 'Username is already taken. Please choose another.';
        } else {
            $email_stmt = $mysqli->prepare('SELECT account_id FROM accounts WHERE email = ? LIMIT 1');
            $email_stmt->bind_param('s', $form['email']);
            $email_stmt->execute();
            $email_result = $email_stmt->get_result();
            $emailExists = $email_result->num_rows > 0;
            $email_stmt->close();

            if ($emailExists) {
                $error = 'An account with that email already exists.';
            } else {
                $role = 'artist';
                $about = $form['about'] === '' ? null : $form['about'];

                $mon_start = !empty($_POST['mon_active']) && $form['mon_start'] !== '' ? $form['mon_start'] : null;
                $mon_end = !empty($_POST['mon_active']) && $form['mon_end'] !== '' ? $form['mon_end'] : null;
                $tue_start = !empty($_POST['tue_active']) && $form['tue_start'] !== '' ? $form['tue_start'] : null;
                $tue_end = !empty($_POST['tue_active']) && $form['tue_end'] !== '' ? $form['tue_end'] : null;
                $wed_start = !empty($_POST['wed_active']) && $form['wed_start'] !== '' ? $form['wed_start'] : null;
                $wed_end = !empty($_POST['wed_active']) && $form['wed_end'] !== '' ? $form['wed_end'] : null;
                $thu_start = !empty($_POST['thu_active']) && $form['thu_start'] !== '' ? $form['thu_start'] : null;
                $thu_end = !empty($_POST['thu_active']) && $form['thu_end'] !== '' ? $form['thu_end'] : null;
                $fri_start = !empty($_POST['fri_active']) && $form['fri_start'] !== '' ? $form['fri_start'] : null;
                $fri_end = !empty($_POST['fri_active']) && $form['fri_end'] !== '' ? $form['fri_end'] : null;
                $sat_start = !empty($_POST['sat_active']) && $form['sat_start'] !== '' ? $form['sat_start'] : null;
                $sat_end = !empty($_POST['sat_active']) && $form['sat_end'] !== '' ? $form['sat_end'] : null;
                $sun_start = !empty($_POST['sun_active']) && $form['sun_start'] !== '' ? $form['sun_start'] : null;
                $sun_end = !empty($_POST['sun_active']) && $form['sun_end'] !== '' ? $form['sun_end'] : null;

                $address = $form['address'] === '' ? null : $form['address'];
                $city = $form['city'] === '' ? null : $form['city'];
                $state = $form['state'] === '' ? null : $form['state'];
                $postal_code = $form['postal_code'] === '' ? null : $form['postal_code'];

                $mysqli->begin_transaction();

                try {
                    $profile_image_path = 'images/profilephotos/defaultProfile.jpg';
                    $insert_stmt = $mysqli->prepare(
                        'INSERT INTO accounts (role, username, first_name, last_name, email, password, profile_image_path) VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    $insert_stmt->bind_param('sssssss', $role, $form['username'], $form['first_name'], $form['last_name'], $form['email'], $password, $profile_image_path);
                    $insert_stmt->execute();
                    $account_id = (int)$insert_stmt->insert_id;
                    $insert_stmt->close();

                    $profile_stmt = $mysqli->prepare(
                        'INSERT INTO artist_profiles (
                            account_id,
                            about,
                            availability_status,
                            mon_start, mon_end,
                            tue_start, tue_end,
                            wed_start, wed_end,
                            thu_start, thu_end,
                            fri_start, fri_end,
                            sat_start, sat_end,
                            sun_start, sun_end
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );

                    $profile_stmt->bind_param(
                        'issssssssssssssss',
                        $account_id,
                        $about,
                        $form['availability_status'],
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
                        $sun_end
                    );
                    $profile_stmt->execute();
                    $profile_id = (int)$profile_stmt->insert_id;
                    $profile_stmt->close();

                    if ($address !== null || $city !== null || $state !== null || $postal_code !== null) {
                        $map_stmt = $mysqli->prepare(
                            'INSERT INTO map_data (artist_profile_id, address, city, state, postal_code, created_at, updated_at)
                             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
                        );
                        $map_stmt->bind_param('issss', $profile_id, $address, $city, $state, $postal_code);
                        $map_stmt->execute();
                        $map_stmt->close();
                    }

                    $mysqli->commit();
                    header('Location: login.php');
                    exit();
                } catch (Throwable $t) {
                    $mysqli->rollback();
                    $error = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Artist Account - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="styles/settings.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Create Artist Account Form Section -->
    <div id="login-container">
        <div id="login-box">
            <div id="login-header">
                <h1>CREATE ARTIST ACCOUNT</h1>
            </div>
            <form id="login-form" action="create-account-artist.php" method="post">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?= e($form['first_name']) ?>" required>

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= e($form['last_name']) ?>" required>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= e($form['username']) ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <label for="about">About</label>
                <textarea id="about" name="about" rows="5"><?= e($form['about']) ?></textarea>

                <div id="availability-section">
                    <label for="availability-status">Availability Status</label>
                    <select id="availability-status" name="availability_status">
                        <option value="available" <?= $form['availability_status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="limited" <?= $form['availability_status'] === 'limited' ? 'selected' : '' ?>>Limited Availability</option>
                        <option value="booked" <?= $form['availability_status'] === 'booked' ? 'selected' : '' ?>>Fully Booked</option>
                        <option value="unavailable" <?= $form['availability_status'] === 'unavailable' ? 'selected' : '' ?>>Not Taking Clients</option>
                    </select>

                    <div id="weekly-schedule">
                        <h3>Weekly Hours</h3>
                        <div class="schedule-grid">
                            <div class="schedule-row <?= (empty($form['mon_start']) || empty($form['mon_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="mon-active" name="mon_active" <?= (!empty($form['mon_start']) && !empty($form['mon_end'])) ? 'checked' : '' ?>>
                                    <label for="mon-active">Monday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="mon-start" name="mon_start" value="<?= e($form['mon_start']) ?>" <?= (empty($form['mon_start']) || empty($form['mon_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="mon-end" name="mon_end" value="<?= e($form['mon_end']) ?>" <?= (empty($form['mon_start']) || empty($form['mon_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>

                            <div class="schedule-row <?= (empty($form['tue_start']) || empty($form['tue_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="tue-active" name="tue_active" <?= (!empty($form['tue_start']) && !empty($form['tue_end'])) ? 'checked' : '' ?>>
                                    <label for="tue-active">Tuesday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="tue-start" name="tue_start" value="<?= e($form['tue_start']) ?>" <?= (empty($form['tue_start']) || empty($form['tue_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="tue-end" name="tue_end" value="<?= e($form['tue_end']) ?>" <?= (empty($form['tue_start']) || empty($form['tue_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>

                            <div class="schedule-row <?= (empty($form['wed_start']) || empty($form['wed_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="wed-active" name="wed_active" <?= (!empty($form['wed_start']) && !empty($form['wed_end'])) ? 'checked' : '' ?>>
                                    <label for="wed-active">Wednesday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="wed-start" name="wed_start" value="<?= e($form['wed_start']) ?>" <?= (empty($form['wed_start']) || empty($form['wed_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="wed-end" name="wed_end" value="<?= e($form['wed_end']) ?>" <?= (empty($form['wed_start']) || empty($form['wed_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>

                            <div class="schedule-row <?= (empty($form['thu_start']) || empty($form['thu_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="thu-active" name="thu_active" <?= (!empty($form['thu_start']) && !empty($form['thu_end'])) ? 'checked' : '' ?>>
                                    <label for="thu-active">Thursday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="thu-start" name="thu_start" value="<?= e($form['thu_start']) ?>" <?= (empty($form['thu_start']) || empty($form['thu_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="thu-end" name="thu_end" value="<?= e($form['thu_end']) ?>" <?= (empty($form['thu_start']) || empty($form['thu_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>

                            <div class="schedule-row <?= (empty($form['fri_start']) || empty($form['fri_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="fri-active" name="fri_active" <?= (!empty($form['fri_start']) && !empty($form['fri_end'])) ? 'checked' : '' ?>>
                                    <label for="fri-active">Friday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="fri-start" name="fri_start" value="<?= e($form['fri_start']) ?>" <?= (empty($form['fri_start']) || empty($form['fri_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="fri-end" name="fri_end" value="<?= e($form['fri_end']) ?>" <?= (empty($form['fri_start']) || empty($form['fri_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>

                            <div class="schedule-row <?= (empty($form['sat_start']) || empty($form['sat_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="sat-active" name="sat_active" <?= (!empty($form['sat_start']) && !empty($form['sat_end'])) ? 'checked' : '' ?>>
                                    <label for="sat-active">Saturday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="sat-start" name="sat_start" value="<?= e($form['sat_start']) ?>" <?= (empty($form['sat_start']) || empty($form['sat_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="sat-end" name="sat_end" value="<?= e($form['sat_end']) ?>" <?= (empty($form['sat_start']) || empty($form['sat_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>

                            <div class="schedule-row <?= (empty($form['sun_start']) || empty($form['sun_end'])) ? 'closed' : '' ?>">
                                <div class="day-toggle">
                                    <input type="checkbox" id="sun-active" name="sun_active" <?= (!empty($form['sun_start']) && !empty($form['sun_end'])) ? 'checked' : '' ?>>
                                    <label for="sun-active">Sunday</label>
                                </div>
                                <div class="time-inputs">
                                    <input type="time" id="sun-start" name="sun_start" value="<?= e($form['sun_start']) ?>" <?= (empty($form['sun_start']) || empty($form['sun_end'])) ? 'disabled' : '' ?>>
                                    <span class="time-separator">-</span>
                                    <input type="time" id="sun-end" name="sun_end" value="<?= e($form['sun_end']) ?>" <?= (empty($form['sun_start']) || empty($form['sun_end'])) ? 'disabled' : '' ?>>
                                </div>
                                <span class="closed-label">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="location-section">
                    <h3>Studio Location</h3>

                    <label for="address">Street Address</label>
                    <input type="text" id="address" name="address" value="<?= e($form['address']) ?>">

                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?= e($form['city']) ?>">

                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?= e($form['state']) ?>">

                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?= e($form['postal_code']) ?>">
                </div>

                <?php if (!empty($error)) { ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php } ?>

                <button type="submit" id="login-button">Create</button>

            </form>
            <div id="signup-links">
                <a href="create-account.php" class="signup-link">Personal user looking to make an account? Click
                    here</a>
                <a href="login.php" class="signup-link">Have an account? Click here to Log In</a>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <div id="footer-placeholder"></div>

    <script>
        document.querySelectorAll('.schedule-row input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const row = this.closest('.schedule-row');
                const timeInputs = row.querySelectorAll('input[type="time"]');

                if (this.checked) {
                    row.classList.remove('closed');
                    timeInputs.forEach(function(input) {
                        input.disabled = false;
                    });
                } else {
                    row.classList.add('closed');
                    timeInputs.forEach(function(input) {
                        input.disabled = true;
                    });
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