<?php

require_once("../includes/php/functions.php");

SECURITY_HEADERS();

$FILE_F = new FILE_F();

$LOGIN_F = new LOGIN_F();
$LOGIN_F->loggedInCheck();

$LOG_F = new LOG_F();

$fileSecretKey = $LOGIN_F->decryptText($_COOKIE['fsc']);

if(isset($_FILES['file'])) {
    $uploadedFile = $_FILES['file'];
    $fileName = basename($uploadedFile['name']);
    $fileSize = $FILE_F->formatFileSize($uploadedFile['size']);

    $targetPath = $FILE_F->generateUploadDirName($FILE_F->getExtension($fileName));
    
    if($FILE_F->encryptFile($uploadedFile['tmp_name'], $targetPath, $fileSecretKey)) {
        if($FILE_F->addFileToDB($fileName, $fileSize, $targetPath)) {
            die();
        }
        else {
            unlink($targetPath);
            $LOG_F->logThis("UPLOAD - Nepovedlo se vytvorit DB zaznam pro soubor " . $targetPath, "error");
        }
    }
    else {
        unlink($targetPath);
        $LOG_F->logThis("UPLOAD - Nepovedlo se zasifrovat soubor " . $targetPath, "error");
    }
}

header("HTTP/1.1 500 Internal Server Error");