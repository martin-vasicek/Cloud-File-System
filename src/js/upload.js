import * as ErrorMsg from './errorMsg.js';
import * as FilePage from './files.js';

export function generateModalContent() {
    const container = document.createElement('div');
    container.classList.add('container');

    const row = document.createElement('div');
    row.classList.add('row', 'justify-content-center');
    container.appendChild(row);

    const dropZone = document.createElement('div');
    dropZone.classList.add('drop-zone', 'text-center');
    dropZone.id = 'dropZone';
    row.appendChild(dropZone);

    const icon = document.createElement('span');
    icon.classList.add('drop-zone__icon');
    icon.textContent = '📁';
    dropZone.appendChild(icon);

    const text = document.createElement('span');
    text.classList.add('drop-zone__text');
    text.textContent = 'Přetáhněte sem soubor nebo klikněte pro výběr.';
    dropZone.appendChild(text);

    const fileInput = document.createElement('input');
    fileInput.classList.add('drop-zone__input');
    fileInput.type = 'file';
    fileInput.id = 'fileInput';
    dropZone.appendChild(fileInput);

    const modalBody = document.querySelector('.modal-body');
    modalBody.innerHTML = '';
    modalBody.appendChild(container);

    dropZone.addEventListener('click', function() {
        if(!uploading) {
            fileInput.click();
        }
    });

    dropZone.addEventListener('dragover', function(event) {
        event.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', function() {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', function(event) {
        event.preventDefault();
        if(!uploading) {
            dropZone.classList.remove('drag-over');
            let files = event.dataTransfer.files;
            handleFiles(files);
        }
    });

    fileInput.addEventListener('change', function() {
        let files = this.files;
        handleFiles(files);
    });

    const uploadButton = document.getElementById('button-confirm-upload');
    uploadButton.addEventListener('click', function() {
        fileSubmit();
    });
}

function fileSubmit() {
    if (file != null) {
        uploading = true;
        const data = new FormData();
        data.append('file', file);

        const xhr = new XMLHttpRequest();

        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');

        progressContainer.style.display = 'block';

        xhr.upload.addEventListener("progress", function(event) {
            if (event.lengthComputable) {
                let percentCompleted = Math.round((event.loaded / event.total) * 100);
                progressBar.style.width = percentCompleted + "%";
                if (percentCompleted < 100) {
                    progressBar.innerText = percentCompleted + "%";
                } else {
                    progressBar.innerText = "Šifruji...";
                }
            }
        });        

        xhr.open("POST", "action/upload.php");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                uploading = false;
                window.removeEventListener('beforeunload', preventWindowClose);
                if (xhr.status == 200) {
                    location.reload();
                }
                else {
                    uploading = false;
                    enableUploadControls();
                    FilePage.closeUploadPopup();
                    ErrorMsg.showErrorMsg("Nahrání souboru se nezdařilo.", "danger");
                }
            }
        };

        xhr.send(data);

        disableUploadControls();
    }
}

function disableUploadControls() {
    window.addEventListener('beforeunload', preventWindowClose);
    document.getElementById('button-confirm-upload').disabled = true;
    document.getElementById('close-button').disabled = true;
    document.getElementById('dropZone').style.cursor = 'not-allowed';
}

function enableUploadControls() {
    window.removeEventListener('beforeunload', preventWindowClose);
    document.getElementById('button-confirm-upload').disabled = false;
    document.getElementById('close-button').disabled = false;
    document.getElementById('dropZone').style.cursor = 'pointer';
}

function preventWindowClose(e) {
    e.preventDefault();
}

