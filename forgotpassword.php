<?php
session_start();

$error_message = '';
$success_message = '';

if(isset($_POST["confirm"])) 
{
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "hotel_reservation_system"; 

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if(!$conn) 
    {
        $error_message = "Connection failed: " . mysqli_connect_error();
    } 
    else 
    {
        $email = $_POST["email"];
        $newpassword = $_POST["newPassword"];
        $confirmpassword = $_POST["confirmPassword"];

        // Validate passwords match
        if($newpassword != $confirmpassword) 
        {
            $error_message = "Passwords do not match";
        } 
        else 
        {
            // Check if email exists
            $query = "SELECT * FROM customer WHERE email = '$email'";
            $result = mysqli_query($conn, $query);
            
            if(mysqli_num_rows($result) > 0) 
            {
                // Email exists, update password
                $hashed_password = password_hash($confirmpassword, PASSWORD_DEFAULT);
                $update_query = "UPDATE customer SET Cuspassword = '$hashed_password' WHERE email = '$email'";
                
                if(mysqli_query($conn, $update_query)) 
                {
                    $success_message = "Password changed successfully";
                    // Redirect after 3 seconds
                    header("Refresh: 3; url=signin.php");
                } 
                else 
                {
                    $error_message = "Error updating password: " . mysqli_error($conn);
                }
            } 
            else 
            {
                $error_message = "Email not found";
            }
        }
        
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="Style_files/forgotpassword.css?v=1.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Form -->
        <form class="forgot-password-form" id="forgotPasswordForm" method="post" action="">
            <h2>Reset Password</h2>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                <div class="password-toggle">
                    <i class="fas fa-eye" id="toggleNewPassword"></i>
                </div>
            </div>
            
            <div class="input-group">
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                <div class="password-toggle">
                    <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                </div>
            </div>
            
            <button type="submit" class="reset-btn" name="confirm">Reset Password</button>
            
            <div class="back-to-login">
                <p>Remember your password? <a href="signin.php">Sign In</a></p>
            </div>
        </form>
    </div>

    <!-- PHP Notifications -->
    <?php if(!empty($error_message)): ?>
        <div class="notification error-notification">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($success_message)): ?>
        <div class="notification success-notification">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <!-- Simplified JavaScript (just for password toggle) -->
    <script src="forgotpassword.js"></script>
</body>
</html>