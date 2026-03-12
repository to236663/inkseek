<?php
require_once __DIR__ . '/connect.php';
session_start();

if (
    isset($_SESSION['simulated_tattoo_ids'])
    && is_array($_SESSION['simulated_tattoo_ids'])
    && isset($mysqli)
    && $mysqli instanceof mysqli
) {
    $delete_link_stmt = $mysqli->prepare("DELETE FROM tattoo_style_tag WHERE tattoo_id = ?");
    $delete_tattoo_stmt = $mysqli->prepare("DELETE FROM tattoos WHERE tattoo_id = ? AND image_path = ?");
    $simulated_image_path = 'images/tattoos/uploadsim.jpg';

    if ($delete_link_stmt && $delete_tattoo_stmt) {
        foreach ($_SESSION['simulated_tattoo_ids'] as $tattoo_id) {
            $tattoo_id = (int)$tattoo_id;
            $delete_link_stmt->bind_param('i', $tattoo_id);
            $delete_link_stmt->execute();

            $delete_tattoo_stmt->bind_param('is', $tattoo_id, $simulated_image_path);
            $delete_tattoo_stmt->execute();
        }

        $delete_link_stmt->close();
        $delete_tattoo_stmt->close();
    }
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}

session_destroy();

// Redirect to previous page or login
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.html';
header("Location: " . $redirect);
exit();
