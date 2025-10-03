<?php
$helpers = \Core\Helpers::class;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $helpers::sanitize($pageTitle ?? 'Etat des courriers') ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="assets/css/print.css">
</head>
<body class="print-layout">
    <?= $content ?>
</body>
</html>
