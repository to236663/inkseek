<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/connect.php';

function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$myId = (int)($_SESSION['logged_in_account_id'] ?? 0);
$sendError = '';

// Resolve active conversation from query param
$conversationWithId = 0;
if (isset($_GET['conversation']) && ctype_digit((string)$_GET['conversation'])) {
    $conversationWithId = (int)$_GET['conversation'];
}

// Basic server-side message submit (no live polling/fetch)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipientIdRaw = $_POST['recipient_id'] ?? null;
    $recipientId = filter_var($recipientIdRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $messageBody = trim((string)($_POST['message_body'] ?? ''));

    if (!$recipientId) {
        $sendError = 'Invalid recipient.';
    } elseif ($recipientId === $myId) {
        $sendError = 'Cannot message yourself.';
    } elseif ($messageBody === '') {
        $sendError = 'Message cannot be empty.';
    } elseif (!$db_ready || !($mysqli instanceof mysqli)) {
        $sendError = 'Database unavailable.';
    } else {
        $recipientStmt = $mysqli->prepare('SELECT account_id FROM accounts WHERE account_id = ? LIMIT 1');

        if (!$recipientStmt) {
            $sendError = 'Could not verify recipient.';
        } else {
            $recipientStmt->bind_param('i', $recipientId);
            $recipientStmt->execute();
            $recipientExists = $recipientStmt->get_result()->fetch_row() !== null;
            $recipientStmt->close();

            if (!$recipientExists) {
                $sendError = 'Recipient not found.';
            } else {
                $insertStmt = $mysqli->prepare(
                    'INSERT INTO messages (sender_id, recipient_id, message_body) VALUES (?, ?, ?)'
                );

                if (!$insertStmt) {
                    $sendError = 'Could not send message.';
                } else {
                    $insertStmt->bind_param('iis', $myId, $recipientId, $messageBody);
                    $insertOk = $insertStmt->execute();
                    $insertStmt->close();

                    if ($insertOk) {
                        header('Location: messages.php?conversation=' . $recipientId);
                        exit();
                    }

                    $sendError = 'Could not send message.';
                }
            }
        }
    }

    // Keep the attempted conversation selected after validation failure.
    if ($recipientId && $recipientId > 0) {
        $conversationWithId = (int)$recipientId;
    }
}

// Sidebar: distinct conversation partners with last message
$conversations = [];
if ($db_ready && $mysqli instanceof mysqli) {
    $sidebarStmt = $mysqli->prepare(
        'SELECT
            a.account_id,
            a.username,
            a.profile_image_path,
            lm.message_body,
            lm.created_at
         FROM (
             SELECT
                 CASE WHEN sender_id = ? THEN recipient_id ELSE sender_id END AS other_id,
                 MAX(message_id) AS last_message_id
             FROM messages
             WHERE sender_id = ? OR recipient_id = ?
             GROUP BY other_id
         ) AS convos
         JOIN messages lm ON lm.message_id = convos.last_message_id
         JOIN accounts a ON a.account_id = convos.other_id
         ORDER BY lm.created_at DESC'
    );

    if ($sidebarStmt) {
        $sidebarStmt->bind_param('iii', $myId, $myId, $myId);
        $sidebarStmt->execute();
        $sidebarResult = $sidebarStmt->get_result();
        while ($row = $sidebarResult->fetch_assoc()) {
            $conversations[] = $row;
        }
        $sidebarStmt->close();
    }
}

// Default to most recent conversation if none specified
if ($conversationWithId === 0 && !empty($conversations)) {
    $conversationWithId = (int)$conversations[0]['account_id'];
}

// Active conversation: other user info + messages
$activeMessages = [];
$otherUser = null;

