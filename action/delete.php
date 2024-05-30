<?php

require_once("../includes/php/functions.php");

SECURITY_HEADERS();

$FILE_F = new FILE_F();

$LOGIN_F = new LOGIN_F();
$LOGIN_F->loggedInCheck();

$LOG_F = new LOG_F();

$requiredFileName = $_GET['file'];

if ($FILE_F->isFileInDB($requiredFileName)) {
    $path = "../upload/" . $requiredFileName;
    if (file_exists($path)) {
        if($FILE_F->removeFileFromDB($path)) {
            if(unlink($path)) {
                die();
            }
        }
        else {
            $LOG_F->logThis("DELETE - Nepovedlo se odstranit zaznam z databaze pro soubor " . $path, "error");
        }
    }
    else {
        $LOG_F->logThis("DELETE - Soubor " . $path . " na lokalnim ulozisti neexistuje", "error");
    }
}
$LOG_F->logThis("DELETE - Soubor " . $path . " neni v databazi", "error");

header("HTTP/1.1 500 Internal Server Error");