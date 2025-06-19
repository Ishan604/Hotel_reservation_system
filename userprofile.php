<?php
session_start();

$roomno = '';

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

if($conn) 
{
    $email = $_SESSION["email"];
    $user_query = "SELECT * FROM customer WHERE email='$email'";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    $customerid = $user["customer_id"];

    $getroom = "SELECT * FROM rooms";
    $result = mysqli_query($conn, $getroom);
    if(mysqli_num_rows($result) > 0)
    {
        $get = mysqli_fetch_assoc($result);
        $roomno = $get["room_no"];
        //echo "<script>console.log('Room No: $roomno');</script>";
    }


    // Initialize variables
    $room_type = $capacity = '';

    // Get ALL user reservations (don't fetch here, just prepare the query)
    $reservations_query = "SELECT * FROM reservations WHERE customer_email='$email' ORDER BY check_in_date DESC";
    
    // Handle form submissions first
    if (isset($_POST['cancel_reservation'])) 
    {
        $reservation_id = $_POST['reservation_id'];
        // $room_no_to_update = $_POST['room_no']; // Assuming room_no is passed in the form
        
        // Cancel the reservation
        $cancel_query = "DELETE FROM reservations WHERE reservation_id='$reservation_id' AND customer_email='$email'";
        if (mysqli_query($conn, $cancel_query)) 
        {
            // Update the room's availability to 1 (available)
            $delete_room = "DELETE FROM rooms WHERE customer_id='$customerid' AND room_no='$roomno'";
            mysqli_query($conn, $delete_room);

            $success_message = "Reservation cancelled successfully!";
        } 
        else 
        {
            $error_message = "Error cancelling reservation: " . mysqli_error($conn);
        }
    }

    if (isset($_POST['update_reservation'])) 
    {
        // Get the specific reservation being updated
        $reservation_id = $_POST['reservation_id'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $guests = $_POST['guests'];
        $roomtype_updateform = $_POST["roomtype"];
    
        // First validate the dates
        if (strtotime($check_out) <= strtotime($check_in)) 
        {
            $error_message = "Check-out date must be after check-in date";
        } 
        else 
        {
            $update_query = "UPDATE reservations SET 
                            check_in_date='$check_in', 
                            check_out_date='$check_out', 
                            occupants='$guests' 
                            WHERE reservation_id='$reservation_id' AND customer_email='$email'";
    
           
            $update_rooms = "UPDATE rooms SET room_type='$roomtype_updateform', capacity='$guests' 
                            WHERE customer_id='$customerid' AND room_no='$roomno'";
             
    
            $r1 = mysqli_query($conn, $update_query);
            $r2 = mysqli_query($conn, $update_rooms);
    
            if ($r1 === TRUE && $r2 === TRUE) 
            {
                $success_message = "Reservation updated successfully!";
            } 
            else 
            {
                $error_message = "Error updating reservation: " . mysqli_error($conn);
            }
        }
    }

    // Now execute the main reservations query
    $reservations_result = mysqli_query($conn, $reservations_query);
    
    // Get room data (only if needed)
    if(mysqli_num_rows($reservations_result) > 0) 
    {
        $rooms_query = "SELECT * FROM rooms WHERE customer_id='$customerid'";
        $rooms_result = mysqli_query($conn, $rooms_query);
        
        if(mysqli_num_rows($rooms_result) > 0) 
        {
            $room_columns = mysqli_fetch_assoc($rooms_result);
            $room_type = $room_columns["room_type"];
            $capacity = $room_columns["capacity"];
            $roomno = $room_columns["room_no"];
        }
    }
} 
else 
{
    echo "Connection error!" . mysqli_connect_error($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hotel Reservation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="Style_files/userprofile_design.css">
    <style>
        /* Profile Popup Styles */
        .profile-popup {
            display: none;
            position: absolute;
            right: 30px;
            top: 70px;
            width: 280px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 100;
            padding: 15px 0;
            border: 1px solid var(--border-color);
        }

        .profile-popup.active {
            display: block;
        }

        .profile-popup-header {
            padding: 0 15px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .profile-popup-header h4 {
            font-size: 16px;
            color: var(--light-text);
            margin-bottom: 5px;
        }

        .profile-popup-name {
            font-weight: 600;
            font-size: 18px;
            color: var(--text-color);
        }

        .profile-popup-menu {
            list-style: none;
            padding: 10px 0;
        }

        .profile-popup-menu li {
            padding: 8px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            color: var(--text-color);
        }

        .profile-popup-menu li:hover {
            background-color: var(--background-light);
        }

        .profile-popup-menu li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .profile-popup-menu li.sign-out {
            color: var(--error-color);
            border-top: 1px solid var(--border-color);
            margin-top: 5px;
            padding-top: 12px;
        }

        .profile-name-container {
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Checkbox styles for the menu items */
        .checkbox-item {
            display: flex;
            align-items: center;
            width: 100%;
            color: var(--text-color);
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Header Section -->
        <header class="profile-header">
            <div class="logo">Hotel Reservation System</div>
            <div class="user-info">
                <div class="profile-name-container" onclick="toggleProfilePopup()">
                    <div class="profile-avatar">
                        <?php 
                            $initial = strtoupper(substr($user['email'], 0, 1));
                            echo $initial;
                        ?>
                    </div>
                    <div class="user-details">
                        <span class="user-email"><?php echo $user['email']; ?></span>
                        <span class="user-status">Member</span>
                    </div>
                </div>

                <!-- Profile Popup -->
                <div class="profile-popup" id="profilePopup">
                    <div class="profile-popup-header">
                        <h4>UI property</h4>
                        <div class="profile-popup-name"><?php echo $user['Cusaname'] ?? explode('@', $user['email'])[0]; ?></div>
                        <span class="profile-popup-genius">Genius Level 1</span>
                    </div>
                    
                    <ul class="profile-popup-menu">
                        <li>
                            <label class="checkbox-item">
                                <input type="checkbox"> Bookings & Trips
                            </label>
                        </li>
                        <li>
                            <label class="checkbox-item">
                                <input type="checkbox" checked> Genius loyalty programme
                            </label>
                        </li>
                        <li>
                            <label class="checkbox-item">
                                <input type="checkbox"> Rewards & Wallet
                            </label>
                        </li>
                        <li>
                            <label class="checkbox-item">
                                <input type="checkbox"> Reviews
                            </label>
                        </li>
                        <li>
                            <label class="checkbox-item">
                                <input type="checkbox"> Saved
                            </label>
                        </li>
                        <li class="sign-out" onclick="signOut()">
                            <i class="fas fa-sign-out-alt"></i> Sign out
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Greeting Section -->
        <section class="greeting-section">
            <h1>Hi, <?php echo explode('@', $user['email'])[0]; ?></h1> 
            <p>Welcome back to your account</p>
        </section>

        <!-- Main Content -->
        <div class="profile-content">
            <!-- Left Navigation -->
            <aside class="profile-sidebar">
                <nav>
                    <div class="nav-section">
                        <h3>Account</h3>
                        <ul>
                            <li><a href="#" class="active"><i class="fas fa-user"></i> Personal details</a></li>
                            <li><a href="Add_creditcard.php"><i class="fas fa-credit-card"></i> Payment methods</a></li>
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
                    
                    <div class="nav-section">
                        <h3>Help and support</h3>
                        <ul>
                            <li><a href="#"><i class="fas fa-question-circle"></i> Contact Customer service</a></li>
                            <li><a href="#"><i class="fas fa-shield-alt"></i> Safety resource centre</a></li>
                        </ul>
                    </div>
                </nav>
            </aside>

            <!-- Main Profile Content -->
            <main class="profile-main">
                <!-- Reservations Section -->
                <section class="reservations-section">
                    <h2>Your Reservations</h2>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (mysqli_num_rows($reservations_result) > 0): ?>
                        <div class="reservations-list">
                            <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): ?>
                                <div class="reservation-card">
                                    <div class="reservation-header">
                                        <h3><?php echo "Hotel Room Reservation"; ?></h3>
                                        <span class="status-badge"><?php echo ucfirst($reservation['status']); ?></span>
                                    </div>
                                    
                                    <div class="reservation-details">
                                        <div class="detail">
                                            <span class="label">Check-in:</span>
                                            <span class="value"><?php echo date('M d, Y', strtotime($reservation['check_in_date'])); ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Check-out:</span>
                                            <span class="value"><?php echo date('M d, Y', strtotime($reservation['check_out_date'])); ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Guests:</span>
                                            <span class="value"><?php echo $reservation['occupants']; ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Payment Status:</span>
                                            <span class="value"><?php echo ucfirst($reservation['payment_status']); ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Room Type:</span>
                                            <span class="value"><?php echo $room_type; ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Room No:</span>
                                            <span class="value"><?php echo $roomno; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="reservation-actions">
                                        <button class="btn-update" onclick="showUpdateForm('<?php echo $reservation['reservation_id']; ?>')">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                            <button type="submit" name="cancel_reservation" class="btn-cancel">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Update Form (Hidden by default) -->
                                    <div id="update-form-<?php echo $reservation['reservation_id']; ?>" class="update-form" style="display: none;">
                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                            
                                            <div class="form-group">
                                                <label for="check_in">Check-in Date:</label>
                                                <input type="date" id="check_in" name="check_in" 
                                                       value="<?php echo $reservation['check_in_date']; ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="check_out">Check-out Date:</label>
                                                <input type="date" id="check_out" name="check_out" 
                                                       value="<?php echo $reservation['check_out_date']; ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="guests">Number of Guests:</label>
                                                <input type="number" id="guests" name="guests" min="1" 
                                                       value="<?php echo $reservation['occupants']; ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="roomtype">Room Type:</label>
                                                <input type="text" id="roomtype" name="roomtype" 
                                                       value="<?php echo $room_type; ?>" required>
                                            </div>
                                            
                                            <div class="form-actions">
                                                <button type="button" class="btn-cancel" 
                                                        onclick="hideUpdateForm('<?php echo $reservation['reservation_id']; ?>')">
                                                    Cancel
                                                </button>
                                                <button type="submit" name="update_reservation" class="btn-update">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-reservations">
                            <i class="fas fa-suitcase-rolling"></i>
                            <p>You don't have any reservations yet.</p>
                            <a href="booking.html" class="btn-primary">Book a hotel now</a>
                        </div>
                    <?php endif; ?>
                </section>
                
                <!-- Account Details Section -->
                <section class="account-section">
                    <h2>Account Details</h2>
                    <div class="account-details">
                        <div class="detail-row">
                            <span class="detail-label">Full Name:</span>
                            <span class="detail-value"><?php echo $user['Cusname']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo $user['email']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo $user['phone'] ?? 'Not provided'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">NIC:</span>
                            <span class="detail-value"><?php echo $user['NIC'] ?? 'Not provided'; ?></span>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        function showUpdateForm(reservationId) 
        {
            document.getElementById('update-form-' + reservationId).style.display = 'block';
        }
        
        function hideUpdateForm(reservationId) 
        {
            document.getElementById('update-form-' + reservationId).style.display = 'none';
        }
        
        function toggleProfilePopup() 
        {
            const popup = document.getElementById('profilePopup');
            popup.classList.toggle('active');
        }

        function signOut() 
        {
            window.location.href = 'userlogout.php';
        }

        document.addEventListener('click', function(event) 
        {
            const popup = document.getElementById('profilePopup');
            const profileContainer = document.querySelector('.profile-name-container');
            
            if (!popup.contains(event.target)) {
                if (!profileContainer.contains(event.target)) {
                    popup.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
