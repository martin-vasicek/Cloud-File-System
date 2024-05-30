<?php

require_once("functions.php");

class HTML {

    private $FILE_F;

    function __construct() {
        $this->FILE_F = new FILE_F();
    }

    function includes() {
        echo
        '
        <link rel="stylesheet" href="/lib/bootstrap/bootstrap.min.css">
        <link rel="stylesheet" href="/lib/font-awesome/css/all.min.css">

        <script src="/lib/bootstrap/bootstrap.bundle.min.js"></script>
        <script src="/lib/crypto-js/crypto-js.min.js"></script>
        <script src="/lib/jquery/jquery.min.js"></script>

        <link href="src/css/main.css" rel="stylesheet">
        <script type="module" src="/src/js/functions.js"></script>
        ';
    }

    function loginForm() {
        echo
        '
        <link rel="stylesheet" href="/src/css/loginForm.css">
        <script type="module" src="/src/js/login.js"></script>

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Přihlášení do souborového systému</div>
                        <div class="card-body">
                            <form id="login-form">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Uživatelské jméno">
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="pwd" name="pwd" placeholder="Heslo">
                                </div>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" id="login-button">Přihlásit se</button>
                                    <div id="login-result" class="text-center" hidden></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    function fileTable() {
        echo
        '
        <link rel="stylesheet" href="/src/css/fileTable.css">
        <script type="module" src="/src/js/files.js"></script>

        <!-- JS and SVG icon for error message -->
        <script type="module" src="/src/js/errorMsgModal.js"></script>
        <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
            <symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </symbol>
        </svg>

        <div class="container">
            <!-- Error message -->
            <div id="fixed-alert" class="m-3 alert fade fixed-top" role="alert">
                <svg class="bi d-inline-block align-text-top me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                <span class="error-message">Toto je upozornění. Zobrazí se po dobu 5 sekund.</span>
            </div>
            <!-- End -->
            <div class="mt-5 fileTable-top-menu">
                <h2 class="mt-3 mb-3">Uložené soubory</h2>
                <button class="btn btn-success" id="upload-button"><i class="fas fa-upload"></i> Nahrát soubor</button>
            </div>
            <table class="table table-striped table-dark">
                <thead>
                    <tr>
                        <th scope="col">Název</th>
                        <th scope="col">Velikost</th>
                        <th scope="col">Poslední změna</th>
                        <th scope="col">Stáhnout</th>
                        <th scope="col">Smazat</th>
                    </tr>
                </thead>
                <tbody>
                ';
                $files = $this->FILE_F->getFilesFromDB();
                foreach($files as $dbFile) {
                    echo
                    '
                    <tr>
                        <td>'.$dbFile['name'].'</td>
                        <td>'.$dbFile['size'].'</td>
                        <td>'.$dbFile['date'].'</td>
                        <td>
                            <button class="btn btn-primary btn-sm button-download" id="'.basename($dbFile['localPath']).'"><i class="fas fa-download"></i> Stáhnout</button>
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm button-delete" id="'.basename($dbFile['localPath']).'"><i class="fas fa-trash-alt"></i> Smazat</button>
                        </td>
                    </tr>
                    ';
                }
                echo
                '
                </tbody>
            </table>
            <!-- File Upload Popup -->
            '
            .
            $this->fileUploadModal()
            .
            '
            <!-- Alert Popup -->
            '
            .
            $this->alertModal()
            .
            '
        </div>
        ';
    }

    function fileUploadModal() {
        return
        '
        <script type="module" src="/src/js/upload.js"></script>
        <link rel="stylesheet" href="/src/css/uploadPopup.css">

        <div class="modal fade" id="upload-popup" tabindex="-1" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header">
                        <h5 class="modal-title">Nahrát soubor</h5>
                        <button type="button" class="btn-close" id="close-button" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Modal content -->
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary" id="button-confirm-upload" disabled>
                            <i class="fas fa-upload"></i> Nahrát
                        </button>
                    </div>
                </div>
            </div>
        </div>
        ';
    }

    function alertModal() {
        return
        '
        <div class="modal fade" id="alert-popup" tabindex="-1" aria-labelledby="alert-text" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header">
                        <h5 class="modal-title" id="alert-text">Odstranit soubor</h5>
                    </div>
                    <div class="modal-body">
                        Opravdu chcete tento soubor odstranit? Tato akce je nevratná!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="button-alert-yes"><i class="fas fa-exclamation-triangle"></i> Ano</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="button-alert-no">Ne</button>
                    </div>
                </div>
            </div>
        </div>
        ';
    }
}