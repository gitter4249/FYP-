<?php
// google_calendar_config.php
session_start();

define('CLIENT_ID',     '660259102319-h9cpnihil177absi2u9dg2taemqv6kmb.apps.googleusercontent.com');
define('CLIENT_SECRET', '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');
define('REDIRECT_URI',  'http://localhost/fyp/fyp/admin/google_calendar_oauth.php');
define('CALENDAR_ID',   'primary');

define('AUTH_URL',      'https://accounts.google.com/o/oauth2/v2/auth');
define('TOKEN_URL',     'https://oauth2.googleapis.com/token');
define('API_BASE',      'https://www.googleapis.com/calendar/v3/calendars/' . urlencode(CALENDAR_ID) . '/events');
?>