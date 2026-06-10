<?php
function proxy_error_page(string $message): void
{
    http_response_code(502);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;padding:24px;color:#1f2937}code{background:#f3f4f6;padding:2px 6px;border-radius:4px}</style></head><body>';
    echo '<h2>Could not load this website</h2>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

if (!isset($_GET['url'])) {
    proxy_error_page('No URL provided.');
}

$url = trim($_GET['url']);
$parts = parse_url($url);

if (
    !filter_var($url, FILTER_VALIDATE_URL) ||
    empty($parts['scheme']) ||
    empty($parts['host']) ||
    !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
) {
    proxy_error_page('Invalid URL.');
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => '',
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ReviewonProxy/1.0)',
]);

$html = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($html === false || $html === '') {
    proxy_error_page($curlError ?: 'The remote server returned an empty response.');
}

if ($statusCode >= 400) {
    proxy_error_page('The remote server returned HTTP status ' . $statusCode . '.');
}

if ($contentType && stripos($contentType, 'text/html') === false && stripos($contentType, 'application/xhtml+xml') === false) {
    header('Content-Type: ' . $contentType);
    echo $html;
    exit;
}

$originalUrlJson = json_encode($url, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$baseHref = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
$baseTag = '<base href="' . $baseHref . '">';

// Meta CSP from the fetched page can block our injected helper script inside the iframe.
$html = preg_replace('/<meta[^>]+http-equiv=["\']?Content-Security-Policy["\']?[^>]*>/i', '', $html);

if (preg_match('/<head\b[^>]*>/i', $html)) {
    $html = preg_replace('/(<head\b[^>]*>)/i', '$1' . $baseTag, $html, 1);
} else {
    $html = $baseTag . $html;
}

$scriptInject = <<<JS
<script>
(function() {
    const originalUrl = $originalUrlJson;
    const proxyEndpoint = window.location.origin + window.location.pathname;

    function shouldSkipUrl(value) {
        return !value ||
            value.startsWith('#') ||
            value.startsWith('javascript:') ||
            value.startsWith('mailto:') ||
            value.startsWith('tel:') ||
            value.startsWith('data:');
    }

    function proxyUrl(value) {
        try {
            return proxyEndpoint + '?url=' + encodeURIComponent(new URL(value, originalUrl).href);
        } catch (error) {
            return value;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a[href]').forEach(function(link) {
            const href = link.getAttribute('href');
            if (!shouldSkipUrl(href)) {
                link.setAttribute('href', proxyUrl(href));
                link.removeAttribute('target');
            }
        });

        document.querySelectorAll('form[action]').forEach(function(form) {
            const action = form.getAttribute('action');
            if (!shouldSkipUrl(action)) {
                form.setAttribute('action', proxyUrl(action));
            }
        });
    });
})();
</script>
JS;

if (stripos($html, '</body>') !== false) {
    $html = str_ireplace('</body>', $scriptInject . '</body>', $html);
} else {
    $html .= $scriptInject;
}

echo $html;
?>
