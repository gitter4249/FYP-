<?php
// google_calendar_oauth.php
require_once 'google_calendar_config.php';

if (isset($_GET['code'])) {
    $params = [
        'code'          => $_GET['code'],
        'client_id'     => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'redirect_uri'  => REDIRECT_URI,
        'grant_type'    => 'authorization_code'
    ];
    $ch = curl_init(TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);
    $token = json_decode($response, true);
    $_SESSION['google_calendar_token'] = $token;
    header('Location: admin_dashboard.php?view=appointments');
    exit;
} else {
    $scope = urlencode('https://www.googleapis.com/auth/calendar.events');
    $authUrl = AUTH_URL . '?' . http_build_query([
        'client_id'     => CLIENT_ID,
        'redirect_uri'  => REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'https://www.googleapis.com/auth/calendar.events',
        'access_type'   => 'offline',
        'prompt'        => 'consent'
    ]);
    header('Location: ' . $authUrl);
    exit;
}
?>