<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/includes/database.php";
require_once __DIR__ . "/includes/global.php";

$title = "Afspraak Maken";
$showConfirm = false;

$sql = "SELECT * FROM categories";
$categories = $db->getAssoc($sql);
$categories = sanitize_recursive($categories);

if (!empty($_POST)) {
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
        $formName = $formField['name'];
        $description = $formField['description'];
        if (empty($_POST[$formName])) {
            $errors[] = "\"{$description}\" mag niet leeg zijn";
        }
    };
    if (empty($errors)) {
        $fromTimeSQL = "{$db->qStr($_POST['date'])} {$db->qStr($_POST['from_time'])}";
        $untilTimeSQL = "{$db->qStr($_POST['date'])} {$db->qStr($_POST['until_time'])}";
        $nameSQL = $db->qStr($_POST['name']);
        $emailSQL = $db->qStr($_POST['email']);
        $phoneSQL = $db->qStr($_POST['phone']);
        $detailsSQL = $db->qStr($_POST['details']);
        $categorySQL = $db->qStr($_POST['category']);

        $sql = "INSERT INTO appointments (from_time, until_time, name, email, phone, details, category)
            VALUES({$fromTimeSQL}, {$untilTimeSQL}, {$nameSQL}, {$emailSQL}, {$phoneSQL}, {$detailsSQL}, {$categorySQL})";
        $ok = $db->execute($sql);
        if ($ok) {
            $showConfirm = true;
        }
        else {
            $errors[] = "Daar ging iets fout. Probeer het nog eens.";
        }
    };
};
?>

<?php include_once __DIR__ . "/includes/header.php";
if ($showConfirm) { ?>
    <h1>Afspraak ingepland!</h1>
    <table class="confirmTable">
        <tbody>
            <tr>
                <th>
                    Soort Afspraak:
                </th>
                <td>
                    <?= $categories[$_POST['category']]['name'] ?>
                </td>
            </tr>
            <tr>
                <th>
                    Datum:
                </th>
                <td>
                    <?= $_POST['date'] ?>
                </td>
            </tr>
            <tr>
                <th>
                    Tijd:
                </th>
                <td>
                    <?= "{$_POST['from_time']} - {$_POST['until_time']}" ?>
                </td>
            </tr>
            <tr>
                <th>
                    Naam:
                </th>
                <td>
                    <?= $_POST['name'] ?>
                </td>
            </tr>
            <tr>
                <th>
                    E-Mail adres:
                </th>
                <td>
                    <?= $_POST['email'] ?>
                </td>
            </tr>
            <tr>
                <th>
                    Telefoonnummer:
                </th>
                <td>
                    <?= $_POST['phone'] ?>
                </td>
            </tr>
            <tr>
                <th>
                    Korte beschrijving:
                </th>
                <td>
                    <?= $_POST['details'] ?>
                </td>
            </tr>
        </tbody>
    </table>
<?php }
else { ?>
    <form class="bookingform" method="post">
        <label for="categoryInput">Soort afspraak:</label>
        <br>
        <select name="category" id="categoryInput">
            <option value="" selected disabled hidden>Kiezen...</option>
            <?php foreach ($categories as $key => $category) { ?>
                <option value="<?= $key ?>"><?= $category["name"] ?></option>
            <?php } ?>
        </select>
        <br>
        <input type="text" name="date" id="datepicker" class="hasTimeslots" hidden/>
        <ul id="timeslots"></ul>
        <br>
        <div class="inputWrapper">
            <label for="name">Naam</label>
            <br>
            <input type="text" name="name">
        </div>
        <div class="inputWrapper">
            <label for="email">E-mail adres</label>
            <br>
            <input type="text" name="email">
        </div>
        <div class="inputWrapper">
            <label for="phone">Telefoonnummer</label>
            <br>
            <input type="text" name="phone">
        </div>
        <div class="inputWrapper">
            <label for="details">Korte beschrijving</label>
            <br>
            <textarea name="details"></textarea>
        </div>
        <input type="submit" value="submit">
    </form>

<?php }
include_once __DIR__ . "/includes/footer.php" ?>
