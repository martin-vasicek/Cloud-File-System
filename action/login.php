<?php
header('Content-Type: application/json');

require_once('../includes/php/functions.php');

SECURITY_HEADERS();

$LOGIN_F = new LOGIN_F();

$username = $_POST['username'];
$pwd = $_POST['pwd'];

if($LOGIN_F->verifyLogin($username, $pwd) == true) {
    $fileSecretKey = $LOGIN_F->generateSecretKey($username, $pwd);
    setcookie("fsc", $LOGIN_F->encryptText($fileSecretKey), 0, "/", "", true, true);

    $_SESSION['loggedin'] = true;
    echo "true";
}
else if($LOGIN_F->verifyLogin($username, $pwd) == false) {
    $_SESSION['loggedin'] = false;
    echo "false";
}
else {
    echo "error";
}