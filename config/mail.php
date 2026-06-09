<?php
// config/mail.php — Resend defaults; optional SMTP in config/mail.local.php

define('RESEND_API_KEY', 're_bC2rnUsP_Gk1BjuT8PiLPigr1J4mBG8rm');
define('RESEND_FROM_EMAIL', 'onboarding@resend.dev');
define('RESEND_FROM_NAME', 'Reviewon');

$mailLocal = __DIR__ . '/mail.local.php';
if (is_readable($mailLocal)) {
    require $mailLocal;
}
