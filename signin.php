<?php
session_start();

if(isset($_POST["signin"]))
{
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "hotel_reservation_system"; 

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $email = $_POST["email"];
        $cpassword = $_POST["password"];

        if($conn)
        {
            if(!empty($email) && !empty($cpassword))
            {
                $get_email = "SELECT * FROM customer WHERE email='$email'";
                $result = mysqli_query($conn, $get_email);

                if($result == TRUE) 
                {
                    $value_set = mysqli_fetch_assoc($result);

                    // Check if the query returned a valid user
                    if($value_set)
                    {
                        if(password_verify($cpassword, $value_set["Cuspassword"])) 
                        {
                            $_SESSION["fullname"] = $value_set["Cusname"];
                            $_SESSION["email"] = $value_set["email"];
                            header("Location: userprofile.php");
                            exit();
                        }
                        else
                        {
                            $error_message = "Incorrect email or password!";
                        }
                    }
                    else
                    {
                        $error_message = "No user found with that email!";
                    }
                }
                else
                {
                    $error_message = "Error in Logging: " . mysqli_error($conn);
                }
            }
            else
            {
                $error_message = "Fields cannot be empty!";
            }
        }
        else
        {
            $error_message = "Connection failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Style_files/signinform_design.css">
    <style>
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #2ecc71;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            animation: slideIn 0.5s forwards, fadeOut 0.5s forwards 3s;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .error-notification {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>

    <?php if(isset($error_message)): ?>
        <div class="notification error-notification" id="errorNotification">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <!--Form-->
        <form class="signin-form" id="signinForm" method="POST">
            <h2>Sign In</h2>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <div class="password-toggle">
                    <i class="fas fa-eye" id="togglePassword"></i>
                </div>
            </div>
            
            <div class="forgot-password">
                <a href="forgotpassword.php">Forgot Password?</a>
            </div>
            
            <button type="submit" class="signin-btn" name="signin">Sign In</button>
            
            <div class="signup-prompt">
                <p>Don't have an account? <a href="registration.php">Sign Up</a></p>
            </div>
            
            <div class="social-login-separator">
                <span>Or sign in with</span>
            </div>
            
            <div class="social-login-options">
                <button type="button" class="social-btn google">
                    <i class="fab fa-google"></i> Google
                </button>
                <button type="button" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
            </div>
        </form>
    </div>

    <script src="Scripts/signinform.js"></script>
    <script>
        // Show error notification if it exists
        document.addEventListener('DOMContentLoaded', function() {
            var errorNotification = document.getElementById('errorNotification');
            if(errorNotification) 
            {
                errorNotification.style.display = 'block';
                setTimeout(function() { 
                    errorNotification.style.display = 'none'; 
                }, 3500);
            }
            
            // Password toggle functionality
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>