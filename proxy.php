<?php
function proxy_endpoint(): string
{
    $scheme = 'http';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = strtolower(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]) === 'https' ? 'https' : 'http';
    } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/proxy.php';

    return $scheme . '://' . $host . $script;
}

function proxy_error_page(string $message): void
{
    http_response_code(502);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;padding:24px;color:#1f2937}code{background:#f3f4f6;padding:2px 6px;border-radius:4px}</style></head><body>';
    echo '<h2>Could not load this website</h2>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

function should_skip_url(string $value): bool
{
    $value = trim($value);

    return $value === '' ||
        strpos($value, '#') === 0 ||
        preg_match('/^(javascript|mailto|tel|data|blob):/i', $value);
}

function remove_dot_segments(string $path): string
{
    $isAbsolute = strpos($path, '/') === 0;
    $segments = explode('/', $path);
    $output = [];

    foreach ($segments as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }

        if ($segment === '..') {
            array_pop($output);
            continue;
        }

        $output[] = $segment;
    }

    return ($isAbsolute ? '/' : '') . implode('/', $output);
}

function absolute_url(string $value, string $baseUrl): string
{
    $value = html_entity_decode(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (should_skip_url($value)) {
        return $value;
    }

    if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $value)) {
        return $value;
    }

    $base = parse_url($baseUrl);
    $scheme = $base['scheme'] ?? 'https';
    $host = $base['host'] ?? '';
    $port = isset($base['port']) ? ':' . $base['port'] : '';
    $origin = $scheme . '://' . $host . $port;

    if (strpos($value, '//') === 0) {
        return $scheme . ':' . $value;
    }

    if (strpos($value, '/') === 0) {
        return $origin . $value;
    }

    $basePath = $base['path'] ?? '/';
    $baseDir = substr($basePath, -1) === '/' ? $basePath : dirname($basePath) . '/';

    return $origin . remove_dot_segments($baseDir . $value);
}

function proxied_url(string $value, string $baseUrl): string
{
    if (should_skip_url($value)) {
        return $value;
    }

    $absolute = absolute_url($value, $baseUrl);

    if (is_proxy_url($absolute)) {
        return $absolute;
    }

    return proxy_endpoint() . '?url=' . rawurlencode($absolute);
}

function is_proxy_url(string $value): bool
{
    $valueParts = parse_url($value);
    $proxyParts = parse_url(proxy_endpoint());

    if (!$valueParts || !$proxyParts) {
        return false;
    }

    $valueHost = strtolower($valueParts['host'] ?? '');
    $proxyHost = strtolower($proxyParts['host'] ?? '');
    $valuePort = (string) ($valueParts['port'] ?? '');
    $proxyPort = (string) ($proxyParts['port'] ?? '');
    $valuePath = $valueParts['path'] ?? '';
    $proxyPath = $proxyParts['path'] ?? '';
    $query = $valueParts['query'] ?? '';

    parse_str($query, $params);

    return $valueHost === $proxyHost &&
        $valuePort === $proxyPort &&
        $valuePath === $proxyPath &&
        isset($params['url']);
}

function unwrap_proxy_url(string $value): string
{
    $guard = 0;

    while ($guard < 5 && is_proxy_url($value)) {
        $query = parse_url($value, PHP_URL_QUERY) ?: '';
        parse_str($query, $params);

        if (empty($params['url']) || !is_string($params['url'])) {
            break;
        }

        $value = $params['url'];
        $guard++;
    }

    return $value;
}

function rewrite_srcset(string $value, string $baseUrl): string
{
    $candidates = explode(',', $value);
    $rewritten = [];

    foreach ($candidates as $candidate) {
        $candidate = trim($candidate);
        if ($candidate === '') {
            continue;
        }

        $parts = preg_split('/\s+/', $candidate, 2);
        $url = $parts[0] ?? '';
        $descriptor = $parts[1] ?? '';
        $rewritten[] = proxied_url($url, $baseUrl) . ($descriptor ? ' ' . $descriptor : '');
    }

    return implode(', ', $rewritten);
}

