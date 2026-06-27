<?php
if (!isset($_SESSION)) session_start();
include_once __DIR__ . '/lang.php';
?>
<footer class="footer">
    <div class="container">
<?= t('footer_copyright') ?>
        <div class="footer-links">
            <a href="privacy.php"><?= t('footer_privacy') ?></a>
            <a href="terms.php"><?= t('footer_terms') ?></a>
            <a href="mailto:vexelflow.agency@gmail.com"><?= t('footer_contact') ?></a>
        </div>
    </div>
</footer>