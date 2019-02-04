<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";
require_once __DIR__ . "/../includes/need_login.php";

$title = "Afspraak bewerken";
$showForm = true;

if (!empty($_GET["id"])) {
    $idSQL = $db->qStr($_GET['id']);
    $sql = "SELECT * FROM appointments WHERE `id` = {$idSQL}";
    $appointment = $db->getRow($sql);
    $appointment = sanitize_recursive($appointment);

    if (empty($appointment)) {
        $errors[] = "Deze afspraak bestaat niet, en kan dus niet bewerkt worden.";
        $showForm = false;
    }
    elseif (!empty($_POST)) {
        $formFields = [
            [
                "name" => "category",
                "description" => "Soort afspraak"
            ],
            [
                "name" => "date",
                "description" => "Datum"
            ],
            [
                "name" => "from_time",
                "description" => "Starttijd"
            ],
            [
                "name" => "until_time",
                "description" => "Eindtijd"
            ],
            [
                "name" => "name",
                "description" => "Naam"
            ],
            [
                "name" => "email",
                "description" => "E-Mail"
            ],
            [
                "name" => "phone",
                "description" => "Telefoonnummer"
            ],
            [
                "name" => "details",
                "description" => "Korte beschrijving"
            ],
        ];
        foreach ($formFields as $formField) {
            $name = $formField['name'];
            $description = $formField['description'];
            if (empty($_POST[$name])) {
                $errors[] = "Het veld {$description} mag niet leeg zijn";
            }
        };
        if (empty($errors)) {
            $idSQL = $db->qStr($_GET['id']);
            $fromTimeSQL = "{$db->qStr($_POST['date'])} {$db->qStr($_POST['from_time'])}";
            $untilTimeSQL = "{$db->qStr($_POST['date'])} {$db->qStr($_POST['until_time'])}";
            $nameSQL = $db->qStr($_POST['name']);
            $emailSQL = $db->qStr($_POST['email']);
            $phoneSQL = $db->qStr($_POST['phone']);
            $detailsSQL = $db->qStr($_POST['details']);
            $categorySQL = $db->qStr($_POST['category']);

            $sql = "UPDATE appointments
                SET from_time = '{$fromTimeSQL}',
                until_time = '{$untilTimeSQL}',
                name = '{$nameSQL}',
                email = '{$emailSQL}',
                phone = '{$phoneSQL}',
                details = '{$detailsSQL}',
                category = '{$categorySQL}'
                WHERE `id` = {$appointment['id']}";
            $ok = $db->execute($sql);
            $sql = "SELECT * FROM appointments WHERE `id` = {$idSQL}";
            $appointment = $db->getRow($sql);
        };
    };
}
else {
    $errors[] = "Deze afspraak bestaat niet, en kan dus niet bewerkt worden.";
    $showForm = false;
}
?>

<?php include_once "../includes/header.php";
if ($showForm) {
    $sql = "SELECT * FROM categories";
    $categories = $db->getAssoc($sql);
    $categories = sanitize_recursive($categories);
    $fromTime = new DateTime($appointment['from_time']);
    $untilTime = new DateTime($appointment['until_time']); ?>
    <form class="bookingform" method="post">
        <label for="categoryInput">Soort afspraak:</label>
        <select name="category" id="categoryInput">
            <?php foreach ($categories as $key => $category) { ?>
                <option <?php if ($key == $appointment['category']) {echo "selected";} ?> value="<?= $key ?>"><?= $category['name'] ?></option>
            <?php } ?>
        </select>
        <br>
        <input type="text" name="date" id="datepicker" value="<?= $fromTime->format('Y-m-d') ?>"/>
        <label for="from_time">vanaf</label>
        <input type="text" name="from_time" id="from_time" value="<?= $fromTime->format('H:i') ?>"/>
        <label for="until_time">tot</label>
        <input type="text" name="until_time" id="until_time" value="<?= $untilTime->format('H:i') ?>"/>
        <br>
        <label for="name">Naam</label>
        <input type="text" name="name" id="name" value="<?= $appointment['name'] ?>">
        <br>
        <label for="email">E-mail adres</label>
        <input type="text" name="email" id="email" value="<?= $appointment['email'] ?>">
        <br>
        <label for="phone">Telefoonnummer</label>
        <input type="text" name="phone" id="phone" value="<?= $appointment['phone'] ?>">
        <br>
        <label for="details">Korte beschrijving</label>
        <br>
        <textarea name="details" id="details"><?= $appointment['details'] ?></textarea>
        <br>
        <input type="submit" value="submit">
    </form>
<?php };
include_once "../includes/footer.php" ?>