function rewrite_html_urls(string $html, string $baseUrl): string
{
    $html = preg_replace_callback(
        '/\s(href|src|action|poster)=([\'"])(.*?)\2/is',
        function (array $match) use ($baseUrl): string {
            $attribute = strtolower($match[1]);
            $quote = $match[2];
            $value = $match[3];

            return ' ' . $attribute . '=' . $quote . htmlspecialchars(proxied_url($value, $baseUrl), ENT_QUOTES, 'UTF-8') . $quote;
        },
        $html
    );

    return preg_replace_callback(
        '/\s(srcset)=([\'"])(.*?)\2/is',
        function (array $match) use ($baseUrl): string {
            $quote = $match[2];
            $value = $match[3];

            return ' srcset=' . $quote . htmlspecialchars(rewrite_srcset($value, $baseUrl), ENT_QUOTES, 'UTF-8') . $quote;
        },
        $html
    );
}

function rewrite_css_urls(string $css, string $baseUrl): string
{
    $css = preg_replace_callback(
        '/url\(\s*([\'"]?)(?!data:|blob:|#|%23)([^\'")]+)\1\s*\)/i',
        function (array $match) use ($baseUrl): string {
            return 'url("' . proxied_url($match[2], $baseUrl) . '")';
        },
        $css
    );

    return preg_replace_callback(
        '/@import\s+([\'"])(?!data:|blob:|#|%23)(.*?)\1/i',
        function (array $match) use ($baseUrl): string {
            return '@import ' . $match[1] . proxied_url($match[2], $baseUrl) . $match[1];
        },
        $css
    );
}

if (!isset($_GET['url'])) {
    proxy_error_page('No URL provided.');
}

$url = unwrap_proxy_url(trim($_GET['url']));
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
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_ENCODING => '',
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ReviewonProxy/1.0)',
]);

$html = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

if ($html === false || $html === '') {
    proxy_error_page($curlError ?: 'The remote server returned an empty response.');
}

if ($statusCode >= 400) {
    proxy_error_page('The remote server returned HTTP status ' . $statusCode . '.');
}

$baseUrl = $effectiveUrl ?: $url;

if ($contentType && stripos($contentType, 'text/css') !== false) {
    header('Content-Type: ' . $contentType);
    echo rewrite_css_urls($html, $baseUrl);
    exit;
}

if ($contentType && stripos($contentType, 'text/html') === false && stripos($contentType, 'application/xhtml+xml') === false) {
    header('Content-Type: ' . $contentType);
    echo $html;
    exit;
}

$html = rewrite_html_urls($html, $baseUrl);

$originalUrlJson = json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$baseHref = htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8');
$baseTag = '<base href="' . $baseHref . '">';

// Meta CSP from the fetched page can block our injected helper script inside the iframe.
$html = preg_replace('/<meta[^>]+http-equiv=["\']?Content-Security-Policy["\']?[^>]*>/i', '', $html);
// Rewritten proxied assets no longer match the original SRI hashes.
$html = preg_replace('/\s+integrity=(["\']).*?\1/i', '', $html);

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
        if (!value ||
            value.startsWith('#') ||
            value.startsWith('javascript:') ||
            value.startsWith('mailto:') ||
            value.startsWith('tel:') ||
            value.startsWith('data:')) {
            return true;
        }

        try {
            const absoluteUrl = new URL(value, window.location.href);
            const proxyUrl = new URL(proxyEndpoint);
            return absoluteUrl.origin === proxyUrl.origin &&
                absoluteUrl.pathname === proxyUrl.pathname &&
                absoluteUrl.searchParams.has('url');
        } catch (error) {
            return false;
        }
    }

    function proxyUrl(value) {
        try {
            if (shouldSkipUrl(value)) return value;
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
