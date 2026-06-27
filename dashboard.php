<?php
session_start();
include_once __DIR__ . '/includes/lang.php';
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
<html lang="<?= $lang === 'fa' ? 'fa' : 'en' ?>" dir="<?= $dir ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('dashboard_title') ?> - Reviewon</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>

<body dir="<?= $dir ?>">
    <header class="dashboard-header">
        <div class="logo">Reviewon</div>
        <div class="user-info" style="display: flex; align-items: center; gap: 1rem">
            <!--<span><?php echo htmlspecialchars($_SESSION['name'] ?? $userEmail); ?></span> -->
            <a href="<?= build_lang_switch_url() ?>" class="nav-link lang-switch" title="<?= t('nav_toggle_label') ?>"><?= t('nav_toggle') ?></a>
            <a href="account.php" class="btn-primary"><?= t('nav_profile') ?></a>
            <a href="logout.php" class="btn-logout"><?= t('nav_logout') ?></a>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="space-5"></div>
        <div class="actions">
            <div class="search-container">
                <input type="text" id="siteSearch" placeholder="<?= t('dashboard_search_placeholder') ?>" class="search-input">
            </div>
            <button id="newSiteBtn" class="btn-primary"><?= t('dashboard_new_site') ?></button>
        </div>

        <div class="site-list">
            <?php if (empty($allSites)): ?>
                <p><?= t('dashboard_no_sites') ?></p>
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
                            <a href="tool.php?id=<?php echo $site['id']; ?>" class="btn-secondary"><?= t('dashboard_open') ?></a>
                            <?php if ($site['owner_id'] === $userId): ?>
                                <button class="btn-share btn-secondary" style="cursor: pointer !important;" data-id="<?php echo $site['id']; ?>"><?= t('dashboard_share') ?></button>
                                <button class="btn-delete btn-secondary" style="cursor: pointer !important;" data-id="<?php echo $site['id']; ?>"><?= t('dashboard_delete') ?></button>
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
                <h2><?= t('dashboard_share_modal_title') ?></h2>
                <form id="shareForm" style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
                    <input type="hidden" id="shareSiteId" name="siteId">
                    <div class="form-group">
                        <label for="shareEmail"><?= t('dashboard_share_email_label') ?></label>
                        <input type="email" id="shareEmail" name="email" placeholder="<?= t('dashboard_share_email_placeholder') ?>" required>
                    </div>
                    <button type="submit" class="btn-primary"><?= t('dashboard_share_button') ?></button>
                </form>

                <div class="access-list-container">
                    <h4><?= t('dashboard_access_list_title') ?></h4>
                    <ul id="accessList" class="access-list">
                        <li><?= t('dashboard_access_loading') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for New Site -->
    <div id="newSiteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><?= t('dashboard_add_site_title') ?></h2>
            <form id="newSiteForm" action="create_site.php" method="POST">
                <div class="form-group">
                    <label for="siteUrl">Website URL</label>
                    <input type="text" id="siteUrl" name="url" placeholder="example.com" required>
                </div>
                <button type="submit" class="btn-primary"><?= t('dashboard_create_site') ?></button>
            </form>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h3 style="color: #dc2626; margin-top: 0;"><?= t('dashboard_delete_confirm_title') ?></h3>
            <p><?= t('dashboard_delete_confirm_text') ?></p>
            <div class="modal-actions" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                <button id="cancelDeleteBtn" class="btn-secondary" style="cursor: pointer !important;"><?= t('dashboard_cancel') ?></button>
                <button id="confirmDeleteBtn" class="btn-primary" style="background-color: #dc2626; border-color: #dc2626;"><?= t('dashboard_confirm_delete') ?></button>
            </div>
        </div>
    </div>

    <!-- Desktop-only Access Modal -->
    <div id="desktopOnlyModal" class="modal" aria-hidden="true" style="display:none; overflow:hidden; z-index: 100000;">
        <div class="modal-content" style="max-width: 520px; text-align: center; margin: 0; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) !important;">
            <!--<span class="close" onclick="closeModal('desktopOnlyModal')" aria-label="Close">&times;</span>-->
            <h2 style="margin-top: 0;">Desktop access only</h2>
            <p style="margin: 1rem 0 1.5rem; color: var(--text-secondary);">This page is available only on desktop devices.</p>

            <div style="display:flex; justify-content:center; gap: 12px; flex-wrap: wrap;">
                <a href="logout.php" class="btn-secondary" style="text-decoration:none;">Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Prevent scrolling while the modal is shown -->
    <script>
        (function () {
            const modal = document.getElementById('desktopOnlyModal');
            if (!modal) return;

            const originalClose = window.closeModal;
            window.closeModal = function (modalId) {
                if (modalId === 'desktopOnlyModal') {
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                }
                if (typeof originalClose === 'function') return originalClose(modalId);
                const el = document.getElementById(modalId);
                if (el) el.style.display = 'none';
            };

            const observer = new MutationObserver(function () {
                const isShown = modal.style.display === 'block';
                if (isShown) {
                    document.documentElement.style.overflow = 'hidden';
                    document.body.style.overflow = 'hidden';
                } else {
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                }
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
        })();
    </script>


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

