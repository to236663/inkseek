<?php
require 'connection.php';
session_start();

$account_id = $_SESSION['user_id'];
$schedule = [];
$success = false;

// Get profile_id for this account
$stmt = $mysqli->prepare("SELECT profile_id FROM artist_profiles WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$profile_id = $profile['profile_id'];
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    $status = $_POST['availability_status'] ?? 'available';

    // Build the UPDATE query dynamically
    $stmt = $mysqli->prepare("UPDATE artist_profiles SET 
        availability_status = ?,
        mon_start = ?, mon_end = ?,
        tue_start = ?, tue_end = ?,
        wed_start = ?, wed_end = ?,
        thu_start = ?, thu_end = ?,
        fri_start = ?, fri_end = ?,
        sat_start = ?, sat_end = ?,
        sun_start = ?, sun_end = ?
        WHERE profile_id = ?");

    // For each day, use the posted time if checkbox is checked, otherwise NULL
    $mon_start = !empty($_POST['mon_active']) ? $_POST['mon_start'] : null;
    $mon_end   = !empty($_POST['mon_active']) ? $_POST['mon_end']   : null;
    $tue_start = !empty($_POST['tue_active']) ? $_POST['tue_start'] : null;
    $tue_end   = !empty($_POST['tue_active']) ? $_POST['tue_end']   : null;
    $wed_start = !empty($_POST['wed_active']) ? $_POST['wed_start'] : null;
    $wed_end   = !empty($_POST['wed_active']) ? $_POST['wed_end']   : null;
    $thu_start = !empty($_POST['thu_active']) ? $_POST['thu_start'] : null;
    $thu_end   = !empty($_POST['thu_active']) ? $_POST['thu_end']   : null;
    $fri_start = !empty($_POST['fri_active']) ? $_POST['fri_start'] : null;
    $fri_end   = !empty($_POST['fri_active']) ? $_POST['fri_end']   : null;
    $sat_start = !empty($_POST['sat_active']) ? $_POST['sat_start'] : null;
    $sat_end   = !empty($_POST['sat_active']) ? $_POST['sat_end']   : null;
    $sun_start = !empty($_POST['sun_active']) ? $_POST['sun_start'] : null;
    $sun_end   = !empty($_POST['sun_active']) ? $_POST['sun_end']   : null;

    $stmt->bind_param("ssssssssssssssi",
        $status,
        $mon_start, $mon_end,
        $tue_start, $tue_end,
        $wed_start, $wed_end,
        $thu_start, $thu_end,
        $fri_start, $fri_end,
        $sat_start, $sat_end,
        $sun_start, $sun_end,
        $profile_id
    );
    $stmt->execute();
    $stmt->close();

    $success = true;
}

// Load existing schedule
$stmt = $mysqli->prepare("SELECT 
    availability_status,
    mon_start, mon_end,
    tue_start, tue_end,
    wed_start, wed_end,
    thu_start, thu_end,
    fri_start, fri_end,
    sat_start, sat_end,
    sun_start, sun_end
    FROM artist_profiles WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$availability_status = $row['availability_status'] ?? 'available';

// Build $schedule array to match the HTML expectations
$days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
foreach ($days as $day) {
    if (!empty($row[$day . '_start'])) {
        $schedule[$day] = [
            'start' => $row[$day . '_start'],
            'end'   => $row[$day . '_end']
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
                    <img src="images/profile photos/Artist/Profile_1.jpg" alt="Profile Picture">
                </div>
                <button id="edit-picture-btn">Edit Profile Picture</button>
            </div>

            <!-- Edit Form -->
<div id="settings-form">
    <form method="POST" action="artist-settings.php">

        <?php if ($success): ?>
            <p class="success-msg">Changes saved successfully!</p>
        <?php endif; ?>

        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your name" value="Naomi Sinclair">

        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" value="SilverSpire_Ink">

        <label for="about">About</label>
        <textarea id="about" name="about" rows="5" placeholder="Tell us about yourself">Specializing in neo-traditional and illustrative tattoos with a focus on nature-inspired designs. 10+ years of experience creating custom pieces that tell your story.</textarea>

        <!-- Availability -->
        <div id="availability-section">
            <label for="availability-status">Availability Status</label>
            <select id="availability-status" name="availability_status">
                <option value="available"   <?= $availability_status === 'available'   ? 'selected' : '' ?>>Available</option>
                <option value="limited"     <?= $availability_status === 'limited'     ? 'selected' : '' ?>>Limited Availability</option>
                <option value="booked"      <?= $availability_status === 'booked'      ? 'selected' : '' ?>>Fully Booked</option>
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

        <button type="button" id="edit-portfolio-btn">Edit Portfolio</button>
        <button type="submit" id="save-changes-btn">Save Changes</button>

    </form>
</div>


<!-- Account Actions -->
<div id="account-actions">
    <a href="user-settings.html" class="action-link">Convert to User Account</a>
    <a href="#" class="action-link">Delete Account</a>
</div>

</div><!-- end settings-column -->
</div><!-- end settings-container -->

<!-- Footer -->
<div id="footer-placeholder"></div>

<script>
    document.querySelectorAll('.schedule-row input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
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