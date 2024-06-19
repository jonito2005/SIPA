// script.js
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('main-content');
    sidebar.classList.toggle('open');
    mainContent.classList.toggle('shifted');
}

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.querySelector('form');
    loginForm.addEventListener('submit', function (event) {
        // Basic client-side validation (you'll do more in PHP)
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (emailInput.value === '' || passwordInput.value === '') {
            alert('Please fill in all fields.');
            event.preventDefault(); // Prevent form submission
        } 
    });
});