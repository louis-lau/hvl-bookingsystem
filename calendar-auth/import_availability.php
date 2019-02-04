<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";

$fullSync = false;
$startDate = new DateTime();

$sql = "SELECT * FROM `availability_imports`";
$calendarTokens = $db->getRow($sql);

if (empty($calendarTokens['calendar_id'])) {
    echo "No calendar set up";
    exit;
};

$client = new Google_Client();
$client->setAuthConfig('client_secrets.json');
$client->addScope(Google_Service_Calendar::CALENDAR_READONLY);

if (!empty($calendarTokens['refresh_token'])) {
    // Get access token using refresh token
    $client->fetchAccessTokenWithRefreshToken($calendarTokens['refresh_token']);
}
else {
    echo "No refresh token set";
    exit;
}

$service = new Google_Service_Calendar($client);

// Try partial sync using sync token
if (isset($calendarTokens['sync_token'])) {
    $params = array(
        'syncToken' => $calendarTokens['sync_token'],
        'singleEvents' => true,
    );

    try {
        $events = $service->events->listEvents($calendarTokens['calendar_id'], $params);
    } catch (\Google_Service_Exception $e) {
        // Getting events with partial sync didn't work, we need to do a full sync
        $fullSync = true;
    }
}
else {
    $fullSync = true;
}

if ($fullSync) {
    $params = array(
        'timeMin' => $startDate->Format('c'),
        'singleEvents' => true,
    );


    try {
        // Get all events after today
        $events = $service->events->listEvents($calendarTokens['calendar_id'], $params);
    } catch (\Google_Service_Exception $e) {
        // Full sync didn't work
        echo "Something went wrong, try adding your calendar again!";
        exit;
    }

    // Clear all availability data, we're doing a full sync
    $sql = "DELETE FROM `availability`";
    $ok = $db->execute($sql);
}

// Loop through events and write to db
while (true) {
    foreach ($events->getItems() as $event) {
        $id = $event->getId();
        $idSQL = $db->qStr($id);

        // Event created or updated
        if ($event->getStatus() === 'confirmed') {
            $fromTimeSQL = $db->qStr($event->getStart()->getDateTime());
            $untilTimeSQL = $db->qStr($event->getEnd()->getDateTime());
            $descriptionSQL = $db->qStr($event->getSummary());

            $sql = "INSERT INTO `availability` (`id`, `from_time`, `until_time`, `description`)
              VALUES ({$idSQL},{$fromTimeSQL},{$untilTimeSQL},{$descriptionSQL})
              ON DUPLICATE KEY UPDATE from_time={$fromTimeSQL}, until_time={$untilTimeSQL}, description={$descriptionSQL}";
            $ok = $db->execute($sql);
        }
        // Event Cancelled
        elseif ($event->getStatus() === 'cancelled') {
            $sql = "DELETE FROM `availability` WHERE `id` = {$idSQL}";
            $ok = $db->execute($sql);
        }
    }
    $pageToken = $events->getNextPageToken();
    if ($pageToken) {
        // Continue to next page and repeat
        $params['pageToken'] = $pageToken;
        $events = $service->events->listEvents($calendarTokens['calendar_id'], $params);
    } else {
        $syncToken = $events->getNextSyncToken();
        if ($syncToken) {
            // Store sync token to do partial sync next time around
            $syncTokenSQL = $db->qStr($syncToken);
            $sql = "UPDATE `availability_imports` SET `sync_token` = {$syncTokenSQL} WHERE `calendar_id` = '{$calendarTokens['calendar_id']}'";
            $ok = $db->execute($sql);
        }
        break;
    }
}

header("Location: {$baseURL}admin");
exit;