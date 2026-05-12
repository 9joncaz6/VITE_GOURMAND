document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.burger');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (burger && mobileMenu) {
        burger.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });
    }
});


document.querySelectorAll('.filter-input').forEach(input => {
    input.addEventListener('change', () => {

        const params = new URLSearchParams(new FormData(document.querySelector('#filters')));

        fetch('/menu/filter?' + params.toString())
            .then(r => r.json())
            .then(data => {
                document.querySelector('#menu-list').innerHTML = data.html;
            });
    });
});

