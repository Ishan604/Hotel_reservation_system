<?php
session_start();

if (isset($_SESSION['location'])) {
    $location = $_SESSION['location'];
    $checkIn = $_SESSION['checkIn'];
    $checkOut = $_SESSION['checkOut'];
    $adults = isset($_SESSION['adults']) ? $_SESSION['adults'] : 0;
    $children = isset($_SESSION['children']) ? $_SESSION['children'] : 0;
    $rooms = $_SESSION['rooms'];
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_reservation_system";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnreserve'])) 
{
    $roomNo = $_POST['roomno'];

    // Check if room is available in hotel_id = 2
    $query = "SELECT is_available FROM rooms WHERE room_no = '$roomNo' AND hotel_id = 2";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) 
    {
        $row = mysqli_fetch_assoc($result);
        if ($row['is_available'] == 1) 
        {
            $_SESSION['Room_type'] = $_POST['roomtype'];
            $_SESSION['Room_No'] = $_POST['roomno'];
            $_SESSION['selectApartment'] = $_POST['selectApartment'];
            $_SESSION['Room_name'] = $_POST['roomname'];
            $_SESSION['hotel_id'] = 2;

            $capcity = $adults + $children;
            $_SESSION["capacity"] = $capcity;
            header("Location: reservation.php");
            exit();
        } 
        else 
        {
            $error = "This room is already reserved. Please select another room.";
        }
    } 
    else 
    {
        // Room not found under hotel_id = 2 - treat as available
        $_SESSION['Room_type'] = $_POST['roomtype'];
        $_SESSION['Room_No'] = $roomNo;
        $_SESSION['selectApartment'] = $_POST['selectApartment'];
        $_SESSION['hotel_id'] = 2;

        $capcity = $adults + $children;
        $_SESSION["capacity"] = $capcity;
        header("Location: reservation.php");
        exit();
    }
}

