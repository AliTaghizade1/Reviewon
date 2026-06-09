<?php
session_start();

require 'config/db.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPass = $_POST['new_pass'] ?? '';
    $confirm = $_POST['new_pass_confirm'] ?? '';

    $error = '';
    if (!$token) {
        $error = 'Invalid token.';
    } elseif ($newPass === '' || $confirm === '') {
        $error = 'Please fill both password fields.';
    } elseif ($newPass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'Token not found.';
        } else {
            if (strtotime($row['expires_at']) < time()) {
                $error = 'Token has expired.';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $stmtUpd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtUpd->execute([$hash, $row['user_id']]);

                // حذف توکن‌های قبلی برای امنیت
                $stmtDel = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $stmtDel->execute([$row['user_id']]);

                // پیام و ریدایرکت بعد از تلاش به لاگین
                $_SESSION['flash_reset_success'] = 'Password updated successfully. Please log in with your new password.';
                header('Location: index.php');
                exit;
            }
        }
    }
}

$tokenValid = false;
if ($token) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $tokenValid = ((int)$stmt->fetchColumn() > 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="dashboard-container" style="max-width:720px; margin: 4rem auto;">
    <h2>Set New Password</h2>

    <?php if (!empty($error)): ?>
        <div class="alert danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <?php if (!$tokenValid && empty($error)): ?>
        <div class="alert danger">Invalid or expired token.</div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
        <form method="POST" action="reset_password.php" class="account-form" style="margin-top: 1.5rem;">
            <input type="hidden" name="token" value="<?php echo h($token); ?>">

            <div class="form-group">
                <label for="new_pass">New password</label>
                <input type="password" id="new_pass" name="new_pass" required>
            </div>

            <div class="form-group">
                <label for="new_pass_confirm">Confirm new password</label>
                <input type="password" id="new_pass_confirm" name="new_pass_confirm" required>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 1rem;">Confirm</button>
        </form>
    <?php endif; ?>

    <p style="margin-top: 1rem;"><a href="index.php">Back to home</a></p>
</div>
</body>
</html>

