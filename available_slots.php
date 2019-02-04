<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/includes/database.php";
require_once __DIR__ . "/includes/global.php";

function noTimeslots() {
    echo "No timeslots available";
    exit;
}

if (isset($_GET['category'])) {
    $categorySQL = $db->qStr($_GET['category']);
    $sql = "SELECT * FROM categories WHERE `id` = ${categorySQL}";
    $result = $db->getRow($sql);
    if (!$result) {
        noTimeslots();
    }
    $session_length = $result['session_length'];
}
else {
    noTimeslots();
}

$startDate = new DateTime();
$endDate = clone $startDate;

// Add one day to stat date and one month to end date
$startDate->add(DateInterval::createFromDateString("1 Day"));
$endDate->add(DateInterval::createFromDateString("1 Month"));

$interval = DateInterval::createFromDateString("1 day");
$dates = new DatePeriod($startDate, $interval, $endDate);

// Loop through dates and check if availability has been set for that date
foreach ($dates as $date) {
    $sql = "SELECT * FROM `availability` WHERE `from_time` LIKE '{$date->format("Y-m-d")}%' ORDER BY `from_time` ASC";
    $availableTimeslots = $db->getAll($sql);

    // Loop through the set availabilty timeslots
    foreach ($availableTimeslots as $availableTimeslot) {
        $availableTimeslotFrom = new DateTime($availableTimeslot["from_time"]);
        $availableTimeslotUntil = new DateTime($availableTimeslot["until_time"]);

        // Round down minutes in availability so it starts at the whole hour
        $availableTimeslotFrom->setTime($availableTimeslotFrom->format('H'), 0);
        $availableTimeslotUntil->setTime($availableTimeslotUntil->format('H'), 0);

        // sessions of 90 minutes should start at half past the hour, availability always starts on whole hours. So add 30 minutes
        if ($session_length == 90) {
            $availableTimeslotFrom->add(DateInterval::createFromDateString("30 minutes"));
        }

        $interval = DateInterval::createFromDateString("60 minutes");
        $hours = new DatePeriod($availableTimeslotFrom, $interval, $availableTimeslotUntil);

        // Loop through every hour between start time and end time of this availability timeslot, check for existing appointments
        foreach ($hours as $TimeslotStart) {
            $TimeslotEnd = clone $TimeslotStart;
            $TimeslotEnd->add(DateInterval::createFromDateString("${session_length} minutes")); // add session time to timeslotstart to get end time
            $TimeslotStartSQL = $db->qStr($TimeslotStart->format("Y-m-d H:i:s"));
            $TimeslotEndSQL = $db->qStr($TimeslotEnd->format("Y-m-d H:i:s"));

            $sql = "SELECT * FROM `appointments`
                WHERE `from_time` >= {$TimeslotStartSQL} AND `from_time`  <  {$TimeslotEndSQL}
                OR    `until_time` > {$TimeslotStartSQL} AND `until_time` <= {$TimeslotEndSQL}
                OR    `from_time` <= {$TimeslotStartSQL} AND `until_time` >= {$TimeslotEndSQL}";
            $result = $db->getRow($sql);

            // Return timeslot to array if valid and not occupied by another appointment
            if (empty($result) && $TimeslotEnd <= $availableTimeslotUntil) {
                $validTimeslots[] = [
                    "date" => [
                        "from" => $TimeslotStart->format("Y-m-d"),
                        "until" => $TimeslotEnd->format("Y-m-d"),
                    ],
                    "time" => [
                        "from" => $TimeslotStart->format("H:i"),
                        "until" => $TimeslotEnd->format("H:i"),
                    ]
                ];
            }
        }
    }
}


if (isset($validTimeslots)) {
    header('Content-Type: application/json');
    echo json_encode($validTimeslots);
}
else {
    noTimeslots();
}
