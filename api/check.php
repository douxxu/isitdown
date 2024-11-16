<?php
function isSiteUp($url) {
    $parsedUrl = parse_url($url, PHP_URL_HOST) ?? $url;
    $domain = filter_var($parsedUrl, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

    if ($domain === false) {
        return [
            'status' => 'error',
            'message' => htmlspecialchars('Invalid URL provided.')
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
                'message' => htmlspecialchars('The response time is too long.')
            ];
        }

        $headers = @get_headers("http://$domain", 1);
        
        if ($headers) {
            foreach ($headers as $key => $value) {
                $headers[$key] = htmlspecialchars(
                    is_array($value) ? implode(', ', array_map('htmlspecialchars', $value)) : $value
                );
            }
        } else {
            $headers = htmlspecialchars('Unable to retrieve headers');
        }
        
        return [
            'status' => 'up',
            'ping' => htmlspecialchars($pingTime),
            'headers' => $headers
        ];
    } else {
        return [
            'status' => 'down',
            'message' => htmlspecialchars('The site is down.')
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = htmlspecialchars($_POST['url']);
    echo json_encode(isSiteUp($url));
}
?>