// Get availability for rooms in hotel_id = 2
$roomAvailability = [];
if ($conn) 
{
    $query = "SELECT room_no, is_available FROM rooms WHERE hotel_id = 2";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) 
    {
        $roomAvailability[$row['room_no']] = $row['is_available'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Room Search Results | The Crown Stays</title>
  <link rel="stylesheet" href="Style_files/room1_design.css">
  <style>
    .reserve-btn {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
    }
    .reserve-btn:hover {
      background-color: #0056b3;
    }
    .reserve-btn.disabled {
      background-color: #cccccc;
      cursor: not-allowed;
    }
    .room-not-available {
      color: red;
      font-weight: bold;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <header class="hero-section">
    <div class="navbar">
      <div class="container nav-container">
        <div class="logo">
          <span>The</span><span class="highlight">Crown</span><span>Stays</span>
        </div>
        <ul class="nav-links">
          <li><a href="index.html">Home</a></li>
          <li><a href="#">About</a></li>
          <li><a href="#">Services</a></li>
          <li><a href="#">Contact</a></li>
          <li><a href="signin.php">Sign In</a></li>
          <li><a href="registration.php">Log In</a></li>
        </ul>
      </div>
    </div>
    <div class="hero-content">
      <h1>Check It & Buy It</h1>
      <p>Rooms matching your search criteria</p>
    </div>
  </header>

  <!-- Booking Form -->
  <section class="main-booking-section container">
    <form id="bookingForm" class="main-booking-form" action="room_page.php" method="POST">
      <div class="input-group location-input">
        <img src="img/icons/bed.png" alt="Location Icon" class="input-icon" />
        <input type="text" name="location" placeholder="Location" value="<?php echo $location?>" required />
        <span class="clear-input">✖</span>
      </div>
      
      <div class="input-group date-input">
        <img src="img/icons/calender.png" alt="Calendar Icon" class="input-icon" />
        <input type="date" name="checkIn" value="<?php echo $checkIn ?>" required />
        <span>—</span>
        <input type="date" name="checkOut" value="<?php echo $checkOut ?>" required />
      </div>

      <!-- Separate Dropdown for Adults -->
      <div class="input-group guests-input">
        <img src="img/icons/user.png" alt="Person Icon" class="input-icon" />
        <select name="adults" required>
          <option value="1" <?php echo ($adults == 1) ? 'selected' : ''; ?>>Adult 1</option>
          <option value="2" <?php echo ($adults == 2) ? 'selected' : ''; ?>>Adult 2</option>
          <option value="3" <?php echo ($adults == 3) ? 'selected' : ''; ?>>Adult 3</option>
          <option value="4" <?php echo ($adults == 4) ? 'selected' : ''; ?>>Adult 4</option>
          <option value="5" <?php echo ($adults == 5) ? 'selected' : ''; ?>>Adult 5</option>
        </select>
      </div>
  
      <!-- Separate Dropdown for Children -->
      <div class="input-group guests-input">
        <img src="img/icons/user.png" alt="Person Icon" class="input-icon" />
        <select name="children" required>
          <option value="0" <?php echo ($children == 0) ? 'selected' : ''; ?>>Children 0</option>
          <option value="1" <?php echo ($children == 1) ? 'selected' : ''; ?>>Children 1</option>
          <option value="2" <?php echo ($children == 2) ? 'selected' : ''; ?>>Children 2</option>
          <option value="3" <?php echo ($children == 3) ? 'selected' : ''; ?>>Children 3</option>
          <option value="4" <?php echo ($children == 4) ? 'selected' : ''; ?>>Children 4</option>
        </select>
      </div>
  
      <!-- Separate Dropdown for Rooms -->
      <div class="input-group guests-input">
        <img src="img/icons/dropdown.png" alt="Dropdown Arrow" class="input-icon" />
        <select name="rooms" required>
          <option value="1" <?php echo ($rooms == 1) ? 'selected' : ''; ?>>1 Room</option>
          <option value="2" <?php echo ($rooms == 2) ? 'selected' : ''; ?>>2 Rooms</option>
          <option value="3" <?php echo ($rooms == 3) ? 'selected' : ''; ?>>3 Rooms</option>
          <option value="4" <?php echo ($rooms == 4) ? 'selected' : ''; ?>>4 Rooms</option>
        </select>
      </div>
  
      <button type="submit" class="btn search-btn" name="search">Search</button>
    </form>
  </section>

  <!-- Apartment Overview Section -->
  <section class="apartment-overview container">
    <!-- Photo Gallery -->
    <div class="photo-gallery">
      <h2>Villa Pear Breez</h2>
      <h6 style="color: blue;">No. 15, Lighthouse Street, Fort, Galle 80000, Sri Lanka.</h6>
      <div class="photo-grid">
        <img src="img/galle_room2/room1.jpg" alt="Room Image" />
        <img src="img/galle_room2/room2.jpg" alt="Room Image" />
        <img src="img/galle_room2/room3.jpg" alt="Room Image" />
        <img src="img/galle_room2/room4.jpg" alt="Room Image" />
        <img src="img/galle_room2/room5.jpg" alt="Room Image" />
        <img src="img/galle_room2/room6.jpg" alt="Room Image" />
        <img src="img/galle_room2/room7.jpg" alt="Room Image" />
        <img src="img/galle_room2/room8.jpg" alt="Room Image" />
        <!-- Add more images here as needed -->
      </div>
    </div>

    <!-- About This Property -->
    <div class="about-property">
      <h2>About this property</h2>
      <p>Villa Pearl Breez is located in the scenic city of Galle, offering convenient access to popular attractions and the golden coastline. This charming apartment is thoughtfully designed to provide comfort and relaxation for every guest.</p>
      <p>With a spacious living area, a fully equipped kitchen, and cozy bedrooms, Villa Pearl Breez is perfect for both short-term getaways and extended stays. It offers a tranquil environment along with all the modern amenities needed for a memorable and comfortable experience.</p>
    </div>

    <!-- Facilities Section -->
    <div class="facilities">
      <h2>Facilities</h2><br>
      <div class="facility-category">
        <h3>General</h3>
        <ul>
          <li><img src="img/icons/wifi.png" alt="Wi-Fi" /> Free Wi-Fi</li>
          <li><img src="img/icons/ac.png" alt="Air Conditioning" /> Air Conditioning</li>
          <li><img src="img/icons/tv.png" alt="Television" /> Cable TV</li>
        </ul>
      </div>
      <div class="facility-category">
        <h3>Kitchen</h3>
        <ul>
          <li><img src="img/icons/fridge.png" alt="Fridge" /> Refrigerator</li>
          <li><img src="img/icons/oven.png" alt="Microwave" /> Microwave</li>
          <li><img src="img/icons/cofee.png" alt="Coffee Maker" /> Coffee Maker</li>
        </ul>
      </div>
      <div class="facility-category">
        <h3>Bathroom</h3>
        <ul>
          <li><img src="img/icons/shower.png" alt="Shower" /> Shower</li>
          <li><img src="img/icons/toilet.png" alt="Toilet" /> Toilet</li>
        </ul>
      </div>
    </div>
  </section>

   <!-- Apartment Details Section -->
   <h2 style="padding-left: 20px;">Availability</h2><br>
<section class="apartment-details container">
  <section class="main-booking-section container">
    <!--Form-->
    <form id="bookingForm" class="main-booking-form" action="room_page.php" method="POST">
      <div class="input-group date-input">
        <img src="img/icons/calender.png" alt="Calendar Icon" class="input-icon" />
        <input type="date" name="checkIn" value="<?php echo $checkIn?>" required />
        <span>—</span>
        <input type="date" name="checkOut" value="<?php echo $checkOut?>" required />
      </div>
  
      <!-- Separate Dropdown for Adults -->
      <div class="input-group guests-input">
        <img src="img/icons/user.png" alt="Person Icon" class="input-icon" />
        <select name="adults" required>
          <option value="1" <?php echo ($_SESSION['adults'] == 1) ? 'selected' : ''; ?>>Adult 1</option>
          <option value="2" <?php echo ($_SESSION['adults'] == 2) ? 'selected' : ''; ?>>Adult 2</option>
          <option value="3" <?php echo ($_SESSION['adults'] == 3) ? 'selected' : ''; ?>>Adult 3</option>
          <option value="4" <?php echo ($_SESSION['adults'] == 4) ? 'selected' : ''; ?>>Adult 4</option>
          <option value="5" <?php echo ($_SESSION['adults'] == 5) ? 'selected' : ''; ?>>Adult 5</option>
        </select>
      </div>
  
      <!-- Separate Dropdown for Children -->
      <div class="input-group guests-input">
        <img src="img/icons/user.png" alt="Person Icon" class="input-icon" />
        <select name="children" required>
          <option value="0" <?php echo ($_SESSION['children'] == 0) ? 'selected' : ''; ?>>Children 0</option>
          <option value="1" <?php echo ($_SESSION['children'] == 1) ? 'selected' : ''; ?>>Children 1</option>
          <option value="2" <?php echo ($_SESSION['children'] == 2) ? 'selected' : ''; ?>>Children 2</option>
          <option value="3" <?php echo ($_SESSION['children'] == 3) ? 'selected' : ''; ?>>Children 3</option>
          <option value="4" <?php echo ($_SESSION['children'] == 4) ? 'selected' : ''; ?>>Children 4</option>
        </select>
      </div>
  
      <!-- Separate Dropdown for Rooms -->
      <div class="input-group guests-input">
        <img src="img/icons/dropdown.png" alt="Dropdown Arrow" class="input-icon" />
        <select name="rooms" required>
          <option value="1" <?php echo ($_SESSION['rooms'] == 1) ? 'selected' : ''; ?>>1 Room</option>
          <option value="2" <?php echo ($_SESSION['rooms'] == 2) ? 'selected' : ''; ?>>2 Rooms</option>
          <option value="3" <?php echo ($_SESSION['rooms'] == 3) ? 'selected' : ''; ?>>3 Rooms</option>
          <option value="4" <?php echo ($_SESSION['rooms'] == 4) ? 'selected' : ''; ?>>4 Rooms</option>
        </select>
      </div>
  
      <button type="submit" class="btn search-btn" name="search">Search</button>
    </form>
  </section>  

  <!-- Table for Apartment Information -->
  <form method="post" action="">
    <table class="room-info-table">
        <tr>
          <td><strong>Apartment Type</strong></td>
          <td>
          <input type="hidden" name="roomname" value="Apartment with Terrace">  
          Apartment with Terrace
          </td>
        </tr>
        <tr>
          <td><strong>Number of Guests</strong></td>
          <td><?php echo $adults; ?> adults, <?php echo $children; ?> children</td>
        </tr>
        <tr>
          <td><strong>Room Type</strong></td>
          <td>
            <input type="hidden" name="roomtype" value="Single Room">
            Single Room
          </td>
        </tr>
        <tr>
          <td><strong>Room Number</strong></td>
          <td>
            <input type="hidden" name="roomno" value="Room 01">
            Room 01
          </td>
        </tr>
        <tr>
          <td><strong>Today's Price</strong></td>
          <td>2300 LKR + 300 LKR taxes and fees</td>
        </tr>
        <tr>
          <td><strong>Important Details</strong></td>
          <td>
            <ul>
              <li><img src="img/icons/mark.png" alt="Check" /> Continental breakfast included</li>
              <li><img src="img/icons/mark.png" alt="Check" /> 1 Single Large Bed</li>
              <li><img src="img/icons/mark.png" alt="Check" /> Free cancellation before <?php echo $checkIn; ?></li>
              <li><img src="img/icons/mark.png" alt="Check" /> No prepayment needed – pay at the property</li>
              <li><img src="img/icons/mark.png" alt="Check" /> Credit card details needed
                  <span style="color: red; padding-left: 10px; font-size:smaller">*or else the reservation will cancel at 7.00 P.M</span>
              </li>
              <li><img src="img/icons/mark.png" alt="Check" /> Genius discount may be available</li>
            </ul>
          </td>
        </tr>
        <tr>
          <td><strong>Select an Apartment</strong></td>
          <td>
            <select name="selectApartment" id="selectApartment">
              <option value="0">0</option>
              <option value="1">1</option>
            </select>
          </td>
          <td>
            <strong>Select an Apartment</strong>
          </td>
        </tr>
    </table>

    <div class="reserve-section">
      <?php 
      // Check if Room 01 exists in database and is reserved
      if (isset($roomAvailability['Room 01']) && $roomAvailability['Room 01'] == 0): ?>
        <button type="button" class="btn reserve-btn disabled" title="This room is already reserved" style="cursor: not-allowed;">I'll reserve</button>
        <div class="room-not-available">This room is already reserved</div>
      <?php else: ?>
        <button type="submit" class="btn reserve-btn" name="btnreserve" style="font-weight: bold;">I'll reserve</button>
      <?php endif; ?>
    </div>
  </form>

  <!--Room 2-->
  <form method="post" action="">
    <table class="room-info-table">
        <tr>
          <td><strong>Apartment Type</strong></td>
          <td>
          <input type="hidden" name="roomname" value="Apartment with Terrace">  
          Apartment with Terrace
          </td>
        </tr>
        <tr>
          <td><strong>Number of Guests</strong></td>
          <td><?php echo $adults; ?> adults, <?php echo $children; ?> children</td>
        </tr>
        <tr>
          <td><strong>Room Type</strong></td>
          <td>
            <input type="hidden" name="roomtype" value="Family Room">
            Family Room
          </td>
        </tr>
        <tr>
          <td><strong>Room Number</strong></td>
          <td>
            <input type="hidden" name="roomno" value="Room 02">
            Room 02
          </td>
        </tr>
        <tr>
          <td><strong>Today's Price</strong></td>
          <td>3000 LKR + 300 LKR taxes and fees</td>
        </tr>
        <tr>
          <td><strong>Important Details</strong></td>
          <td>
            <ul>
              <li><img src="img/icons/mark.png" alt="Check" /> Continental breakfast included</li>
              <li><img src="img/icons/mark.png" alt="Check" /> 2 Normal Beds</li>
              <li><img src="img/icons/mark.png" alt="Check" /> Free cancellation before <?php echo $checkIn; ?></li>
              <li><img src="img/icons/mark.png" alt="Check" /> No prepayment needed – pay at the property</li>
              <li><img src="img/icons/mark.png" alt="Check" /> Credit card details needed
                  <span style="color: red; padding-left: 10px; font-size:smaller">*or else the reservation will cancel at 7.00 P.M</span>
              </li>
              <li><img src="img/icons/mark.png" alt="Check" /> Genius discount may be available</li>
            </ul>
          </td>
        </tr>
        <tr>
          <td><strong>Select an Apartment</strong></td>
          <td>
            <select name="selectApartment" id="selectApartment">
              <option value="0">0</option>
              <option value="1">1</option>
            </select>
          </td>
        </tr>
    </table>

    <div class="reserve-section">
      <?php 
      // Check if Room 02 exists in database and is reserved
      if (isset($roomAvailability['Room 02']) && $roomAvailability['Room 02'] == 0): ?>
        <button type="button" class="btn reserve-btn disabled" title="This room is already reserved" style="cursor: not-allowed;">I'll reserve</button>
        <div class="room-not-available">This room is already reserved</div>
      <?php else: ?>
        <button type="submit" class="btn reserve-btn" name="btnreserve" style="font-weight: bold;">I'll reserve</button>
      <?php endif; ?>
    </div>
  </form>

  <!--Room 3-->
  <form method="post" action="">
    <table class="room-info-table">
        <tr>
          <td><strong>Apartment Type</strong></td>
          <td>
          <input type="hidden" name="roomname" value="Apartment with Terrace">  
          Apartment with Terrace
          </td>
        </tr>
        <tr>
          <td><strong>Number of Guests</strong></td>
          <td><?php echo $adults; ?> adults, <?php echo $children; ?> children</td>
        </tr>
        <tr>
          <td><strong>Room Type</strong></td>
          <td>
            <input type="hidden" name="roomtype" value="Double Room">
            Double Room
          </td>
        </tr>
        <tr>
          <td><strong>Room Number</strong></td>
          <td>
            <input type="hidden" name="roomno" value="Room 03">
            Room 03
          </td>
        </tr>
        <tr>
          <td><strong>Today's Price</strong></td>
          <td>4000 LKR + 500 LKR taxes and fees</td>
        </tr>
        <tr>
          <td><strong>Important Details</strong></td>
          <td>
            <ul>
              <li><img src="img/icons/mark.png" alt="Check" /> Continental breakfast included</li>
              <li><img src="img/icons/mark.png" alt="Check" /> 2 Large Beds</li>
              <li><img src="img/icons/mark.png" alt="Check" /> Free cancellation before <?php echo $checkIn; ?></li>
              <li><img src="img/icons/mark.png" alt="Check" /> No prepayment needed – pay at the property</li>
              <li><img src="img/icons/mark.png" alt="Check" /> Credit card details needed
                  <span style="color: red; padding-left: 10px; font-size:smaller">*or else the reservation will cancel at 7.00 P.M</span>
              </li>
              <li><img src="img/icons/mark.png" alt="Check" /> Genius discount may be available</li>
            </ul>
          </td>
        </tr>
        <tr>
          <td><strong>Select an Apartment</strong></td>
          <td>
            <select name="selectApartment" id="selectApartment">
              <option value="0">0</option>
              <option value="1">1</option>
            </select>
          </td>
        </tr>
    </table>

    <div class="reserve-section">
      <?php 
      // Check if Room 03 exists in database and is reserved
      if (isset($roomAvailability['Room 03']) && $roomAvailability['Room 03'] == 0): ?>
        <button type="button" class="btn reserve-btn disabled" title="This room is already reserved" style="cursor: not-allowed;">I'll reserve</button>
        <div class="room-not-available">This room is already reserved</div>
      <?php else: ?>
        <button type="submit" class="btn reserve-btn" name="btnreserve" style="font-weight: bold;">I'll reserve</button>
      <?php endif; ?>
    </div>
  </form>

  <style>
    .reserve-btn {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
    }
    .reserve-btn:hover {
      background-color: #0056b3;
    }
  </style>
  <p class="reserve-note" style="color: green; font-weight:bold">*You won't be charged yet</p>
</section>

  <script src="room_page.js"></script>
</body>
</html>