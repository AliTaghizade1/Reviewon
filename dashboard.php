<?php
session_start();
require 'config/db.php';

// بررسی لاگین بودن
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['email'];

// سایت‌های مالکیت شده
$stmt = $pdo->prepare("SELECT * FROM sites WHERE owner_id = ?");
$stmt->execute([$userId]);
$ownedSites = $stmt->fetchAll();

// سایت‌های اشتراکی
$stmtShared = $pdo->prepare("
    SELECT s.* FROM sites s 
    JOIN site_access sa ON s.id = sa.site_id 
    WHERE sa.shared_with_email = ? 
    AND s.owner_id != ? 
");
$stmtShared->execute([$userEmail, $userId]);
$sharedSites = $stmtShared->fetchAll();

// ترکیب
$allSites = array_merge($ownedSites, $sharedSites);

function site_display_name(string $url): string
{
    $host = parse_url($url, PHP_URL_HOST) ?: $url;
    $host = preg_replace('/^www\./i', '', strtolower($host));
    $parts = array_values(array_filter(explode('.', $host)));

    if (count($parts) >= 3 && strlen(end($parts)) === 2) {
        $name = $parts[count($parts) - 3];
    } elseif (count($parts) >= 2) {
        $name = $parts[count($parts) - 2];
    } else {
        $name = $parts[0] ?? $host;
    }

    $name = str_replace(['-', '_'], ' ', $name);
    return ucwords($name);
}

// ✅ Fix: مطمئن شو created_at درست convert می‌شه
usort($allSites, function($a, $b) {
    // اگر created_at خالیه، unix timestamp 0 بذار
    $dateA = $a['created_at'] ? strtotime($a['created_at']) : 0;
    $dateB = $b['created_at'] ? strtotime($b['created_at']) : 0;
    
    // جدیدترین اول (descending)
    if ($dateB == $dateA) return 0;
    return ($dateA > $dateB) ? -1 : 1;
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Reviewon</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>

<body>
    <header class="dashboard-header">
<div class="logo">Reviewon</div>
        <div class="user-info" style="display: flex; align-items: center; gap: 1rem">
            <!--<span><?php echo htmlspecialchars($_SESSION['name'] ?? $userEmail); ?></span> -->

            <a href="account.php" class="btn-primary">Profile</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="space-5"></div>
        <div class="actions">
            <div class="search-container">
                <input type="text" id="siteSearch" placeholder="Search..." class="search-input">
            </div>
            <button id="newSiteBtn" class="btn-primary">New Site</button>
        </div>

        <div class="site-list">
            <?php if (empty($allSites)): ?>
                <p>No sites yet. Click "New Site" to start.</p>
            <?php else: ?>
                <?php foreach ($allSites as $site): ?>
                    <div class="site-card">
                        <?php
                        $domain = parse_url($site['url'], PHP_URL_HOST);
                        $displayUrl = $domain ? $domain : $site['url'];
                        $displayName = site_display_name($site['url']);
                        $previewUrl = 'proxy.php?url=' . urlencode($site['url']);
                        ?>
                        <div class="site-preview">
                            <iframe
                                class="site-preview-frame"
                                src="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                title="Preview of <?php echo htmlspecialchars($displayUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                loading="lazy"
                                tabindex="-1"
                                referrerpolicy="no-referrer"
                            ></iframe>
                            <div class="preview-label"><?php echo htmlspecialchars($displayUrl); ?></div>
                        </div>
                        <h3 title="<?php echo htmlspecialchars($site['url']); ?>"><?php echo htmlspecialchars($displayName); ?></h3>
                        <div class="site-actions">
                            <a href="tool.php?id=<?php echo $site['id']; ?>" class="btn-secondary">Open</a>
                            <?php if ($site['owner_id'] === $userId): ?>
                                <button class="btn-share btn-secondary" style="cursor: pointer !important;" data-id="<?php echo $site['id']; ?>">Share Access</button>
                                <button class="btn-delete btn-secondary" style="cursor: pointer !important;" data-id="<?php echo $site['id']; ?>">Delete</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Modal for Share Access -->
        <div id="shareModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Share Access</h2>
                <form id="shareForm" style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
                    <input type="hidden" id="shareSiteId" name="siteId">
                    <div class="form-group">
                        <label for="shareEmail">Add Collaborator (Email)</label>
                        <input type="email" id="shareEmail" name="email" placeholder="colleague@example.com" required>
                    </div>
                    <button type="submit" class="btn-primary">Grant Access</button>
                </form>

                <div class="access-list-container">
                    <h4>People with access</h4>
                    <ul id="accessList" class="access-list">
                        <li>Loading...</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for New Site -->
    <div id="newSiteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Site</h2>
            <form id="newSiteForm" action="create_site.php" method="POST">
                <div class="form-group">
                    <label for="siteUrl">Website URL</label>
                    <input type="text" id="siteUrl" name="url" placeholder="example.com" required>
                </div>
                <button type="submit" class="btn-primary">Create</button>
            </form>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h3 style="color: #dc2626; margin-top: 0;">Delete Site?</h3>
            <p>Are you sure you want to delete this site? All comments and access permissions will be permanently removed.</p>
            <div class="modal-actions" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                <button id="cancelDeleteBtn" class="btn-secondary" style="cursor: pointer !important;">Cancel</button>
                <button id="confirmDeleteBtn" class="btn-primary" style="background-color: #dc2626; border-color: #dc2626;">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- Desktop-only Access Modal -->
    <div id="desktopOnlyModal" class="modal" aria-hidden="true">
        <div class="modal-content" style="max-width: 520px; text-align: center;">
            <span class="close" onclick="closeModal('desktopOnlyModal')" aria-label="Close">&times;</span>
            <h2 style="margin-top: 0;">این بخش فقط در دسکتاپ در دسترس است</h2>
            <p style="margin: 1rem 0 1.5rem; color: var(--text-secondary);">
                به خاطر تجربه کاربری بهتر، Dashboard در موبایل و تبلت محدود شده است.
            </p>

            <div style="display:flex; justify-content:center; gap: 12px; flex-wrap: wrap;">
                <a href="index.php" class="btn-secondary" style="text-decoration:none;">بازگشت به صفحه اصلی</a>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>

    <!-- Desktop-only modal for mobile/tablet -->
    <script>
        (function () {
            // Detect mobile/tablet ONLY (no viewport width checks)
            const ua = navigator.userAgent || '';
            const isMobileUA = /Android|iPhone|iPad|iPod|Mobile|Tablet/i.test(ua);

            if (!isMobileUA) return;

            const modalId = 'desktopOnlyModal';
            if (typeof openModal === 'function') {
                openModal(modalId);
            } else {
                var el = document.getElementById(modalId);
                if (el) el.style.display = 'block';
            }
        })();
    </script>
</body>

</html>

