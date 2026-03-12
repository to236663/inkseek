<?php
// Include the database connection file
require_once("connect.php");

// Start the session so we can store and access session variables
session_start();

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the username from the form input
    $username = $_POST['username'];

    // Get the first name from the form input
    $first_name = $_POST['first_name'];

    // Get the last name from the form input
    $last_name = $_POST['last_name'];

    // Get the email from the form input
    $email = $_POST['email'];

    // Get the password from the form input
    $password = $_POST['password'];

    // Get the confirm password from the form input
    $confirm_password = $_POST['confirm_password'];

    // Check if the password and confirm password match before doing anything
    if ($_POST['confirm_password'] != $password) {

        // If they don't match, set an error message
        $error = "Passwords do not match.";
    } else {

        // Check if the username already exists in the database
        $check_stmt = $mysqli->prepare("SELECT * FROM accounts WHERE username = ?");

        // Bind the username to the "?" placeholder
        $check_stmt->bind_param("s", $username);

        // Execute the check query
        $check_stmt->execute();

        // Get the results
        $check_result = $check_stmt->get_result();

        // If a row is found it means the username is already taken
        if ($check_result->num_rows > 0) {

            // Set an error message telling the user the username is taken
            $error = "Username is already taken. Please choose another.";
        } else {

            // Check if the email already exists in the database
            $email_stmt = $mysqli->prepare("SELECT * FROM accounts WHERE email = ?");

            // Bind the email to the "?" placeholder
            $email_stmt->bind_param("s", $email);

            // Execute the email check query
            $email_stmt->execute();

            // Get the results
            $email_result = $email_stmt->get_result();

            // If a row is found it means the email is already registered
            if ($email_result->num_rows > 0) {

                // Set an error message telling the user the email is already in use
                $error = "An account with that email already exists.";
            } else {

                // All checks passed so insert the new account into the database
                // role is set to "user" automatically since this is the regular user form
                $insert_stmt = $mysqli->prepare("INSERT INTO accounts (role, username, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?, ?)");

                // Bind all the values to the "?" placeholders
                // "ssssss" means all 6 values are strings
                $insert_stmt->bind_param("ssssss", $role, $username, $first_name, $last_name, $email, $password);

                // Set the role to "user" since this is the regular account creation page
                $role = "user";

                // Execute the insert query to save the new account
                $insert_stmt->execute();

                // Check if the insert was successful by seeing if any rows were affected
                if ($insert_stmt->affected_rows > 0) {

                    // Account created successfully so redirect to the login page
                    header("Location: login.php");

                    // Stop the rest of the PHP script from running
                    exit();
                } else {

                    // Something went wrong with the insert
                    $error = "Something went wrong. Please try again.";
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
    <title>Create Account - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/login.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body>
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Create Account Form Section -->
    <div id="login-container">
        <div id="login-box">
            <div id="login-header">
                <h1>CREATE USER ACCOUNT</h1>
            </div>

            <!--
                action="create-account.php" submits the form to this PHP file
                method="post" sends the data via POST so PHP can read it with $_POST
            -->
            <form id="login-form" action="create-account.php" method="post">

                <label for="first_name">First Name</label>
                <!-- name="first_name" matches $_POST['first_name'] in the PHP -->
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name</label>
                <!-- name="last_name" matches $_POST['last_name'] in the PHP -->
                <input type="text" id="last_name" name="last_name" required>

                <label for="username">Username</label>
                <!-- name="username" matches $_POST['username'] in the PHP -->
                <input type="text" id="username" name="username" required>

                <label for="email">Email</label>
                <!-- name="email" matches $_POST['email'] in the PHP -->
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <!-- name="password" matches $_POST['password'] in the PHP -->
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirm Password</label>
                <!-- name="confirm_password" matches $_POST['confirm_password'] in the PHP -->
                <input type="password" id="confirm_password" name="confirm_password" required>

                <!--
                    If the $error variable is set in PHP display it here in red
                    so the user knows what went wrong
                -->
                <?php if (!empty($error)) { ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php } ?>

                <!-- type="submit" makes this button submit the form to the server -->
                <button type="submit" id="login-button">Create</button>

            </form>

            <div id="signup-links">
                <a href="create-account-artist.php" class="signup-link">Artist looking to make an account? Click here</a>
                <a href="login.php" class="signup-link">Have an account? Click here to Log In</a>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <div id="footer-placeholder"></div>
</body>

</html>
<?php $mysqli->close(); ?>