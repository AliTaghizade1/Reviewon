<?php
// account.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require 'config/db.php';          // اتصال PDO





// اطمینان از ورود کاربر
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// اطلاعات جاری کاربر
$user = $pdo->prepare("SELECT id, email, name FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch(PDO::FETCH_ASSOC);

// ذخیره نام در session برای استفاده در Dashboard/Tool
if (!empty($user['name'])) {
    $_SESSION['name'] = $user['name'];
}

// پردازش فرم

$successMsg = $errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_name') {
        $newName = trim($_POST['full_name'] ?? '');
        if ($newName !== '') {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $_SESSION['user_id']]);

            $_SESSION['name'] = $newName;
            $user['name'] = $newName;
        $successMsg .= "Name updated.<br>";
        }
    }

    if ($action === 'update_password') {
        if (!empty($_POST['current_pass']) && !empty($_POST['new_pass']) && !empty($_POST['new_pass_confirm'])) {
            $userRow = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $userRow->execute([$_SESSION['user_id']]);
            $currentHash = $userRow->fetchColumn();

            if (password_verify($_POST['current_pass'], $currentHash)) {
                $newHash = password_hash($_POST['new_pass'], PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newHash, $_SESSION['user_id']]);
                $successMsg .= "Password changed.<br>";
            } else {
                $errorMsg .= "The current password is incorrect.<br>";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="logo">Reviewon</div>
    <nav class="header-nav">
        <a href="dashboard.php" class="btn-primary">Dashboard</a>
<!--<a href="account.php" class="nav-link">Account</a> -->
<a href="logout.php" class="btn-logout">Logout</a>

    </nav>

</header>

<div class="space-5"></div>
<main class="dashboard-container">
    <h2>Account</h2>

    <?php if ($errorMsg): ?>
        <div class="alert danger"><?php echo $errorMsg; ?></div>
    <?php endif; ?>
    <?php if ($successMsg): ?>
        <div class="alert success"><?php echo $successMsg; ?></div>
    <?php endif; ?>

    <div class="space-1"></div>

    <!-- 1) Personal information -->
    <section class="account-section">
        <h3>Personal information</h3>

        <div class="account-form-wrapper">
            <!-- Change name form -->
            <form method="post" class="account-form" style="margin-bottom: 1.25rem;" >
                <input type="hidden" name="action" value="update_name">

                <div class="form-group">
                    <label for="full_name">Full name</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>

                <div class="form-actions" style="margin-top: 0.75rem;">
                    <button type="submit" class="btn-primary">Save name</button>
                </div>
            </form>

            <div class="space-1"></div>

            <h3>Change password</h3>

            <!-- Change password form -->
            <form method="post" class="account-form">
                <input type="hidden" name="action" value="update_password">

                <div class="form-group">
                    <label for="current_pass">Current password</label>
                    <input type="password" id="current_pass" name="current_pass" required>
                </div>

                <div class="form-group">
                    <label for="new_pass">New password</label>
                    <input type="password" id="new_pass" name="new_pass" required>
                </div>

                <div class="form-group">
                    <label for="new_pass_confirm">Confirm new password</label>
                    <input type="password" id="new_pass_confirm" name="new_pass_confirm" required>
                </div>

                <div class="form-actions" style="margin-top: 0.75rem;">
                    <button type="submit" class="btn-primary">Save password</button>
                </div>
            </form>
        </div>
    </section>

    <div class="space-3"></div>

    <!-- 2) Plans -->
    <section class="plans-section">
        <h3>Plans</h3>
        <div class="plans-grid" style="justify-content: center;">
            <div class="plan-box" style="max-width: 720px;">
                <h4 style="margin-top: 0;">
                    Do you want to increase your account's capabilities?
                </h4>
                <p style="margin-bottom: 0;">
                    Send us a message at
                    <a href="mailto:AliTaghizade.Contact@gmail.com"
                       class="btn-secondary"
                       style="display: inline-block; margin-left: 0.75rem; padding: 0.55rem 1rem;">
                        AliTaghizade.Contact@gmail.com
                    </a>
                </p>
            </div>
        </div>
    </section>
</main>


<script src="js/script.js"></script>
</body>
</html>

