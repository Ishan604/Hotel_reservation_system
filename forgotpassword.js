document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.getElementById('toggleNewPassword').addEventListener('click', function() {
        const passwordField = document.getElementById('newPassword');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const passwordField = document.getElementById('confirmPassword');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
});