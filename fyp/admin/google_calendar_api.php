<?php
// google_calendar_api.php
require_once 'google_calendar_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['google_calendar_token'])) {
    echo json_encode(['error' => 'Not authenticated', 'auth_url' => AUTH_URL . '?' . http_build_query([
        'client_id' => CLIENT_ID,
        'redirect_uri' => REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/calendar.events',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ])]);
    exit;
}

$token = $_SESSION['google_calendar_token'];
$accessToken = $token['access_token'];

if (isset($token['expires_in']) && time() > $token['created'] + $token['expires_in']) {
    $params = [
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'refresh_token' => $token['refresh_token'],
        'grant_type'    => 'refresh_token'
    ];
    $ch = curl_init(TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    $newToken = json_decode($response, true);
    if (isset($newToken['access_token'])) {
        $token['access_token'] = $newToken['access_token'];
        $token['expires_in']   = $newToken['expires_in'];
        $token['created']      = time();
        $_SESSION['google_calendar_token'] = $token;
        $accessToken = $token['access_token'];
    } else {
        unset($_SESSION['google_calendar_token']);
        echo json_encode(['error' => 'Token refresh failed', 'auth_url' => AUTH_URL . '?' . http_build_query([
            'client_id' => CLIENT_ID,
            'redirect_uri' => REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ])]);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$headers = [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
];

function apiRequest($url, $method = 'GET', $data = null) {
    global $headers;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method == 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method == 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$httpCode, json_decode($response, true)];
}

switch ($action) {
    case 'list':
        $start = $_GET['start'] ?? date('Y-m-d\TH:i:sP', strtotime('-1 month'));
        $end   = $_GET['end']   ?? date('Y-m-d\TH:i:sP', strtotime('+3 month'));
        $url = API_BASE . '?' . http_build_query([
            'maxResults' => 250,
            'orderBy'    => 'startTime',
            'singleEvents' => 'true',
            'timeMin'    => $start,
            'timeMax'    => $end
        ]);
        list($code, $data) = apiRequest($url);
        $events = [];
        if ($code == 200 && isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $startDate = $item['start']['dateTime'] ?? $item['start']['date'];
                $endDate   = $item['end']['dateTime']   ?? $item['end']['date'];
                $events[] = [
                    'id'          => $item['id'],
                    'title'       => $item['summary'] ?? '',
                    'start'       => $startDate,
                    'end'         => $endDate,
                    'description' => $item['description'] ?? ''
                ];
            }
        }
        echo json_encode($events);
        break;

    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        $eventData = [
            'summary'     => $data['title'],
            'description' => $data['description'] ?? '',
            'start'       => ['dateTime' => $data['start'], 'timeZone' => 'Asia/Kuala_Lumpur'],
            'end'         => ['dateTime' => $data['end'],   'timeZone' => 'Asia/Kuala_Lumpur']
        ];
        list($code, $result) = apiRequest(API_BASE, 'POST', $eventData);
        echo json_encode(['success' => $code == 200, 'id' => $result['id'] ?? null]);
        break;

    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $eventId = $data['id'];
        $eventData = [
            'summary'     => $data['title'],
            'description' => $data['description'] ?? '',
            'start'       => ['dateTime' => $data['start'], 'timeZone' => 'Asia/Kuala_Lumpur'],
            'end'         => ['dateTime' => $data['end'],   'timeZone' => 'Asia/Kuala_Lumpur']
        ];
        list($code, $result) = apiRequest(API_BASE . '/' . $eventId, 'PUT', $eventData);
        echo json_encode(['success' => $code == 200]);
        break;

    case 'delete':
        $eventId = $_GET['id'];
        list($code, $result) = apiRequest(API_BASE . '/' . $eventId, 'DELETE');
        echo json_encode(['success' => $code == 204]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>