<?php
// header.php
// اطمینان از شروع session برای نمایش دکمه‌های شرطی
if (!isset($_SESSION)) session_start();
?>
<header>
<div class="logo">Reviewon</div>
    <nav class="header-nav">
        <a href="index.php#features" class="nav-link">Features</a>
        <!--<a href="#why-us" class="nav-link">Why Us</a> -->
        <button id="loginBtn" class="btn-primary nav-cta">Login</button>
    </nav>
</header>

<!-- Auth Modal -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('authModal')">&times;</span>

        <div id="authLoginView">
            <h2>Welcome</h2>
            <form id="authForm" action="auth.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-primary">Login / Sign Up</button>
                <p class="hint">No account? One will be created automatically.</p>
                <!--<p class="hint" style="margin-top: 0.75rem;">
                    <a href="#" id="showForgotPassword" class="auth-forgot-link">Forgot password?</a>
                </p>-->
            </form>
        </div>

        <div id="authForgotView" style="display: none;">
            <h2>Forgot Password</h2>
            <form id="forgotForm" action="password_reset_request.php" method="POST">
                <div class="form-group">
                    <label for="forgotEmail">Your email</label>
                    <input type="email" id="forgotEmail" name="email" placeholder="your@email.com" required>
                </div>
                <button type="submit" class="btn-primary">Send reset link</button>
                <p class="hint" style="margin-top: 0.75rem;">
                    <a href="#" id="backToLogin" class="auth-forgot-link">Back to login</a>
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
