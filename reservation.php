<?php
session_start();

// Initialize all variables with default values
$location = $checkIn = $checkOut = $roomtype = $roomno = $capacity = $roomname = '';
$adults = $children = $rooms = 0;
$numNights = 0;
$error_message = '';
$success_message = '';
$form_submitted = false;

// Safely retrieve all session variables
if (isset($_SESSION['location'])) 
{
    $location = $_SESSION['location'];
    $checkIn = $_SESSION['checkIn'];
    $checkOut = $_SESSION['checkOut'];
    $adults = $_SESSION['adults'];
    $children = $_SESSION['children'];
    $rooms = $_SESSION['rooms'];
    $roomtype = $_SESSION['Room_type'];
    $roomno = $_SESSION['Room_No'];
    $capacity = $_SESSION['capacity'];
}

if(isset($_SESSION['Room_name']))
{
    $roomname = $_SESSION['Room_name'];
} 
else 
{
    $roomname = "empty"; // Default room name if not set
}

// Calculate total length of stay (number of nights)
if (!empty($checkIn) && !empty($checkOut)) 
{
    try 
    {
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $interval = $checkInDate->diff($checkOutDate);
        $numNights = $interval->days;
    } 
    catch (Exception $e) 
    {
        $error_message = "Invalid date format in session data";
    }
}

// Database connection and form handling
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_reservation_system";

$conn = mysqli_connect($servername, $username, $password, $dbname);


if (isset($_POST['reserve'])) 
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
        if(true) 
        {
            if (!preg_match('/^\d{16}$/', $_POST['card_number'])) 
            {
                $error_message = "Card number must be 16 digits.";
            }

            // Validate CVV (only digits, 3 digits)
            elseif (!preg_match('/^\d{3}$/', $_POST['cvv'])) 
            {
                $error_message = "CVV must be 3 digits.";
            }

            // Validate expiry (already covered by pattern, but reconfirm)
            elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $_POST['expiry'])) 
            {
                $error_message = "Expiry date must be in MM/YY format.";
            }
            else  
            {
                // Proceed with inserting into database
                // Validate required fields
                $required = ['email', 'firstName', 'lastName', 'occupants', 'phone', 'country'];
                foreach ($required as $field) 
                {
                    if (empty($_POST[$field])) 
                    {
                        $error_message = "Please fill in all required fields";
                        break;
                    }
                }

                if (empty($error_message)) 
                {
                    $email = $_POST['email'];
                    $firstName = $_POST['firstName'];
                    $lastName = $_POST['lastName'];
                    $occupants = $_POST['occupants'];
                    $phone = $_POST['phone'];
                    $country = $_POST['country'];
                    $is_available = 0; // mark reserved

                    if ($conn) 
                    {
                        if (!isset($_SESSION['hotel_id'])) 
                        {
                            $error_message = "Hotel Id not set";
                        }
                        else
                        {
                            $hotelid = $_SESSION['hotel_id'];
                        }
                        
                        $check_email_query = "SELECT customer_id FROM customer WHERE email='$email'";
                        $result = mysqli_query($conn, $check_email_query);

                        if (mysqli_num_rows($result) > 0) 
                        {
                            $user = mysqli_fetch_assoc($result);
                            $customer_id = $user['customer_id'];

                            // Check if this customer already has a reservation for these dates
                            $check_reservation = "SELECT reservation_id FROM reservations 
                                                WHERE customer_id='$customer_id' 
                                                AND check_in_date='$checkIn' 
                                                AND check_out_date='$checkOut'";
                            $res_result = mysqli_query($conn, $check_reservation);

                            if (mysqli_num_rows($res_result) > 0) 
                            {
                                $error_message = "You already have a reservation for these dates.";
                            } 
                            else 
                            {
                                // Insert reservation
                                $insert_reservation = "INSERT INTO reservations (customer_id, customer_email, check_in_date, check_out_date, occupants, status, branch) 
                                                    VALUES ('$customer_id', '$email', '$checkIn', '$checkOut', '$occupants', 'pending', '$location')";
                                
                                if (mysqli_query($conn, $insert_reservation)) 
                                {
                                    $reservation_id = mysqli_insert_id($conn);

                                    // Insert room details
                                    $insert_room = "INSERT INTO rooms (customer_id, hotel_id, room_no, room_type, is_available, capacity)
                                                    VALUES ('$customer_id', '$hotelid', '$roomno', '$roomtype', '$is_available', '$capacity')";
                                    
                                    if (mysqli_query($conn, $insert_room)) 
                                    {
                                        $success_message = "Reservation successful!";

                                        // Insert credit card if provided
                                        if (isset($_POST['add_credit_card']) && $_POST['add_credit_card'] === 'yes') 
                                        {
                                            $card_number = $_POST['card_number'];
                                            $expiry = $_POST['expiry'];
                                            $cvv = $_POST['cvv'];

                                            if (!empty($card_number) && !empty($expiry) && !empty($cvv)) 
                                            {
                                                $insert_payment = "INSERT INTO credit_cards (customer_id, card_number, expiry, cvv) 
                                                                VALUES ('$customer_id', '$card_number', '$expiry', '$cvv')";
                                                
                                                if (mysqli_query($conn, $insert_payment)) 
                                                {
                                                    $update_status = "UPDATE reservations SET status='confirmed' WHERE reservation_id='$reservation_id'";
                                                    mysqli_query($conn, $update_status);
                                                } 
                                                else 
                                                {
                                                    $error_message = "Error in inserting payment details: " . mysqli_error($conn);
                                                }
                                            }
                                        }

                                        // Clear POST data to prevent resubmission
                                        $_POST = array();
                                    } 
                                    else 
                                    {
                                        $error_message = "Error in inserting room details: " . mysqli_error($conn);
                                    }
                                } 
                                else 
                                {
                                    $error_message = "Error in inserting reservation: " . mysqli_error($conn);
                                }
                            }
                        } 
                        else 
                        {
                            $error_message = "Email not found! Please register first.";
                        }
                    } 
                    else 
                    {
                        $error_message = "Connection failed!";
                    }
                }
            } 

        }
    }
}

