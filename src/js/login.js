import * as JSFunctions from './functions.js';

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('login-button').addEventListener('click', loginSubmit);
});

document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();
});

export async function loginSubmit() {
    let loginBtnEl = document.getElementById('login-button');
    let originalHtml = loginBtnEl.innerHTML;

    loginBtnEl.disabled = true;
    loginBtnEl.innerText = "Přihlašuji...";

    let resultTextEl = document.getElementById("login-result");
    resultTextEl.setAttribute('class', 'text-center');
    resultTextEl.style.cssText = '';
    resultTextEl.style.opacity = '0';
    resultTextEl.hidden = false;

    let usernameEl = document.getElementById('username');
    let username = JSFunctions.hashString(usernameEl.value);

    let pwdEl = document.getElementById('pwd');
    let pwd = JSFunctions.hashString(pwdEl.value);

    let postData = {
        username: username,
        pwd: pwd
    };

    let result = await JSFunctions.postDataFetch("action/login.php", postData);

    if(result == "true") {
        resultTextEl.classList.add("text-success");
        JSFunctions.showText(resultTextEl.id, "Přihlášení proběhlo v pořádku. Budete přesměrováni...");
        await new Promise(resolve => setTimeout(() => resolve(location.reload()), 1300));
    }
    else if(result == "false") {
        resultTextEl.classList.add("text-danger");
        JSFunctions.showText(resultTextEl.id, "Nesprávné jméno nebo heslo!");

        loginBtnEl.disabled = false;
        loginBtnEl.innerHTML = originalHtml;
    }
    else {
        resultTextEl.classList.add("text-warning");
        JSFunctions.showText(resultTextEl.id, "Při pokusu o přihlášení došlo k chybě. Zkuste to znovu později.");

        loginBtnEl.disabled = false;
        loginBtnEl.innerHTML = originalHtml;
    }
}