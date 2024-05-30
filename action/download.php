<?php

require_once("../includes/php/functions.php");

SECURITY_HEADERS();

$FILE_F = new FILE_F();

$LOGIN_F = new LOGIN_F();
$LOGIN_F->loggedInCheck();

$LOG_F = new LOG_F();

$fileSecretKey = $LOGIN_F->decryptText($_COOKIE['fsc']);

$requiredFileName = $_GET['file'];
$path = "../upload/" . $requiredFileName;

if (file_exists($path)) {
    $tempDecryptedFile = tempnam(sys_get_temp_dir(), 'decrypted_file_');

    if($FILE_F->decryptFile($path, $tempDecryptedFile, $fileSecretKey)) {
        $filesize = filesize($tempDecryptedFile);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $FILE_F->getOriginalName($requiredFileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: private');
        header('Content-Length: ' . $filesize);
        readfile($tempDecryptedFile);
        unlink($tempDecryptedFile);
        die();
    }
    else {
        $LOG_F->logThis("DOWNLOAD - Nepovedlo se desifrovat soubor " . $path, "error");
    }
} else {
    $LOG_F->logThis("DOWNLOAD - Soubor " . $path . " na lokalnim ulozisti neexistuje.", "error");
}

header("HTTP/1.1 500 Internal Server Error");