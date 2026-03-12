<?php
session_start();
require_once __DIR__ . '/connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit();
}

$account_id = (int)($_SESSION['logged_in_account_id'] ?? 0);
$action = trim((string)($_POST['action'] ?? ''));
$redirect = trim((string)($_POST['redirect'] ?? 'user-settings.php'));

function sanitize_redirect(string $value): string
{
    $allowed = [
        'user-settings.php',
        'artist-settings.php',
        'user-profile.php',
        'artist-profile.php',
        'index.html',
    ];

    $path = strtok($value, '?');
    if ($path === false) {
        return 'index.html';
    }

    if (in_array($path, $allowed, true)) {
        return $value;
    }

    return 'index.html';
}

$redirect = sanitize_redirect($redirect);

if ($account_id <= 0 || !isset($mysqli) || !($mysqli instanceof mysqli)) {
    header('Location: index.html');
    exit();
}

if ($action === 'convert_to_artist') {
    $mysqli->begin_transaction();

    try {
        $role_stmt = $mysqli->prepare("UPDATE accounts SET role = 'artist' WHERE account_id = ?");
        $role_stmt->bind_param('i', $account_id);
        $role_stmt->execute();
        $role_stmt->close();

        $profile_stmt = $mysqli->prepare('SELECT profile_id FROM artist_profiles WHERE account_id = ? LIMIT 1');
        $profile_stmt->bind_param('i', $account_id);
        $profile_stmt->execute();
        $profile_result = $profile_stmt->get_result();
        $profile_row = $profile_result ? $profile_result->fetch_assoc() : null;
        $profile_stmt->close();

        if ($profile_row) {
            $profile_id = (int)$profile_row['profile_id'];
        } else {
            $insert_profile = $mysqli->prepare('INSERT INTO artist_profiles (account_id) VALUES (?)');
            $insert_profile->bind_param('i', $account_id);
            $insert_profile->execute();
            $profile_id = (int)$insert_profile->insert_id;
            $insert_profile->close();
        }

        $mysqli->commit();

        $_SESSION['logged_in_access_level'] = 'artist';
        $_SESSION['artist_profile_id'] = $profile_id;

        header('Location: artist-settings.php');
        exit();
    } catch (Throwable $t) {
        $mysqli->rollback();
        header('Location: ' . $redirect);
        exit();
    }
}

if ($action === 'convert_to_user') {
    $role_stmt = $mysqli->prepare("UPDATE accounts SET role = 'user' WHERE account_id = ?");
    $role_stmt->bind_param('i', $account_id);
    $role_stmt->execute();
    $role_stmt->close();

    $_SESSION['logged_in_access_level'] = 'user';

    // Keep artist_profile_id in session optional; data in DB remains untouched by design.
    header('Location: user-settings.php');
    exit();
}

if ($action === 'delete_account') {
    $mysqli->begin_transaction();

    try {
        // Remove link rows for tattoos owned by this account before deleting tattoos.
        $stmt = $mysqli->prepare(
            'DELETE tts FROM tattoo_style_tag tts
             INNER JOIN tattoos t ON t.tattoo_id = tts.tattoo_id
             WHERE t.artist_id = ?'
        );
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove other users\' bookmarks that reference this account\'s tattoos.
        $stmt = $mysqli->prepare(
            'DELETE b FROM bookmarks b
             INNER JOIN tattoos t ON t.tattoo_id = b.tattoo_id
             WHERE t.artist_id = ?'
        );
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove tattoos owned by this account.
        $stmt = $mysqli->prepare('DELETE FROM tattoos WHERE artist_id = ?');
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove bookmarks created by this account.
        $stmt = $mysqli->prepare('DELETE FROM bookmarks WHERE account_id = ?');
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove follows where this account is follower or following.
        $stmt = $mysqli->prepare('DELETE FROM account_follows WHERE follower_account_id = ? OR following_account_id = ?');
        $stmt->bind_param('ii', $account_id, $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove reviews written by or written about this account as artist.
        $stmt = $mysqli->prepare('DELETE FROM reviews WHERE reviewer_id = ? OR artist_id = ?');
        $stmt->bind_param('ii', $account_id, $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove artist style tags for this account.
        $stmt = $mysqli->prepare('DELETE FROM artist_style_tag WHERE artist_id = ?');
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove map data tied to this account\'s artist profile.
        $stmt = $mysqli->prepare(
            'DELETE md FROM map_data md
             INNER JOIN artist_profiles ap ON ap.profile_id = md.artist_profile_id
             WHERE ap.account_id = ?'
        );
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Remove artist profile row.
        $stmt = $mysqli->prepare('DELETE FROM artist_profiles WHERE account_id = ?');
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        // Finally remove account row.
        $stmt = $mysqli->prepare('DELETE FROM accounts WHERE account_id = ?');
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        header('Location: index.html');
        exit();
    } catch (Throwable $t) {
        $mysqli->rollback();
        header('Location: ' . $redirect);
        exit();
    }
}

header('Location: ' . $redirect);
exit();
