var isAnimating = false;
var fixedAlert = document.getElementById('fixed-alert');
var toastNotif = new bootstrap.Toast(fixedAlert);

export async function showErrorMsg(message, type) {
    if (isAnimating) {
        return;
    }
    
    if(type = "danger") {
        fixedAlert.classList.add('alert-danger');
    }
    else if (type == "success") {
        fixedAlert.classList.add('alert-success');
    }

    isAnimating = true;

    fixedAlert.querySelector('.error-message').textContent = message;

    fixedAlert.style.opacity = '0'; 
    fixedAlert.style.display = 'block';
    toastNotif.show();

    setTimeout(function() {
        fixedAlert.style.transition = 'opacity 0.4s ease';
        fixedAlert.style.opacity = '1';
    }, 100);

    setTimeout(function() {
        fixedAlert.style.transition = 'opacity 1s ease';
        fixedAlert.style.opacity = '0';
    }, 3000);

    setTimeout(function() {
        fixedAlert.style.display = 'none';
        fixedAlert.style.transition = ''; 
        isAnimating = false;
    }, 4000);
}