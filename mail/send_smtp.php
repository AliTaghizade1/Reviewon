<?php
// mail/send_smtp.php — SMTP (Gmail / any provider) when Resend test mode is too limited

function smtp_is_configured(): bool
{
    return defined('SMTP_HOST') && defined('SMTP_USER') && defined('SMTP_PASS')
        && SMTP_HOST !== '' && SMTP_USER !== '' && SMTP_PASS !== '';
}

function smtp_send_email(string $to, string $subject, string $html): array
{
    if (!smtp_is_configured()) {
        return ['success' => false, 'message' => 'SMTP is not configured.'];
    }

    $host = SMTP_HOST;
    $port = (defined('SMTP_PORT') && SMTP_PORT) ? (int) SMTP_PORT : 587;
    $user = SMTP_USER;
    $pass = SMTP_PASS;
    $fromEmail = (defined('SMTP_FROM_EMAIL') && SMTP_FROM_EMAIL !== '') ? SMTP_FROM_EMAIL : $user;
    $fromName = (defined('SMTP_FROM_NAME') && SMTP_FROM_NAME !== '') ? SMTP_FROM_NAME : 'Reviewon';
    $fromHeader = $fromName . ' <' . $fromEmail . '>';

    $socket = @stream_socket_client(
        "tcp://{$host}:{$port}",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        return ['success' => false, 'message' => "SMTP connection failed: {$errstr}"];
    }

    stream_set_timeout($socket, 30);

    try {
        smtp_expect(smtp_read($socket), [220]);

        smtp_cmd($socket, 'EHLO localhost');
        smtp_expect(smtp_read($socket), [250]);

        if ($port === 587) {
            smtp_cmd($socket, 'STARTTLS');
            smtp_expect(smtp_read($socket), [220]);

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS failed.');
            }

            smtp_cmd($socket, 'EHLO localhost');
            smtp_expect(smtp_read($socket), [250]);
        }

        smtp_cmd($socket, 'AUTH LOGIN');
        smtp_expect(smtp_read($socket), [334]);
        smtp_cmd($socket, base64_encode($user));
        smtp_expect(smtp_read($socket), [334]);
        smtp_cmd($socket, base64_encode($pass));
        smtp_expect(smtp_read($socket), [235]);

        smtp_cmd($socket, 'MAIL FROM:<' . $fromEmail . '>');
        smtp_expect(smtp_read($socket), [250]);

        smtp_cmd($socket, 'RCPT TO:<' . $to . '>');
        smtp_expect(smtp_read($socket), [250, 251]);

        smtp_cmd($socket, 'DATA');
        smtp_expect(smtp_read($socket), [354]);

        $body = "From: {$fromHeader}\r\n"
            . "To: <{$to}>\r\n"
            . 'Subject: ' . smtp_encode_subject($subject) . "\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "\r\n"
            . $html . "\r\n.\r\n";

        fwrite($socket, $body);
        smtp_expect(smtp_read($socket), [250]);

        smtp_cmd($socket, 'QUIT');
        smtp_read($socket);

        return ['success' => true, 'via' => 'smtp'];
    } catch (Throwable $e) {
        return ['success' => false, 'message' => 'SMTP error: ' . $e->getMessage()];
    } finally {
        fclose($socket);
    }
}

function smtp_cmd($socket, string $cmd): void
{
    fwrite($socket, $cmd . "\r\n");
}

function smtp_read($socket): string
{
    $data = '';
    while ($line = fgets($socket, 515)) {
        $data .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $data;
}

function smtp_expect(string $response, array $codes): void
{
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $codes, true)) {
        throw new RuntimeException(trim($response));
    }
}

function smtp_encode_subject(string $subject): string
{
    if (preg_match('/[^\x20-\x7E]/', $subject)) {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
    return $subject;
}
