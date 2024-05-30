import * as Upload from './upload.js';
import * as ErrorMsg from './errorMsg.js';
import * as JSFunctions from './functions.js';

var deletingFileName;

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('upload-button').addEventListener('click', openUploadPopup);
    document.getElementById('close-button').addEventListener('click', closeUploadPopup);

    var downloadButtons = document.querySelectorAll('.button-download');
    downloadButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            downloadFile(button.id);
        });
    });

    var deleteButtons = document.querySelectorAll('.button-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            deletingFileName = button.id;
            openAlertPopup();
        });
    });
});

export function openUploadPopup() {
    $('#upload-popup').modal('show');
}

export function closeUploadPopup() {
    if(!Upload.uploading) {
        $('#upload-popup').modal('hide');
        Upload.generateModalContent();
    }
}

export function openAlertPopup() {
    document.getElementById('button-alert-yes').addEventListener('click', deleteFile);
    $('#alert-popup').modal('show');
}

export function closeAlertPopup() {
    document.getElementById('button-alert-yes').addEventListener('click', deleteFile);
    $('#alert-popup').modal('hide');
}

function downloadFile(btnId) {
    let btnEl = document.getElementById(btnId);
    let originalHtml = btnEl.innerHTML;

    btnEl.innerText = "Dešifruji a stahuji...";
    btnEl.disabled = true;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'action/download.php?file=' + btnId, true);
    xhr.responseType = 'blob';
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            var fileName = xhr.getResponseHeader('Content-Disposition').split('filename=')[1].replace(/"/g, '');
            var blob = new Blob([xhr.response], { type: 'application/octet-stream' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);

            btnEl.innerHTML = originalHtml;
            btnEl.disabled = false;
        } else {
            ErrorMsg.showErrorMsg("Při stahování souboru se vyskytla chyba.", "danger");
            btnEl.innerHTML = originalHtml;
            btnEl.disabled = false;
        }
    };

    xhr.send();
}

async function deleteFile() {
    let result = await JSFunctions.getUrlXHR("action/delete.php?file=" + deletingFileName);
    console.log(result);
    if(result.status == 200) {
        location.reload();
    }
    else {
        closeAlertPopup();
        await ErrorMsg.showErrorMsg("Při odstraňování souboru došlo k chybě.", "danger");
    }
    deletingFileName = "";
}