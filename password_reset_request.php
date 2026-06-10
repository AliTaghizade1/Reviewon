<?php
session_start();

require 'config/db.php';
require 'config/app.php';
require 'mail/send.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function wants_json_response(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return ($_POST['ajax'] ?? '') === '1' || stripos($accept, 'application/json') !== false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $success = false;

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email.';
    } else {
        // جلوگیری از enumeration
        $message = 'If that email exists, you will receive a reset link shortly.';
        $success = true;

        try {
            $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $stmtDel = $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = ?');
                $stmtDel->execute([$user['id']]);

                $stmtIns = $pdo->prepare(
                    'INSERT INTO password_reset_tokens (token, user_id, expires_at) VALUES (?, ?, ?)'
                );
                $stmtIns->execute([$token, $user['id'], $expiresAt]);

                $resetLink = app_base_url() . '/reset_password.php?token=' . urlencode($token);

                $subject = 'Reset your password';
                $html = '<p>Hi,</p>'
                    . '<p>You requested a password reset for your Reviewon account.</p>'
                    . '<p><a href="' . h($resetLink) . '">Set a new password</a></p>'
                    . '<p>This link expires in 1 hour.</p>'
                    . '<p>If you did not request this, you can ignore this email.</p>';

                $send = app_send_email($user['email'], $subject, $html);
                if (!$send['success']) {
                    error_log('password_reset_request: email send failed: ' . ($send['message'] ?? 'unknown'));
                    $message = $send['message'] ?? 'Could not send the email. Please try again later.';
                    $success = false;
                }
            }
        } catch (Throwable $e) {
            error_log('password_reset_request: ' . $e->getMessage());
            $message = 'Something went wrong. Please try again.';
            $success = false;
        }
    }

    if (wants_json_response()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="dashboard-container" style="max-width:720px; margin: 4rem auto;">
    <h2>Forgot Password</h2>

    <?php if (!empty($message)): ?>
        <div class="alert success"><?php echo h($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="password_reset_request.php" class="account-form" style="margin-top: 1.5rem;">
        <div class="form-group">
            <label for="email">Your email</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="btn-primary" style="margin-top: 1rem;">Send reset link</button>
    </form>

    <p style="margin-top: 1rem;"><a href="index.php">Back to home</a></p>
</div>
</body>
</html>
