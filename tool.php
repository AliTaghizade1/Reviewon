<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$siteId = $_GET['id'] ?? null;

// دریافت اطلاعات سایت
$stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
$stmt->execute([$siteId]);
$currentUserId = $_SESSION['user_id'];
$site = $stmt->fetch();


if (!$site) {
    die("Site not found.");
}

$isOwner = ($site['owner_id'] === $_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Tool - Reviewon</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="tool-body">
    
    <!-- نوار ابزار بالا -->
    <header class="toolbar">
        <div class="toolbar-left">
            <a href="dashboard.php" class="btn-secondary">← Dashboard</a>
            <?php
                // استخراج نام دامنه (Host) از آدرس کامل
                $domain = parse_url($site['url'], PHP_URL_HOST);
                // اگر دامنه پیدا نشد، کل آدرس را نشان بده (برای اطمینان)
                $displayUrl = $domain ? $domain : $site['url'];
            ?>
            <span class="site-url"><?php echo htmlspecialchars($displayUrl); ?></span>
        </div>
        
        <div class="toolbar-center">
            <button class="device-btn active" data-device="desktop" title="Desktop"> <img class="icon-device-btn" src="image/monitor.svg" alt="image"> </button>
            <button class="device-btn" data-device="tablet" title="Tablet"> <img class="icon-device-btn" src="image/monitor-1.svg" alt="image"> </button>
            <button class="device-btn" data-device="mobile" title="Mobile"> <img class="icon-device-btn" src="image/monitor-2.svg" alt="image"> </button>
        </div>

        <div class="toolbar-right">
            <!--<span class="mode-indicator">Click on any element to comment</span> -->
            <?php if ($isOwner): ?>
                <button id="toolShareBtn" style="cursor: pointer !important;" class="btn-secondary btn-sm">Share</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- کانتینر اصلی -->
    <main class="tool-container">
        
        <!-- ناحیه نمایش سایت -->
        <div class="viewport">
            <div id="iframeWrapper" class="iframe-wrapper desktop">
                <!-- آی‌فریم به پراکسی ما اشاره دارد -->
                <iframe id="siteFrame" src="proxy.php?url=<?php echo urlencode($site['url']); ?>"></iframe>
            </div>
        </div>

        <!-- سایدبار کامنت‌ها (فعلا خالی، در فاز ۳ پر می‌شود) -->
        <aside class="sidebar">
            <h3 style="display: flex; justify-content: space-between; align-items: canter;">Comments
                    <select id="commentFilter">
                    <option value="current" selected>Current Page</option>
                    <option value="all">All Pages</option>
                </select>
            </h3>
            <div id="commentsList" class="comments-list">
                <!-- کامنت‌ها اینجا لود می‌شوند -->
            </div>
        </aside>

    </main>

<!-- پاپ‌آپ اولیه -->
<div id="welcomePopup" class="modal">
    <div class="modal-content" style="max-width: 500px; text-align: center; padding: 25px; height: fit-content !important;">
        <h3 style="margin-top: 0; color: #2563eb;">Welcome to Feedback Tool</h3>
        <p style="margin: 15px 0; line-height: 1.5;">
            1. <strong>To add a comment</strong>, simply click anywhere on the page.
        </p>
        <p style="margin: 15px 0; line-height: 1.5;">
            2. <strong>To navigate between pages</strong>, click on links within the page to switch pages and add comments.
        </p>
        <button id="welcomeBtn" style="
            background: #2563eb; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold;
        ">Got it</button>
    </div>
</div>

    <!-- مودال افزودن کامنت -->
    <div id="commentModal" class="modal">
        <div class="modal-content small-modal">
            <h3>New Comment</h3>
            <form id="commentForm">
                <input type="hidden" id="targetSelector" name="selector">
                <input type="hidden" id="targetDevice" name="deviceType">
                <div class="form-group">
                    <label>Comment:</label>
                    <textarea id="commentText" name="content" rows="6" style="width: 97%; padding-left: 1%;" required></textarea>
                </div>
                <div class="modal-actions" style="display: flex;">
                    <button type="button" class="btn-secondary close-modal" style="cursor: pointer !important;">Cancel</button>
                    <button type="submit" class="btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($isOwner): ?>
    <!-- Modal for Share Access (Inside Tool - Updated) -->
    <div id="toolShareModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Share Access</h2>
            
            <form id="toolShareForm" style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 1rem;">
                <input type="hidden" id="toolShareSiteId" name="siteId" value="<?php echo $siteId; ?>">
                <div class="form-group">
                    <label for="toolShareEmail">Add Collaborator (Email)</label>
                    <input type="email" id="toolShareEmail" name="email" placeholder="colleague@example.com" required>
                </div>
                <button type="submit" class="btn-primary">Grant Access</button>
            </form>

            <div class="access-list-container">
                <h4>People with access</h4>
                <ul id="toolAccessList" class="access-list">
                    <li>Loading...</li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // ارسال اطلاعات سایت به جاوااسکریپت
        var SITE_ID = <?php echo json_encode($siteId); ?>;
        var CURRENT_USER_ID = <?php echo json_encode($currentUserId); ?>;
        var SITE_URL = <?php echo json_encode($site['url']); ?>;
    </script>

    <script>
        // ✅ پاپ‌آپ اولیه (فقط یک بار برای هر سایت)
        if (!localStorage.getItem('welcomePopupSeen_' + SITE_ID)) {
            document.getElementById('welcomePopup').style.display = 'flex';
            localStorage.setItem('welcomePopupSeen_' + SITE_ID, 'true');
        }

        document.getElementById('welcomeBtn').addEventListener('click', () => {
            document.getElementById('welcomePopup').style.display = 'none';
        });
    </script>

    <!-- Modal for Delete Comment Confirmation -->
    <div id="deleteCommentModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <span class="close">&times;</span>
            <h3 style="color: #dc2626; margin-top: 0;">Delete Comment?</h3>
            <p>Are you sure you want to delete this comment? This action cannot be undone.</p>
            <div class="modal-actions" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                <button id="cancelCommentDeleteBtn" class="btn-secondary" style="cursor: pointer !important;">Cancel</button>
                <button id="confirmCommentDeleteBtn" class="btn-primary" style="background-color: #dc2626; border-color: #dc2626;">Yes, Delete</button>
            </div>
        </div>
    </div>

    <script src="js/tool.js"></script>
    
</body>
</html>
