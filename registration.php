<?php

session_start(); // Start the session

    if(isset($_POST["create"])) 
    {
        $servername = "localhost"; 
        $username = "root"; 
        $password = ""; 
        $dbname = "hotel_reservation_system"; 

        $conn = mysqli_connect($servername, $username, $password, $dbname);
             
        if($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            // Get the last customer ID to generate the next one
            $get_last_id = "SELECT customer_id FROM customer ORDER BY customer_id DESC LIMIT 1";
            $result = mysqli_query($conn, $get_last_id);
            $row = mysqli_fetch_assoc($result); 
            
            $last_id = 0;
            if($row) 
            {
                $last_id = (int)substr($row['customer_id'], 2); // Extract the numeric part after "CU"
            }
            $new_id = "CU" . str_pad($last_id + 1, 4, "0", STR_PAD_LEFT);
            
            $name = $_POST["fullName"];
            $email = $_POST["email"];
            $cpassword = $_POST["password"];
            $phone = $_POST["phone"];
            $nic = $_POST["nic"];
            $address = $_POST["address"];

            if($conn) 
            {  
                if(filter_var($email, FILTER_VALIDATE_EMAIL))
                {
                    $hashPassword = password_hash($cpassword, PASSWORD_DEFAULT);
                    $add_details = "INSERT INTO customer (customer_id, Cusname, email, Cuspassword, phone, nic, Cusaddress) VALUES('$new_id','$name','$email', '$hashPassword', '$phone','$nic','$address')";
                    $result = mysqli_query($conn, $add_details);
                
                    if($result) 
                    {
                        header("Location: reservation.php");
                        exit();
                    } 
                    else 
                    {
                        $error_message = "Error in Adding Customer: " . mysqli_error($conn);
                    }
                }
                else
                {
                    $error_message = "Invalid Email Address";
                }
            } 
            else 
            {
                $error_message = "Connection Failed";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="Style_files/registerform_design.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .input-group input[type="password"],
        .input-group textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            color: #333;
            background-color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
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
        <form class="create-account-form" method="POST" action="">
            <h2>Create Your Account</h2>

            <div class="input-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g., +94771234567" required>
            </div>

            <div class="input-group">
                <label for="nic">NIC (National Identity Card)</label>
                <input type="text" id="nic" name="nic" placeholder="e.g., 123456789V or 199012345678" required>
            </div>

            <div class="input-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" placeholder="Enter your full address" required></textarea>
            </div>

            <button type="submit" class="create-account-btn" name="create">Create Account</button>

            <div class="login-prompt">
                <p>Already have an account? <a href="signin.php">Sign In</a></p>
            </div>

            <div class="social-login-separator">
                <span>Or create account with</span>
            </div>

            <div class="social-login-options">
                <button type="button" class="social-btn google">
                    <i class="fab fa-google"></i> Google
                </button>
                <button type="button" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
                <button type="button" class="social-btn twitter">
                    <i class="fab fa-twitter"></i> Twitter
                </button>
            </div>
        </form>
    </div>
    
    <script>
        if(errorNotification) 
        {
                errorNotification.style.display = 'block';
                setTimeout(() => { errorNotification.style.display = 'none'; }, 3500);
        }
    </script>

</body>
</html>