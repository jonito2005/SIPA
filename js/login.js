// Simple form validation (add more as needed)
const form = document.querySelector('.login-container form');

form.addEventListener('submit', (event) => {
    const emailInput = document.querySelector('input[type="email"]');
    const passwordInput = document.querySelector('input[type="password"]');

    if (!emailInput.value || !passwordInput.value) {
        alert('Please fill in all fields.');
        event.preventDefault(); // Stop form submission
    } 
});
