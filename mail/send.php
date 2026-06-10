<?php
// mail/send.php — tries SMTP first (any recipient), then Resend

require_once __DIR__ . '/../config/mail.php';

require_once __DIR__ . '/send_resend.php';
require_once __DIR__ . '/send_smtp.php';

function app_send_email(string $to, string $subject, string $html): array
{
    // فقط Resend. (SMTP کلاً نادیده گرفته می‌شود)

    $resend = resend_send_email($to, $subject, $html);

    if ($resend['success']) {
        return $resend;
    }

    $message = resend_user_message($resend);
    if (!smtp_is_configured()) {
        $message .= ' For Gmail/other addresses, copy config/mail.local.php.example to config/mail.local.php and set SMTP (Google App Password).';
    }

    return ['success' => false, 'message' => $message];
}

function resend_user_message(array $result): string
{
    if (!empty($result['raw']['message']) && is_string($result['raw']['message'])) {
        return $result['raw']['message'];
    }
    return $result['message'] ?? 'Could not send the email.';
}
