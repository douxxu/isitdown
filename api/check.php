<?php
function isSiteUp($url) {
    $output = null;
    $statusCode = null;
    exec("ping -c 1 -W 5 $url", $output, $statusCode); 
    
    if ($statusCode === 0) {
        preg_match('/time=(\d+\.?\d*) ms/', implode("\n", $output), $matches);
        $pingTime = $matches[1];
        
        if ($pingTime > 5000) {
            return [
                'status' => 'down',
                'message' => 'The response time is too long.'
            ];
        }
        
        $headers = get_headers("http://$url", 1);
        return [
            'status' => 'up',
            'ping' => $pingTime,
            'headers' => $headers
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
