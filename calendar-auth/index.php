<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";

$title = "Beschikbaarheid importeren";
$noCalendar = false;

$sql = "SELECT * FROM `availability_imports`";
$calendarTokens = $db->getRow($sql);

// If tokens don't exist in database redirect to OAUTH and exit
if (empty($calendarTokens)) {
    $redirect_uri = 'http://localhost/handen_van_licht/calendar-auth/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}

$client = new Google_Client();
$client->setAuthConfig('client_secrets.json');
$client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

// Get access token using refresh token
$client->fetchAccessTokenWithRefreshToken($calendarTokens['refresh_token']);


$service = new Google_Service_Calendar($client);

if (empty($calendarTokens['calendar_id'])) {
    $noCalendar = true;
}
else {
    try {
        $calendar = $service->calendars->get($calendarTokens['calendar_id']);
    } catch (\Google_Service_Exception $e) {
        $noCalendar = true;
    }
}


// Print the next 10 events on the user's calendar.
//$calendarId = 'odldbu9k7sh2266i4131msc39c@group.calendar.google.com';
//$optParams = array(
//    'maxResults' => 10,
//    'orderBy' => 'startTime',
//    'singleEvents' => true,
//    'timeMin' => date('c'),
//);
//$results = $service->events->listEvents($calendarId, $optParams);
//$events = $results->getItems();
//$test = $service->calendarList->listCalendarList();


//    echo "<pre>";
//    print_r($events);
//    echo "</pre>";

include_once "../includes/header.php";
if ($noCalendar) {
    $calendarList = $service->calendarList->listCalendarList();?>
    <h1>Account gelinkt!</h1>
    <p>Kies nu een agenda om je beschikbaarheid uit te importeren:</p>
    <ul>
        <?php foreach ($calendarList->getItems() as $calendarListEntry) {?>
            <li><?= $calendarListEntry->getSummary() ?> (<?= $calendarListEntry->getId() ?>)</li>
        <?php } ?>
    </ul>
<?php }

include_once "../includes/footer.php";?>