function handleFiles(files) {
    if (files.length > 0) {
        file = files[0];
        document.getElementById('button-confirm-upload').disabled = false;

        let dropZoneContent = document.createElement('div');
        dropZoneContent.classList.add('drop-zone__content');

        let iconContainer = document.createElement('div');
        iconContainer.classList.add('mr-2', 'mb-2');
        let icon = document.createElement('i');
        icon.classList.add('fas', 'fa-3x', getFileIcon(file.name));
        iconContainer.appendChild(icon);
        dropZoneContent.appendChild(iconContainer);

        let fileName = document.createElement('div');
        fileName.textContent = truncateFileName(file.name, 30);
        fileName.style.fontSize = '1.2em';
        fileName.style.lineHeight = '1.5';
        dropZoneContent.appendChild(fileName);

        let fileSize = document.createElement('div');
        fileSize.textContent = '(' + formatFileSize(file.size) + ')';
        fileSize.style.fontSize = '1em';
        fileSize.style.color = '#6c757d';
        dropZoneContent.appendChild(fileSize);

        let progressContainer = document.createElement('div');
        progressContainer.classList.add('progress-container');
        progressContainer.id = 'progress-container';
        
        let progressBar = document.createElement('div');
        progressBar.classList.add('progress', 'mt-2');

        let innerProgressBar = document.createElement('div');
        innerProgressBar.classList.add('progress-bar', 'bg-primary');
        innerProgressBar.id = 'progress-bar';
        innerProgressBar.setAttribute('role', 'progressbar');
        innerProgressBar.setAttribute('aria-valuenow', '0');
        innerProgressBar.setAttribute('aria-valuemin', '0');
        innerProgressBar.setAttribute('aria-valuemax', '100');
        innerProgressBar.style.width = '0%';
        innerProgressBar.textContent = '0%';

        progressBar.appendChild(innerProgressBar);
        progressContainer.appendChild(progressBar);

        let dropZone = document.getElementById('dropZone');
        dropZone.innerHTML = '';
        dropZone.appendChild(dropZoneContent);
        dropZone.appendChild(progressContainer);
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileIcon(fileName) {
    let extension = fileName.split('.').pop().toLowerCase();
    switch (extension) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'fa-file-image';
        case 'doc':
        case 'docx':
            return 'fa-file-word';
        case 'xls':
        case 'xlsx':
            return 'fa-file-excel';
        case 'ppt':
        case 'pptx':
            return 'fa-file-powerpoint';
        case 'pdf':
            return 'fa-file-pdf';
        case 'txt':
            return 'fa-file-alt';
        case 'mp3':
            return 'fa-file-audio';
        case 'mp4':
        case 'avi':
        case 'mov':
            return 'fa-file-video';
        case 'zip':
        case 'rar':
            return 'fa-file-archive';
        case 'exe':
            return 'fa-file-code';
        case 'html':
        case 'css':
        case 'js':
            return 'fa-file-code';
        case 'json':
            return 'fa-file-code';
        case 'svg':
            return 'fa-file-image';
        case 'pptm':
        case 'potx':
            return 'fa-file-powerpoint';
        case 'csv':
            return 'fa-file-csv';
        case 'xml':
            return 'fa-file-code';
        case 'java':
            return 'fa-file-code';
        case 'cpp':
            return 'fa-file-code';
        case 'py':
            return 'fa-file-code';
        case 'php':
            return 'fa-file-code';
        case 'sh':
            return 'fa-file-code';
        case 'bat':
            return 'fa-file-code';
        case 'sql':
            return 'fa-file-code';
        case 'ini':
            return 'fa-file-code';
        case 'log':
            return 'fa-file-alt';
        case 'conf':
            return 'fa-file-alt';
        case 'yaml':
        case 'yml':
            return 'fa-file-alt';
        case 'dat':
            return 'fa-file-alt';
        case 'dll':
            return 'fa-file-code';
        case 'iso':
            return 'fa-file-archive';
        case 'deb':
        case 'rpm':
            return 'fa-file-archive';
        case 'tgz':
        case 'gz':
            return 'fa-file-archive';
        case 'bak':
        case 'backup':
            return 'fa-file-archive';
        case '7z':
        case 'tar':
            return 'fa-file-archive';
        case 'java':
            return 'fa-file-code';
        case 'rpm':
        case 'deb':
            return 'fa-file-code';
        case 'dmg':
            return 'fa-file-code';
        case 'cmd':
            return 'fa-file-code';
        case 'bak':
        case 'backup':
            return 'fa-file-code';
        case 'patch':
            return 'fa-file-code';
        case 'ps1':
            return 'fa-file-code';
        case 'wasm':
            return 'fa-file-code';
        case 'key':
        case 'pem':
            return 'fa-key';
        case 'cer':
        case 'crt':
            return 'fa-certificate';
        default:
            return 'fa-file';
    }
}    

function truncateFileName(fileName, maxLength) {
    if (fileName.length > maxLength) {
        return fileName.substring(0, maxLength - 3) + '...';
    } 
    else {
        return fileName;
    }
}

var file;
export var uploading = false;

document.addEventListener('DOMContentLoaded', function() {
    generateModalContent();
});

window.addEventListener('beforeunload', function (e) {
    if (uploading) {
        e.preventDefault();
    }
});