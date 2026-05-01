const btn = document.getElementById('toggle-btn');
const sidebar = document.getElementById('sidebar');
const main = document.getElementById('main-content');

// We add a check to make sure the button exists on the page
if (btn) {
    btn.onclick = function() {
        sidebar.classList.toggle('-translate-x-full');
        main.classList.toggle('ml-64');
    };
}