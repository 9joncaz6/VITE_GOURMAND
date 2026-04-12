const textarea = document.getElementById('message');
const counter = document.getElementById('counter');
const sendBtn = document.getElementById('sendBtn');

textarea.addEventListener('input', () => {
    const length = textarea.value.length;

    counter.textContent = `${length} / 300`;

    if (length === 0 || length > 300) {
        sendBtn.disabled = true;
    } else {
        sendBtn.disabled = false;
    }
});