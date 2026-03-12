<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the shared database connection file.
require_once __DIR__ . '/connect.php';

// Start the session so we can store and access session variables like $_SESSION['logged_in']
session_start();

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if the user is not already logged in before processing login
    if (!isset($_SESSION['logged_in'])) {

        // Get the username from the form input and store it in a variable
        $username = $_POST['username'];

        // Get the password from the form input and store it in a variable
        $password = $_POST['password'];

        // Prepare a SQL query that selects a user where the username matches
        $stmt = $mysqli->prepare("SELECT * FROM accounts WHERE username = ?");

        // Bind the $username variable to the "?" placeholder
        // "s" means the value is a string
        $stmt->bind_param("s", $username);

        // Execute (run) the prepared SQL query against the database
        $stmt->execute();

        // Get the results returned from the query
        $result = $stmt->get_result();

        // Fetch the result as an object so we can access columns like $row->username
        $row = $result->fetch_object();

        if ($row && $password == $row->password) {

            // Mark the user as logged in by setting a session variable
            $_SESSION['logged_in'] = true;

            // Store the account_id in the session
            $_SESSION['logged_in_account_id'] = $row->account_id;

            // Store the role in the session e.g. "artist", "user", "admin"
            $_SESSION['logged_in_access_level'] = $row->role;

            // Store the username in the session
            $_SESSION['logged_in_username'] = $row->username;

            // Store the first name in the session
            $_SESSION['logged_in_first_name'] = $row->first_name;

            // Store the last name in the session
            $_SESSION['logged_in_last_name'] = $row->last_name;

            // Store the email in the session
            $_SESSION['logged_in_email'] = $row->email;

            // Store the profile image path in the session
            $_SESSION['logged_in_profile_image'] = $row->profile_image_path;

            // Check if the logged in user has the role of "artist"
            if ($row->role == "artist") {

                // Prepare a second query to get the extra artist profile details
                // We match the account_id in artist_profiles to the logged in user's account_id
                $artist_stmt = $mysqli->prepare("SELECT * FROM artist_profiles WHERE account_id = ?");

                // Bind the user's account_id to the "?" placeholder
                // "i" means the value is an integer
                $artist_stmt->bind_param("i", $row->account_id);

                // Execute the artist profile query
                $artist_stmt->execute();

                // Get the results from the artist profile query
                $artist_result = $artist_stmt->get_result();

                // Fetch the artist profile row as an object
                $artist_row = $artist_result->fetch_object();

                // Check that artist profile data was actually found before saving it
                if ($artist_row) {

                    // Store the artist profile_id in the session
                    $_SESSION['artist_profile_id'] = $artist_row->profile_id;

                    // Store the artist about/bio text in the session
                    $_SESSION['artist_about'] = $artist_row->about;

                    // Store the artist minimum rate in the session
                    $_SESSION['artist_min_rate'] = $artist_row->min_rate;

                    // Store the artist hourly rate in the session
                    $_SESSION['artist_hourly_rate'] = $artist_row->hourly_rate;

                    // Store the artist day rate in the session
                    $_SESSION['artist_day_rate'] = $artist_row->day_rate;
                }
            }

            // Redirect logged-in users to the DB-backed discover page.
            header("Location: discover.php");

            // Stop the rest of the PHP script from running after the redirect
            exit();
        } else {

            // If username or password does not match, show an error message.
            $error = "Invalid username or password.";
        }
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/login.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Login Form Section -->
    <div id="login-container">
        <div id="login-box">
            <div id="login-header">
                <h1>LOG IN</h1>
            </div>

            <!--
                action="login.php" submits the form to this same PHP file
                method="post" sends the data via POST so PHP can read it with $_POST
            -->
            <form id="login-form" action="login.php" method="post">

                <label for="username">Username</label>
                <!-- name="username" matches $_POST['username'] in the PHP -->
                <input type="text" id="username" name="username" required>

                <label for="password">Password</label>
                <!-- name="password" matches $_POST['password'] in the PHP -->
                <input type="password" id="password" name="password" required>

                <!--
                    If the $error variable is set in PHP (meaning login failed),
                    display it here so the user knows their credentials were wrong
                -->
                <?php if (!empty($error)) { ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php } ?>

                <!--
                    type="submit" makes this button submit the form to the server
                    The onclick and JavaScript have been removed since PHP handles the login now
                -->
                <button type="submit" id="login-button">Log In</button>

            </form>

            <div id="signup-links">
                <p>Don't Have an account?</p>
                <a href="create-account.php" class="signup-link">Personal users click here to Create One</a>
                <a href="create-account-artist.php" class="signup-link">Artists click here to Create One</a>
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