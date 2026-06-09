<?php
// mail/send_resend.php — send via Resend HTTP API

require_once __DIR__ . '/../config/mail.php';

function resend_send_email(string $to, string $subject, string $html): array
{
    $apiKey = (defined('RESEND_API_KEY') ? constant('RESEND_API_KEY') : '');





    if (!$apiKey || $apiKey === 'PUT_YOUR_RESEND_API_KEY_HERE') {
        return ['success' => false, 'message' => 'Resend API key is not configured.'];
    }

    $from = (defined('RESEND_FROM_NAME') && RESEND_FROM_NAME !== '')
        ? RESEND_FROM_NAME . ' <' . RESEND_FROM_EMAIL . '>'

        : RESEND_FROM_EMAIL;

    $from = (string)$from;

    if (!defined('RESEND_FROM_EMAIL') || empty(RESEND_FROM_EMAIL)) {
        return ['success' => false, 'message' => 'RESEND_FROM_EMAIL is not configured.'];
    }



    $payload = [
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $html,
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_SSL_VERIFYPEER => true,

        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'message' => 'cURL error: ' . $err];
    }

    curl_close($ch);

    $decoded = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['success' => true, 'raw' => $decoded];
    }

    $apiMessage = is_array($decoded) && !empty($decoded['message'])
        ? $decoded['message']
        : 'Resend request failed (HTTP ' . $httpCode . ').';

    return [
        'success' => false,
        'message' => $apiMessage,
        'httpCode' => $httpCode,
        'raw' => $decoded ?? $response,
    ];
}

