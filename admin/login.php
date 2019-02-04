<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../includes/database.php";
require_once __DIR__ . "/../includes/global.php";

$incorrectLogin = false;

if (!empty($_POST)) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $usernameSQL = $db->qStr($_POST['username']);
        $password = $_POST['password'];
        $sql = "SELECT * FROM `users` WHERE `username` = {$usernameSQL}";
        $result = $db->getRow($sql);
        if ($result && password_verify($password, $result['password'])) {
            session_start();
            $_SESSION['loggedin'] = true;
            header("Location: {$baseURL}admin");
            exit;
        }
        else {
            $incorrectLogin = true;
        }
    }
else {
        $incorrectLogin = true;
    }
}

if ($incorrectLogin) {
    $errors[] = "Deze gebruikersnaam en/of wachtwoord klopt niet.";
}

?>
<?php include_once __DIR__ . "/../includes/header.php"; ?>
<form method="post">
    <label for="username">Gebruikersnaam:</label><br>
    <input type="text" name="username" id="username"/><br>
    <label for="password">Wachtwoord:</label><br>
    <input type="password" name="password" id="username"/><br>
    <input type="submit" value="inloggen">
</form>
<?php include_once __DIR__ . "/../includes/footer.php"; ?>