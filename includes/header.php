<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?= $baseURL ?>dist/css/datepicker.min.css" />
    <link rel="stylesheet" href="<?= $baseURL ?>css/style.css" >
    <title><?= $title ?? 'Handen van Licht' ?></title>
</head>
<body>
<main>
    <?php if (!empty($errors)) {?>
        <ul>
        <?php foreach ($errors as $error) { ?>
            <li><?= $error ?></li>
        <?php } ?>
        </ul>
    <?php } ?>
