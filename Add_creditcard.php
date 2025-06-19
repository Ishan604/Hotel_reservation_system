<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION["email"]) && !isset($_SESSION["fullname"])) 
{
    header("Location: signin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_reservation_system";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn) 
{
    $email = $_SESSION["email"];

    // Get user details
    $user_query = "SELECT * FROM customer WHERE email='$email'";
    $user_result = mysqli_query($conn, $user_query);
    if(mysqli_num_rows($user_result) > 0)
    {
        $user = mysqli_fetch_assoc($user_result);
        $customer_id = $user["customer_id"];
    }

    // Get pending reservations
    $pending_reservations_query = "SELECT * FROM reservations WHERE customer_email='$email' AND status='pending'";
    $pending_reservations_result = mysqli_query($conn, $pending_reservations_query);

    // Get existing credit cards
    $cards_query = "SELECT * FROM credit_cards WHERE customer_id='$customer_id'";
    $cards_result = mysqli_query($conn, $cards_query);

    // Handle credit card submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
        if(isset($_POST["add_card"]))
        {
            $card_number = mysqli_real_escape_string($conn, $_POST["card_number"]);
            $expiry = mysqli_real_escape_string($conn, $_POST["expiry"]);
            $cvv = mysqli_real_escape_string($conn, $_POST["cvv"]);
            $reservation_id = mysqli_real_escape_string($conn, $_POST["reservation_id"]);
            
            // Validate card number (basic validation)
            if (!preg_match('/^\d{16}$/', $card_number)) 
            {
                $error_message = "Invalid card number. Must be 16 digits.";
            } 
            elseif (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) 
            {
                $error_message = "Invalid expiry date format. Use MM/YY.";
            } 
            elseif (!preg_match('/^\d{3,4}$/', $cvv)) 
            {
                $error_message = "Invalid CVV. Must be 3 or 4 digits.";
            } 
            else 
            {
                // Insert into credit_cards table
                $insert_card_query = "INSERT INTO credit_cards (customer_id, card_number, expiry, cvv) 
                                    VALUES ('$customer_id', '$card_number', '$expiry', '$cvv')";
                
                if (mysqli_query($conn, $insert_card_query)) 
                {
                    // Update reservation status to confirmed
                    $update_reservation_query = "UPDATE reservations SET status='confirmed' 
                                            WHERE reservation_id='$reservation_id' AND customer_email='$email'";
                    
                    if (mysqli_query($conn, $update_reservation_query)) 
                    {
                        $success_message = "Credit card added successfully and reservation confirmed!";
                        // Refresh data
                        $pending_reservations_result = mysqli_query($conn, $pending_reservations_query);
                        $cards_result = mysqli_query($conn, $cards_query);
                    } 
                    else 
                    {
                        $error_message = "Error updating reservation: " . mysqli_error($conn);
                    }
                } 
                else 
                {
                    $error_message = "Error adding credit card: " . mysqli_error($conn);
                }
            }
        }
    }
}
else
{
    echo "Connection error!".mysqli_connect_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Hotel Reservation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Style_files/userprofile_design.css">
    <link rel="stylesheet" href="Style_files/card_details_page_design.css">
</head>
<body>
    <div class="profile-container">
        <!-- Header Section (same as userprofile.php) -->
        <header class="profile-header">
            <div class="logo">Hotel Reservation System</div>
            <div class="user-info">
                <div class="profile-name-container" onclick="toggleProfilePopup()">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['email'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <span class="user-email"><?php echo $user['email']; ?></span>
                        <span class="user-status">Member</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Greeting Section -->
        <section class="greeting-section">
            <h1>Payment Methods</h1>
            <p>Manage your credit cards and payment information</p>
        </section>

        <!-- Main Content -->
        <div class="profile-content">
            <!-- Left Navigation (same as userprofile.php) -->
            <aside class="profile-sidebar">
                <nav>
                    <div class="nav-section">
                        <h3>Account</h3>
                        <ul>
                            <li><a href="userprofile.php"><i class="fas fa-user"></i> Personal details</a></li>
                            <li><a href="#" class="active"><i class="fas fa-credit-card"></i> Payment methods</a></li>
                            <li><a href="#"><i class="fas fa-lock"></i> Security settings</a></li>
                        </ul>
                    </div>
                    
                    <div class="nav-section">
                        <h3>Travel activity</h3>
                        <ul>
                            <li><a href="#"><i class="fas fa-suitcase"></i> Trips and bookings</a></li>
                            <li><a href="#"><i class="fas fa-heart"></i> Saved lists</a></li>
                            <li><a href="#"><i class="fas fa-star"></i> My reviews</a></li>
                        </ul>
                    </div>
                </nav>
            </aside>

            <!-- Payment Methods Content -->
            <main class="profile-main">
                <div class="payment-container">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (mysqli_num_rows($pending_reservations_result) > 0): ?>
                        <div class="pending-alert">
                            <i class="fas fa-exclamation-circle"></i> You have pending reservations that require payment information.
                        </div>
                        
                        <div class="payment-header">
                            <h2>Add Payment Method</h2>
                            <p>Please add a credit card to confirm your reservation</p>
                        </div>
                        
                        <form class="card-form" method="POST">
                            <input type="hidden" name="reservation_id" value="<?php 
                                $reservation = mysqli_fetch_assoc($pending_reservations_result);
                                echo $reservation['reservation_id'];
                            ?>">
                            
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" 
                                       placeholder="1234 5678 9012 3456" maxlength="16" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry">Expiry Date (MM/YY)</label>
                                    <input type="text" id="expiry" name="expiry" 
                                           placeholder="MM/YY" maxlength="5" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" 
                                           placeholder="123" maxlength="4" required>
                                </div>
                            </div>
                            
                            <button type="submit" name="add_card" class="btn-submit">
                                <i class="fas fa-credit-card"></i> Add Card & Confirm Reservation
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <div class="payment-header">
                        <h2>Your Saved Cards</h2>
                    </div>
                    
                    <?php if (mysqli_num_rows($cards_result) > 0): ?>
                        <div class="cards-list">
                            <?php while ($card = mysqli_fetch_assoc($cards_result)): ?>
                                <div class="card-item">
                                    <div class="card-info">
                                        <i class="fas fa-credit-card card-icon"></i>
                                        <div>
                                            <div class="card-number">•••• •••• •••• <?php echo substr($card['card_number'], -4); ?></div>
                                            <div class="card-expiry">Expires <?php echo $card['expiry']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-cards">
                            <i class="fas fa-credit-card" style="font-size: 40px; margin-bottom: 15px;"></i>
                            <p>You haven't added any credit cards yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Format card number input
        document.getElementById('card_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Format expiry date input
        document.getElementById('expiry').addEventListener('input', function(e) {
            this.value = this.value
                .replace(/\D/g, '')
                .replace(/^(\d{2})/, '$1/')
                .substr(0, 5);
        });
        
        // Format CVV input
        document.getElementById('cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        function toggleProfilePopup() {
            const popup = document.getElementById('profilePopup');
            if (popup) popup.classList.toggle('active');
        }
    </script>
</body>
</html>