// Reset form submission flag when coming from another page
if (!isset($_POST['reserve'])) 
{
    $_SESSION['form_submitted'] = false;
}
// Set default room details if not in session
$hotelname = "Hotel";
$roomAddress = "42, Humes Road, 80000 Galle, Sri Lanka";
$roomRating = "9.1 Superb · 181 reviews"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking.com - Complete your reservation</title>
    <link rel="stylesheet" href="Style_files/reservation_design.css">
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
        
        .success-notification {
            background-color: #2ecc71;
        }
        
        .error-notification {
            background-color: #e74c3c;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .good-to-know ul {
        list-style-type: none;
        padding-left: 0;
    }

        .good-to-know li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 8px;
        }

        .good-to-know li::before {
            content: "✓"; /* Unicode checkmark */
            color: #4CAF50;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .apartment-features ul {
        list-style-type: none;
        padding-left: 0;
    }

        .apartment-features li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 8px;
        }

        .apartment-features li::before {
            content: "✓"; /* Unicode checkmark */
            color: #4CAF50;
            position: absolute;
            left: 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    
    <?php if(!empty($error_message)): ?>
        <div class="notification error-notification" id="errorNotification">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if(!empty($success_message)): ?>
        <div class="notification success-notification" id="successNotification">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <header class="booking-header">
        <div class="container">
            <div class="logo">The<span style="font-size: larger; color:#ff5a5f">Crown</span>Stays</div>
            <nav class="main-nav">
                <a href="registration.php" class="btn btn-register">Register</a>
                <a href="signin.php" class="btn btn-signin">Sign in</a>
                <a href="index.html" class="btn btn-signin">Home</a>
            </nav>
        </div>
    </header>

    <main class="container reservation-content">
        <div class="left-column">
            <section class="room-details-summary">
                <div class="stars">
                    <span class="star-icon" style="color: orange">★</span>
                    <span class="star-icon" style="color: orange">★</span>
                    <span class="star-icon" style="color: orange">★</span>
                </div>
                <h2><?php echo $hotelname ?></h2>
                <p><?php echo htmlspecialchars($roomAddress); ?></p>
                <div class="rating">
                    <span class="rating-score">9.1</span>
                    <span class="rating-text">Superb · 181 reviews</span>
                </div>
                <ul class="amenities">
                    <li><img src="img/icons/wifi.png" style="width: 20px;"> WiFi</li>
                    <li><img src="img/icons/busstop.png" style="width: 20px;"> Bus Stand</li>
                    <li><img src="img/icons/parking.png" style="width: 20px;"> Parking</li>
                </ul>
            </section>

            <section class="your-booking-details">
                <h3>Your booking details</h3>
                <div class="booking-info-row">
                    <span class="label">Check-in</span>
                    <span class="value"><?php echo !empty($checkIn) ? date('D M d Y', strtotime($checkIn)) : 'N/A'; ?></span>
                    <span class="time">12:30 - 22:00</span>
                </div>
                <div class="booking-info-row">
                    <span class="label">Check-out</span>
                    <span class="value"><?php echo !empty($checkOut) ? date('D M d Y', strtotime($checkOut)) : 'N/A'; ?></span>
                    <span class="time">08:30 - 12:00</span>
                </div>
                <p class="total-length">Total length of stay: <strong><?php echo $numNights; ?> night<?php echo ($numNights > 1) ? 's' : ''; ?></strong></p>
                <p class="selected-rooms">You selected <strong><?php echo htmlspecialchars($rooms); ?> room for <?php echo htmlspecialchars($adults); ?> adult<?php echo ($adults > 1) ? 's' : ''; ?></strong></p>
                <a href="room1_details.php" class="change-selection">Change your selection</a>
            </section>
        </div>

        <div class="right-column">
            <div class="highlight-message">
                <span style="color: green">If you don't have any account then create an account and if you have an account
                then login to your account
                </span>
                <div class="highlight-actions">
                    <a href="signin.php" class="highlight-link">Sign in</a> or <a href="registration.php" class="highlight-link">Create a free account</a>
                </div>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="reservation-form">
                <h3>Enter your details</h3>
                <div class="message-box required-info">
                    Almost done! Just fill in the <span class="required-star">*</span> required info
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="firstName">First name <span class="required-star">*</span></label>
                        <input type="text" id="firstName" name="firstName" required value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last name <span class="required-star">*</span></label>
                        <input type="text" id="lastName" name="lastName" required value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email address <span class="required-star">*</span></label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small>Confirmation email goes to this address</small>
                </div>

                <div class="form-group">
                    <label for="occupants">Number of Occupants <span class="required-star">*</span></label>
                    <input type="text" id="occupants" name="occupants" min="1" required value="<?php echo isset($_POST['occupants']) ? htmlspecialchars($_POST['occupants']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="country">Country/Region <span class="required-star">*</span></label>
                    <select id="country" name="country" required>
                        <option value="Sri Lanka" <?php echo (isset($_POST['country']) && $_POST['country'] === 'Sri Lanka') ? 'selected' : ''; ?>>Sri Lanka</option>
                        <!-- Add more countries as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone">Phone number <span class="required-star">*</span></label>
                    <div class="phone-input-group">
                        <span class="phone-prefix">LK +94</span>
                        <input type="tel" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group radio-group">
                    <label>Who are you booking for? (optional)</label>
                    <div>
                        <input type="radio" id="mainGuest" name="bookingFor" value="mainGuest" checked>
                        <label for="mainGuest">I am the main guest</label>
                    </div>
                    <div>
                        <input type="radio" id="someoneElse" name="bookingFor" value="someoneElse" <?php echo (isset($_POST['bookingFor']) && $_POST['bookingFor'] === 'someoneElse') ? 'checked' : ''; ?>>
                        <label for="someoneElse">Booking is for someone else</label>
                    </div>
                </div>

                <div class="form-group radio-group">
                    <label>Are you travelling for work? (optional)</label>
                    <div>
                        <input type="radio" id="travelWorkYes" name="travelWork" value="yes" <?php echo (isset($_POST['travelWork']) && $_POST['travelWork'] === 'yes') ? 'checked' : ''; ?>>
                        <label for="travelWorkYes">Yes</label>
                    </div>
                    <div>
                        <input type="radio" id="travelWorkNo" name="travelWork" value="no" checked>
                        <label for="travelWorkNo">No</label>
                    </div>
                </div>

                <section class="good-to-know">
                    <h3>Good to know:</h3>
                    <ul>
                        <li>Credit card details needed</li>
                        <li>Stay flexible: You can cancel for free before <?php echo !empty($checkIn) ? date('D M d Y', strtotime($checkIn)) : 'your check-in date'; ?> at 7.00 P.M</li>
                        <li>You'll get the entire apartment to yourself.</li>
                        <li>No payment needed today. You'll pay when you stay.</li>
                    </ul>
                </section>

                <section class="arrival-time">
                    <h3>Your arrival time</h3>
                    <div class="message-box success-info">
                        You can check in between 12:00 A.M and 18:00 P.M
                    </div>
                    <div class="form-group">
                        <label for="arrivalTime">Add your estimated arrival time (optional)</label>
                        <select id="arrivalTime" name="arrivalTime">
                            <option value="">Please select</option>
                            <?php
                            $start = strtotime('12:30');
                            $end = strtotime('22:00');
                            while ($start <= $end) {
                                $time = date('H:i', $start);
                                $selected = (isset($_POST['arrivalTime']) && $_POST['arrivalTime'] === $time) ? 'selected' : '';
                                echo '<option value="' . $time . '" ' . $selected . '>' . $time . '</option>';
                                $start = strtotime('+30 minutes', $start);
                            }
                            ?>
                        </select>
                        <small>Time is for Galle time zone</small>
                    </div>
                </section>

                <div class="price-summary">
                    <h3>Your price summary</h3>
                    <div class="price-row">
                        <span class="price-label">Price</span>
                        <span class="price-value">US$27</span>
                    </div>
                    <small>+US$3 taxes and charges</small>
                    <div class="price-information">
                        <p>Excludes US$2.70 in taxes and charges</p>
                        <p>10 % Property service charge <span>US$2.70</span></p>
                        <a href="#" class="hide-details">Hide details</a>
                    </div>
                </div>

                <section class="payment-schedule">
                    <h3>Your payment schedule</h3>
                    <p>No payment today. You'll pay when you stay.</p>
                </section>

                <section class="cancellation-policy">
                    <h3>How much will it cost to cancel?</h3>
                    <p>Free cancellation before <?php echo !empty($checkIn) ? date('D M d', strtotime($checkIn)) : 'your check-in date'; ?></p>
                    <p>From 00:00 <?php echo !empty($checkIn) ? date('D M d', strtotime($checkIn . ' + 1 day')) : 'the next day'; ?> <span class="cancellation-fee">2000 LKR</span></p>
                </section>

                <div class="limited-supply">
                    Limited supply for your dates!
                    <p>121 apartments like this are already unavailable on our site</p>
                </div>

                <section class="apartment-features">
                    <h3>Apartment with Terrace</h3>
                    <ul>
                        <li>Breakfast included in the price</li>
                        <li>Free cancellation before <?php echo !empty($checkIn) ? date('D M d Y', strtotime($checkIn)) : 'your check-in date'; ?></li>
                        <li>Guests: <?php echo htmlspecialchars($adults); ?> adult<?php echo ($adults > 1) ? 's' : ''; ?></li>
                        <li>Spotless apartment - 9.2</li>
                        <li>No smoking</li>
                    </ul>
                </section>

                <!-- Add Credit Card Details Section -->
                <div class="form-group">
                    <label><span style="color: #cc0000;">*</span>Add Card Details?</label>
                    <div class="radio-group">
                        <div>
                            <input type="radio" id="cc_yes" name="add_credit_card" value="yes" <?php echo (isset($_POST['add_credit_card']) && $_POST['add_credit_card'] === 'yes') ? 'checked' : ''; ?>/>
                            <label for="cc_yes">Yes</label>
                        </div>
                        <div>
                            <input type="radio" id="cc_no" name="add_credit_card" value="no" <?php echo (isset($_POST['add_credit_card']) && $_POST['add_credit_card'] === 'no') ? 'checked' : ''; ?>/>
                            <label for="cc_no">No</label>
                        </div>
                    </div>
                </div>

                <!-- Payment Access Form (Initially hidden) -->
                <div id="payment-details" style="display:none; margin-top: 15px;">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input
                            type="text"
                            id="card_number"
                            name="card_number"
                            maxlength="16"
                            inputmode="numeric"
                            pattern="\d{16}"
                            placeholder="1234 5678 9012 3456"
                            autocomplete="off"
                            required
                            value="<?php echo isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : ''; ?>"
                        />
                    </div>
                    <div class="form-group form-group-row">
                        <div style="flex:1;">
                            <label for="expiry">Expiry (MM/YY)</label>
                            <input
                                type="text"
                                id="expiry"
                                name="expiry"
                                maxlength="5"
                                inputmode="numeric"
                                pattern="^(0[1-9]|1[0-2])\/\d{2}$"
                                placeholder="MM/YY"
                                required
                                value="<?php echo isset($_POST['expiry']) ? htmlspecialchars($_POST['expiry']) : ''; ?>"
                            />
                        </div>
                        <div style="flex:1;">
                            <label for="cvv">CVV</label>
                            <input
                                type="text"
                                id="cvv"
                                name="cvv"
                                maxlength="3"
                                placeholder="123"
                                autocomplete="off"
                                value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>"
                            />
                        </div>
                    </div>
                </div>

                <!-- Red Warning Message for No -->
                <div
                    id="cancel-warning"
                    style="
                        display: none;
                        color: #cc0000;
                        font-weight: bold;
                        margin-top: 15px;
                        border: 1px solid #cc0000;
                        padding: 10px;
                        border-radius: 5px;
                        background-color: #ffebe6;">
                    The reservation will automatically cancel at 7.00 P.M.
                </div>

                <button type="submit" class="btn btn-reserve" name="reserve">Reserve</button>
            </form>
        </div>
    </main>

    <footer>
    </footer>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Payment section toggle logic
            const yesRadio = document.getElementById("cc_yes");
            const noRadio = document.getElementById("cc_no");
            const paymentDetails = document.getElementById("payment-details");
            const cancelWarning = document.getElementById("cancel-warning");
            const errorNotification = document.getElementById("errorNotification");
            const successNotification = document.getElementById("successNotification");

            function togglePaymentAccess() {
                if (yesRadio.checked) {
                    paymentDetails.style.display = "block";
                    cancelWarning.style.display = "none";
                    document.getElementById("card_number").required = true;
                    document.getElementById("expiry").required = true;
                    document.getElementById("cvv").required = true;
                } else if (noRadio.checked) {
                    paymentDetails.style.display = "none";
                    cancelWarning.style.display = "block";
                    document.getElementById("card_number").required = false;
                    document.getElementById("expiry").required = false;
                    document.getElementById("cvv").required = false;
                }
            }

            // Initialize based on current selection
            if (yesRadio && yesRadio.checked) {
                paymentDetails.style.display = "block";
                cancelWarning.style.display = "none";
            } else if (noRadio && noRadio.checked) {
                paymentDetails.style.display = "none";
                cancelWarning.style.display = "block";
            }

            if (yesRadio) yesRadio.addEventListener("change", togglePaymentAccess);
            if (noRadio) noRadio.addEventListener("change", togglePaymentAccess);

            // Show notifications
            if(errorNotification) {
                errorNotification.style.display = 'block';
                setTimeout(() => { errorNotification.style.display = 'none'; }, 3500);
            }
            
            if(successNotification) {
                successNotification.style.display = 'block';
                setTimeout(() => { successNotification.style.display = 'none'; }, 3500);
            }

            // Clear form if submitted successfully
            <?php if($form_submitted): ?>
                document.querySelector('form').reset();
            <?php endif; ?>
        });
    </script>
</body>
</html>