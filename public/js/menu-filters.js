document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        fetch(this.href)
            .then(response => response.text())
            .then(html => {
                // Extraire uniquement la partie liste
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newList = doc.querySelector('#menu-list').innerHTML;

                document.querySelector('#menu-list').innerHTML = newList;
            });
    });
});
