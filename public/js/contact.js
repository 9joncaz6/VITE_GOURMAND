document.addEventListener('DOMContentLoaded', () => {

    const textarea = document.getElementById('contact_message'); // ✔ ID réel généré par Symfony
    const counter = document.getElementById('counter');
    const sendBtn = document.getElementById('sendBtn');

    if (!textarea || !counter || !sendBtn) {
        console.warn("contact.js : éléments introuvables");
        return;
    }

    const update = () => {
        const length = textarea.value.length;
        counter.textContent = `${length} / 300`;
        sendBtn.disabled = length === 0 || length > 300;
    };

    textarea.addEventListener('input', update);
    update();
});
