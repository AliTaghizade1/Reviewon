<?php
// header.php
if (!isset($_SESSION)) session_start();
include_once __DIR__ . '/lang.php';
?>
<header>
<div class="logo">Reviewon</div>
    <nav class="header-nav">
        <a href="index.php#features" class="nav-link"><?= t('nav_features') ?></a>
        <a href="<?= build_lang_switch_url() ?>" class="nav-link lang-switch" title="<?= t('nav_toggle_label') ?>" data-lang-switch><?= t('nav_toggle') ?></a>
        <!--<a href="#why-us" class="nav-link">Why Us</a> -->
        <button id="loginBtn" class="btn-primary nav-cta"><?= t('nav_login') ?></button>
    </nav>
</header>

<!-- Auth Modal -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('authModal')">&times;</span>

        <div id="authLoginView">
            <h2><?= t('auth_welcome') ?></h2>
            <form id="authForm" action="auth.php" method="POST">
                <div class="form-group">
                    <label for="email"><?= t('auth_login_label') ?></label>
                    <input type="email" id="email" name="email" placeholder="<?= t('auth_placeholder_email') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password"><?= t('auth_password_label') ?></label>
                    <input type="password" id="password" name="password" placeholder="<?= t('auth_placeholder_password') ?>" required>
                </div>
                <button type="submit" class="btn-primary"><?= t('auth_login_submit') ?></button>
                <p class="hint"><?= t('auth_hint') ?></p>
                <!--<p class="hint" style="margin-top: 0.75rem;">
                    <a href="#" id="showForgotPassword" class="auth-forgot-link">Forgot password?</a>
                </p>-->
            </form>
        </div>

        <div id="authForgotView" style="display: none;">
            <h2><?= t('auth_forgot_title') ?></h2>
            <form id="forgotForm" action="password_reset_request.php" method="POST">
                <div class="form-group">
                    <label for="forgotEmail"><?= t('auth_login_label') ?></label>
                    <input type="email" id="forgotEmail" name="email" placeholder="<?= t('auth_placeholder_email') ?>" required>
                </div>
                <button type="submit" class="btn-primary"><?= t('auth_forgot_submit') ?></button>
                <p class="hint" style="margin-top: 0.75rem;">
                    <a href="#" id="backToLogin" class="auth-forgot-link"><?= t('auth_forgot_back') ?></a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// Hero CTA opens modal (safe: wait until DOM is ready)
window.addEventListener('DOMContentLoaded', () => {
    const heroBtn = document.getElementById('heroLoginBtn');
    if (!heroBtn) return;

    heroBtn.addEventListener('click', () => {
        if (typeof openModal === 'function') {
            openModal('authModal');
        } else {
            const modal = document.getElementById('authModal');
            if (modal) modal.style.display = 'block';
        }
    });
});
</script>
