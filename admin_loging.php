<?php
session_start();

if(isset($_POST["admin_login"])) 
{
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "hotel_reservation_system"; 

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if($_SERVER["REQUEST_METHOD"] == "POST") 
    {
        $username_input = $_POST["username"];
        $password_input = $_POST["password"];

        if($conn) 
        {
            if(!empty($username_input) && !empty($password_input)) 
            {
                $get_user = "SELECT * FROM clerks WHERE username='$username_input'";
                $result = mysqli_query($conn, $get_user);

                if($result == TRUE) 
                {
                    $user = mysqli_fetch_assoc($result);

                    if($user) 
                    {
                        if(password_verify($password_input, $user["password"])) 
                        {
                            $_SESSION["admin_id"] = $user["clerk_id"];
                            $_SESSION["admin_name"] = $user["full_name"];
                            $_SESSION["admin_username"] = $user["username"];

                            header("Location: admin_dashboard.php");
                            exit();
                        } 
                        else 
                        {
                            $error_message = "Incorrect username or password!";
                        }
                    } 
                    else 
                    {
                        $error_message = "No admin found with that username!";
                    }
                } 
                else 
                {
                    $error_message = "Error in logging in: " . mysqli_error($conn);
                }
            } 
            else 
            {
                $error_message = "Fields cannot be empty!";
            }
        } 
        else 
        {
            $error_message = "Connection to database failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Style_files/admin_login_design.css">
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
        <form class="login-form" id="adminLoginForm" method="POST">
            <h2>Clerk Login</h2>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                </div>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
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
            
            <button type="submit" class="login-btn" name="admin_login">Login</button>
            
            <div class="back-to-home">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </form>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
        
        // Show error notification if exists
        const errorNotification = document.getElementById('errorNotification');
        if(errorNotification) {
            errorNotification.style.display = 'block';
            setTimeout(() => { errorNotification.style.display = 'none'; }, 3500);
        }
    </script>
</body>
</html>