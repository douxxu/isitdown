<?php
function isSiteUp($url) {
    $parsedUrl = parse_url($url, PHP_URL_HOST) ?? $url;
    $domain = filter_var($parsedUrl, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

    if ($domain === false) {
        return [
            'status' => 'error',
            'message' => 'Invalid URL provided.'
        ];
    }

    $output = null;
    $statusCode = null;

    exec("ping -c 1 -W 5 " . escapeshellarg($domain), $output, $statusCode);

    if ($statusCode === 0) {
        preg_match('/time=(\d+\.?\d*) ms/', implode("\n", $output), $matches);
        $pingTime = $matches[1];

        if ($pingTime > 5000) {
            return [
                'status' => 'down',
                'message' => 'The response time is too long.'
            ];
        }

        $headers = @get_headers("http://$domain", 1);
        return [
            'status' => 'up',
            'ping' => $pingTime,
            'headers' => $headers ?: 'Unable to retrieve headers'
        ];
    } else {
        return [
            'status' => 'down',
            'message' => 'The site is down.'
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];
    echo json_encode(isSiteUp($url));
}
?>
