<?php
session_start();
session_destroy();

// Redirect to previous page or login
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.html';
header("Location: " . $redirect);
exit();
