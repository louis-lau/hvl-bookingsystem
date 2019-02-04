<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";
require_once __DIR__ . "/../includes/need_login.php";

$title = "Overzicht afspraken";

$sql = "SELECT * FROM appointments ORDER BY `from_time` ASC";
$appointments = $db->getAll($sql);
$appointments = sanitize_recursive($appointments);

$sql = "SELECT * FROM categories";
$categories = $db->getAssoc($sql);
$categories = sanitize_recursive($categories);

// echo "<pre>";
// print_r($appointments);
// print_r($categories);
// echo "</pre>";
?>

<?php include_once "../includes/header.php" ?>
    <h1><?= $title ?></h1>
    <table>
        <thead>
            <tr>
                <th>Datum</th>
                <th>Vanaf</th>
                <th>Tot</th>
                <th>Soort</th>
                <th>Naam</th>
                <th>E-Mail</th>
                <th>Telefoon</th>
                <th>Details</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment) {
                $fromTime = new DateTime($appointment['from_time']);
                $untilTime = new DateTime($appointment['until_time']);?>
                <tr>
                    <td><?= $fromTime->format('Y-m-d') ?></td>
                    <td><?= $fromTime->format('H:i') ?></td>
                    <td><?= $untilTime->format('H:i') ?></td>
                    <td><?= $categories[$appointment['category']]["name"]?></td>
                    <td><?= $appointment['name'] ?></td>
                    <td><?= $appointment['email'] ?></td>
                    <td><?= $appointment['phone'] ?></td>
                    <td><?= $appointment['details'] ?></td>
                    <td><a href="<?= $baseURL ?>admin/edit.php?id=<?= $appointment['id']?>">Bewerken</a> <a href="<?= $baseURL ?>admin/delete.php?id=<?= $appointment['id']?>">Verwijderen</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="<?= $baseURL ?>calendar-auth/import_availability.php">Beschikbaarheid synchroniseren</a>
    <br>
    <a href="<?= $baseURL ?>admin/logout.php">Uitloggen</a>
<?php include_once "../includes/footer.php" ?>
