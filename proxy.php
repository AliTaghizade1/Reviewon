<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        die("Invalid URL");
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    // ✅ تغییر کلیدی: دکود کردن URL
    $originalUrl = urldecode($_GET['url']); // ✅ این خط را اصلاح کنید
    
    $scriptInject = <<<JS
<script>
const originalUrl = '$originalUrl';
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href]');
    links.forEach(link => {
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            const absoluteUrl = new URL(href, originalUrl).href;
            //link.setAttribute('href', 'proxy.php?url=' + encodeURIComponent(absoluteUrl));
            link.setAttribute('href', 'proxy.php?url=' + absoluteUrl);
        }
    });
});
</script>
JS;

    $html = str_replace('</body>', $scriptInject . '</body>', $html);
    echo $html;
} else {
    echo "No URL provided.";
}
?>