if ($conversationWithId > 0 && $conversationWithId !== $myId && $db_ready && $mysqli instanceof mysqli) {
    $userStmt = $mysqli->prepare(
        'SELECT account_id, username, profile_image_path FROM accounts WHERE account_id = ? LIMIT 1'
    );
    if ($userStmt) {
        $userStmt->bind_param('i', $conversationWithId);
        $userStmt->execute();
        $otherUser = $userStmt->get_result()->fetch_assoc() ?: null;
        $userStmt->close();
    }

    $msgStmt = $mysqli->prepare(
        'SELECT message_id, sender_id, message_body, created_at
         FROM messages
         WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?)
         ORDER BY created_at ASC'
    );
    if ($msgStmt) {
        $msgStmt->bind_param('iiii', $myId, $conversationWithId, $conversationWithId, $myId);
        $msgStmt->execute();
        $msgResult = $msgStmt->get_result();
        while ($row = $msgResult->fetch_assoc()) {
            $activeMessages[] = $row;
        }
        $msgStmt->close();
    }
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Inkseek</title>
    <link rel="icon" href="images/logos/inkseeklogosimple.png" type="image/png" sizes="16x16">
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/messages.css">
    <script src="scripts/components.js"></script>
    <script src="scripts/auth.js"></script>
</head>

<body class="messages-page">
    <!-- Navigation Bar -->
    <div id="navbar-placeholder"></div>

    <!-- Messages Container -->
    <div id="messages-container">
        <!-- Left Sidebar - Conversation List -->
        <div id="users-sidebar">
            <?php
            // If conversationWithId is set but not in sidebar (first message), prepend them
            $sidebarIds = array_column($conversations, 'account_id');
            if ($conversationWithId > 0 && !in_array($conversationWithId, $sidebarIds) && $otherUser !== null) {
                array_unshift($conversations, [
                    'account_id'        => $otherUser['account_id'],
                    'username'          => $otherUser['username'],
                    'profile_image_path' => $otherUser['profile_image_path'],
                    'message_body'      => '',
                    'created_at'        => null,
                ]);
            }
            ?>

            <?php if (empty($conversations)): ?>
                <p class="sidebar-empty">No conversations yet.</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv):
                    $isActive = (int)$conv['account_id'] === $conversationWithId;
                    $avatar   = $conv['profile_image_path'] ?: 'images/profilephotos/defaultProfile.jpg';
                ?>
                    <a class="user-item<?= $isActive ? ' active' : '' ?>"
                        href="messages.php?conversation=<?= (int)$conv['account_id'] ?>">
                        <img src="<?= e($avatar) ?>" alt="<?= e($conv['username']) ?>" class="user-avatar">
                        <span class="user-name"><?= e($conv['username']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Right Side - Message Section -->
        <div id="message-section">
            <?php if ($otherUser !== null): ?>
                <div id="conversation-header">
                    <img src="<?= e($otherUser['profile_image_path'] ?: 'images/profile photos/User/UP_4.jpg') ?>"
                        alt="<?= e($otherUser['username']) ?>" class="user-avatar">
                    <span class="conversation-username">@<?= e($otherUser['username']) ?></span>
                </div>
                <div id="messages-display">
                    <?php foreach ($activeMessages as $msg): ?>
                        <div class="message-bubble <?= (int)$msg['sender_id'] === $myId ? 'sent' : 'received' ?>">
                            <p><?= e($msg['message_body']) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($activeMessages)): ?>
                        <p class="no-messages">No messages yet. Say hello!</p>
                    <?php endif; ?>
                </div>
                <div id="message-input-container">
                    <form method="post" action="messages.php?conversation=<?= (int)$conversationWithId ?>" id="message-form">
                        <input type="hidden" name="send_message" value="1">
                        <input type="hidden" name="recipient_id" value="<?= (int)$conversationWithId ?>">
                        <input type="text" id="message-input" name="message_body"
                            placeholder="Message <?= e($otherUser['username']) ?>..." autocomplete="off">
                    </form>
                    <?php if ($sendError !== ''): ?>
                        <p id="message-status" style="margin: 8px 6px 0; font-size: 13px; color: #b84a4a; display: block;"><?= e($sendError) ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div id="messages-display">
                    <p class="no-messages">Select a conversation to start messaging.</p>
                </div>
                <div id="message-input-container">
                    <input type="text" id="message-input" placeholder="Message user..." disabled>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Keep view anchored to the newest messages after full-page submit.
        window.addEventListener('load', function() {
            const display = document.getElementById('messages-display');
            if (display) {
                display.scrollTop = display.scrollHeight;
            }
        });
    </script>
</body>

</html>