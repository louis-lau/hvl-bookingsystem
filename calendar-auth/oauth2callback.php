<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->setRedirectUri('http://localhost/handen_van_licht/calendar-auth/oauth2callback.php');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setAccessType("offline");
$client->setApprovalPrompt('force');

$sql = "SELECT * FROM `availability_imports`";
$calendarTokens = $db->getRow($sql);

if (!empty($calendarTokens)) {
    $redirect_uri = 'http://localhost/handen_van_licht/calendar-auth/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}

if (! isset($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
    $client->authenticate($_GET['code']);
    $accessToken = $client->getAccessToken();
    $refreshToken = $client->getRefreshToken();

    $sql = "INSERT INTO `availability_imports`
      (access_token, refresh_token)
      VALUES({$db->qStr($accessToken['access_token'])}, {$db->qStr($refreshToken)})";
    $ok = $db->execute($sql);
    $redirect_uri = 'http://localhost/handen_van_licht/calendar-auth/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}