<?php
// header.php
// اطمینان از شروع session برای نمایش دکمه‌های شرطی
if (!isset($_SESSION)) session_start();
?>
<footer class="footer">
    <div class="container">
&copy; 2024 Reviewon. All rights reserved.
        <div class="footer-links">
            <a href="privacy.php">Privacy</a>
            <a href="terms.php">Terms</a>
            <a href="mailto:vexelflow.agency@gmail.com">Contact</a>
        </div>
    </div>
</footer>