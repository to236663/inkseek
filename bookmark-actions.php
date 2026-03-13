<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=UTF-8');

function bookmark_response(int $statusCode, array $payload): void
{
    global $mysqli;

    http_response_code($statusCode);
    echo json_encode($payload);

    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }

    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bookmark_response(405, [
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    bookmark_response(500, [
        'success' => false,
        'message' => 'Database connection is unavailable.',
    ]);
}

/** @var mysqli $db */
$db = $mysqli;

$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$accountId = isset($_SESSION['logged_in_account_id']) ? (int)$_SESSION['logged_in_account_id'] : 0;
$accessLevel = (string)($_SESSION['logged_in_access_level'] ?? '');
$action = (string)($_POST['action'] ?? '');
$tattooId = filter_input(
    INPUT_POST,
    'tattoo_id',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$loggedIn || $accountId <= 0 || $accessLevel !== 'user') {
    bookmark_response(403, [
        'success' => false,
        'message' => 'Only logged-in users can bookmark tattoos.',
    ]);
}

if ($action !== 'add_bookmark' && $action !== 'remove_bookmark') {
    bookmark_response(400, [
        'success' => false,
        'message' => 'Invalid bookmark action.',
    ]);
}

if ($tattooId === false || $tattooId === null) {
    bookmark_response(400, [
        'success' => false,
        'message' => 'A valid tattoo was not provided.',
    ]);
}

$tattooStmt = $db->prepare('SELECT tattoo_id FROM tattoos WHERE tattoo_id = ? LIMIT 1');
if (!$tattooStmt) {
    bookmark_response(500, [
        'success' => false,
        'message' => 'Could not verify the tattoo.',
    ]);
}

$tattooStmt->bind_param('i', $tattooId);
$tattooStmt->execute();
$tattooExists = $tattooStmt->get_result()->fetch_assoc() ?: null;
$tattooStmt->close();

if (!$tattooExists) {
    bookmark_response(404, [
        'success' => false,
        'message' => 'Tattoo not found.',
    ]);
}

$existingStmt = $db->prepare(
    'SELECT 1
     FROM bookmarks
     WHERE account_id = ? AND tattoo_id = ?
     LIMIT 1'
);

if (!$existingStmt) {
    bookmark_response(500, [
        'success' => false,
        'message' => 'Could not check bookmark status.',
    ]);
}

$existingStmt->bind_param('ii', $accountId, $tattooId);
$existingStmt->execute();
$alreadyBookmarked = $existingStmt->get_result()->fetch_row() !== null;
$existingStmt->close();

if ($action === 'remove_bookmark') {
    if (!$alreadyBookmarked) {
        bookmark_response(200, [
            'success' => true,
            'bookmarked' => false,
        ]);
    }

    $deleteStmt = $db->prepare('DELETE FROM bookmarks WHERE account_id = ? AND tattoo_id = ?');
    if (!$deleteStmt) {
        bookmark_response(500, [
            'success' => false,
            'message' => 'Could not remove bookmark.',
        ]);
    }

    $deleteStmt->bind_param('ii', $accountId, $tattooId);
    $deleted = $deleteStmt->execute();
    $deleteStmt->close();

    if ($deleted) {
        bookmark_response(200, [
            'success' => true,
            'bookmarked' => false,
        ]);
    }

    bookmark_response(500, [
        'success' => false,
        'message' => 'Could not remove bookmark.',
    ]);
}

if ($alreadyBookmarked) {
    bookmark_response(200, [
        'success' => true,
        'bookmarked' => true,
        'already_bookmarked' => true,
    ]);
}

$insertStmt = $db->prepare('INSERT INTO bookmarks (account_id, tattoo_id) VALUES (?, ?)');
if (!$insertStmt) {
    bookmark_response(500, [
        'success' => false,
        'message' => 'Could not save bookmark.',
    ]);
}

$insertStmt->bind_param('ii', $accountId, $tattooId);
$inserted = $insertStmt->execute();
$insertErrorCode = $insertStmt->errno;
$insertStmt->close();

if ($inserted || $insertErrorCode === 1062) {
    bookmark_response(200, [
        'success' => true,
        'bookmarked' => true,
        'already_bookmarked' => $insertErrorCode === 1062,
    ]);
}

bookmark_response(500, [
    'success' => false,
    'message' => 'Could not save bookmark.',
]);
