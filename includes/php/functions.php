<?php

// CONFIGURATION FILE
$config = require_once(__DIR__ . '/config.php');
//

// COOKIE SECRET KEY
$cookieSecretKey = $config['cookiekey'];
//

function dbPDO() {
    global $config;

    $host = $config['dbhost'];
    $dbname = $config['dbname'];
    $username = $config['dbusername'];
    $password = $config['dbpassword'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Chyba při připojování k databázi: " . $e->getMessage());
    }
}

function SECURITY_HEADERS() {
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self'; object-src 'none'; img-src 'self' data: https://www.w3.org/2000/svg;");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

function reloadFileSecretyKey() {
    $LOGIN_F = new LOGIN_F();
    $key = $LOGIN_F->decryptText($_COOKIE['fsc']);

    setcookie("fsc", "", time() - 3600); 

    $newKey = $LOGIN_F->encryptText($key);

    setcookie("fsc", $newKey, 0, "/", "", true, true);
}

class LOGIN_F {
    function __construct() {
        global $cookieSecretKey;
    }

    function verifyLogin($username, $pwd) {
        $pdo = dbPDO();
        
        $stmt = $pdo->prepare("SELECT username, pwd FROM users");
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($username, $row['username']) && password_verify($pwd, $row['pwd'])) {
                return true;
            }
        }
        
        return false;
    }       

    function loggedInCheck() {
        if($_SESSION['loggedin'] != true) {
            header("Location: /index.php");
            die();
        }
    }

    function generateSecretKey($username, $pwd) {
        for ($i = 0; $i < 50000; $i++) {
            $usernameHash = hash('whirlpool', $username);
            $pwdHash = hash('whirlpool', $pwd);
        }

        $secretKey = "";

        for ($i = 0; $i < 50000; $i++) {
            $secretKey = hash('whirlpool', $pwdHash . $usernameHash);
        }
    
        return $secretKey;
    }

    function encryptText($text) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $ciphertext = openssl_encrypt($text, 'aes-256-cbc', $this->cookieSecretKey, 0, $iv);
        $ciphertext_base64 = base64_encode($iv . $ciphertext);

        return $ciphertext_base64;
    }

    function decryptText($text) {
        $ciphertext = base64_decode($text);

        $ivSize = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($ciphertext, 0, $ivSize);
        $ciphertext = substr($ciphertext, $ivSize);

        $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $this->cookieSecretKey, 0, $iv);

        return $plaintext;
    }
}

define('FILE_ENCRYPTION_BLOCKS', 10000);

class FILE_F {

    private $LOG_F;

    function __construct() {
        $this->LOG_F = new LOG_F();
    }

    function getExtension($filename) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return $extension;
    }

    function generateUploadDirName($extension) {
        $uploadDir = null;
        while($uploadDir == null || file_exists($uploadDir)) {
            $shuffle = str_shuffle("abcdefghijklmnopqrstuvxwyz0123456789");
            $uploadDir = "../upload/" . md5($shuffle) . "." . $extension;
        }
        return $uploadDir;
    }

    function encryptFile($inputFile, $outputFile, $key) {
        $key = openssl_digest($key, 'sha256', true);
        $iv = openssl_random_pseudo_bytes(16);
    
        $error = false;
        if ($outputStream = fopen($outputFile, 'wb')) {
            fwrite($outputStream, $iv);
            if ($inputStream = fopen($inputFile, 'rb')) {
                while (!feof($inputStream)) {
                    $plaintext = fread($inputStream, 16 * FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($outputStream, $ciphertext);
                }
                fclose($inputStream);
            } else {
                $error = true;
            }
            fclose($outputStream);
        } else {
            $error = true;
        }
    
        return $error ? false : true;
    }
    
    function decryptFile($inputFile, $outputFile, $key) {
        $key = openssl_digest($key, 'sha256', true);
    
        $error = false;
        if ($outputStream = fopen($outputFile, 'wb')) {
            if ($inputStream = fopen($inputFile, 'rb')) {
                $iv = fread($inputStream, 16);
                while (!feof($inputStream)) {
                    $ciphertext = fread($inputStream, 16 * (FILE_ENCRYPTION_BLOCKS + 1));
                    $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    if ($plaintext === false) {
                        $error = true;
                        break;
                    }
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($outputStream, $plaintext);
                }
                fclose($inputStream);
            } else {
                $error = true;
            }
            fclose($outputStream);
        } else {
            $error = true;
        }
    
        return $error ? false : true;
    }

    function addFileToDB($filename, $filesize, $path) {
        $pdo = dbPDO();
    
        $currentDate = date("H:i | j.n.Y");
        $unixDate = time();
    
        $stmt = $pdo->prepare("INSERT INTO files (name, size, date, unixdate, localPath) VALUES (:filename, :filesize, :uploadDate, :unixdate, :path)");
        $success = $stmt->execute(['filename' => $filename, 'filesize' => $filesize, 'uploadDate' => $currentDate, 'unixdate' => $unixDate ,'path' => $path]);
    
        return $success;
    }

    function removeFileFromDB($path) {
        $pdo = dbPDO();
    
        $stmt = $pdo->prepare("DELETE FROM files WHERE localPath=:path");
        $success = $stmt->execute(['path' => $path]);
    
        return $success;
    }

    function formatFileSize($bytes) {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return sprintf('%.2f', $bytes / pow($k, $i)) . ' ' . $sizes[$i];
    }

    function getFilesFromDB() {
        $pdo = dbPDO();
    
        $stmt = $pdo->prepare("SELECT * FROM files ORDER BY unixdate DESC");
        $stmt->execute();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $files;
    }

    function isFileInDB($filename) {
        $pdo = dbPDO();
    
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM files WHERE localPath=:filename");
        $stmt->execute(['filename' => "../upload/" . $filename]);
    
        $count = $stmt->fetchColumn();
    
        return $count > 0;
    }

    function getOriginalName($filename) {
        $pdo = dbPDO();
    
        $stmt = $pdo->prepare("SELECT name FROM files WHERE localPath=:filename");
        $stmt->execute(['filename' => "../upload/" . $filename]);
    
        $originalName = $stmt->fetchColumn();
    
        return $originalName;
    }
}

class LOG_F {
    function logThis($msg, $type) {
        $logline .= date("H:i|j.n.Y");

        if($type == "info") {
            $logline .= " INFO: ";
        }
        else if ($type == "error") {
            $logline .= " ERROR: ";
        }
        else if ($type == "warning") {
            $logline .= " WARNING: ";
        }

        $logline .= $msg;

        $logFilePath = "../log/main.log";

        if (file_exists($logFilePath)) {
            file_put_contents($logFilePath, $logline . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents($logFilePath, $logline . PHP_EOL);
        }
    }
}