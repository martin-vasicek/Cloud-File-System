export function hashString(string) {
    let hashedString = CryptoJS.SHA256(string).toString();

    return hashedString;
}

export async function postDataFetch(url = '', data = {}) {
    const formData = new URLSearchParams();
    for (const key in data) {
        formData.append(key, data[key]);
    }

    const response = await fetch(url, {
        method: 'POST',
        body: formData
    });

    return response.text();
}

export async function showText(textElId, text) {
    let textEl = document.getElementById(textElId);
    textEl.textContent = text;
    textEl.style.opacity = '0';

    await new Promise(resolve => setTimeout(resolve, 0.8));

    textEl.style.transition = 'opacity 0.8s ease-in-out';
    textEl.style.opacity = '1';
}

export async function getUrlXHR(url) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.onload = function() {
            resolve({ status: xhr.status });
        };
        xhr.onerror = function() {
            reject(xhr.statusText);
        };
        xhr.send();
    });
}