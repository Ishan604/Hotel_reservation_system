document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const signinForm = document.getElementById('signinForm');

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle eye icon
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    // Form submission
    signinForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Here you would typically send the data to your server
        console.log('Signing in with:', { email, password });
        
        // For demo purposes, we'll just show an alert
        alert('Sign in functionality would be implemented here.\nEmail: ' + email);
        
        // In a real application, you would:
        // 1. Validate the inputs
        // 2. Send to your server for authentication
        // 3. Handle the response (redirect on success, show error on failure)
    });
});

