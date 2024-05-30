<?php

require_once("includes/php/functions.php");
require_once("includes/php/functions_html.php");

SECURITY_HEADERS();

$HTML = new HTML();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $HTML->includes();
    ?>
    <title>Souborový systém</title>
</head>
<body>
    <?php
    if($_SESSION['loggedin'] != true) {
        $HTML->loginForm();
    }
    else if ($_SESSION['loggedin'] == true) {
        reloadFileSecretyKey();
        $HTML->fileTable();
    }
    ?>
</body>
</html>