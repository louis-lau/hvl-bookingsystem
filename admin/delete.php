<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";
require_once __DIR__ . "/../includes/need_login.php";

$title = "Afspraak bewerken";
$showForm = true;

$idSQL = $db->qStr($_GET['id']);

if (!empty($_GET["id"])) {
    $sql = "SELECT * FROM appointments WHERE `id` = {$idSQL}";
    $appointment = $db->getRow($sql);
    if (!empty($appointment)) {
        $sql = "DELETE FROM `appointments` WHERE `id` = {$idSQL}";
        $ok = $db->execute($sql);
        header("Location: {$baseURL}admin/");
        exit();
    };
};

echo "Deze afspraak bestaat niet (meer), en kan dus niet verwijderd worden. <br> <a href=\"{$baseURL}admin/\">terug</a>";
