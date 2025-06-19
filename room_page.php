<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    if(isset($_POST['search']) || isset($_POST['book'])) 
    {
        // $location = $_POST['location'];
        // $checkIn = $_POST['checkIn'];
        // $checkOut = $_POST['checkOut'];
        // $adults = $_POST['adults'];
        // $children = $_POST['children'];
        // $rooms = $_POST['rooms'];
        $_SESSION['location'] = $_POST['location'];
        $_SESSION['checkIn'] = $_POST['checkIn'];
        $_SESSION['checkOut'] = $_POST['checkOut'];
        $_SESSION['adults'] = $_POST['adults'];
        $_SESSION['children'] = $_POST['children'];
        $_SESSION['rooms'] = $_POST['rooms'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Room Search Results | The Crown Stays</title>
  <link rel="stylesheet" href="Style_files/room_page_style.css?v=1">
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
          <li><a href="#">Sign In</a></li>
          <li><a href="#">Log In</a></li>
        </ul>
      </div>
    </div>
    <div class="hero-content">
      <h1>Find the Perfect Room for Your Stay</h1>
      <p>Rooms matching your search criteria</p>
    </div>
  </header>

  <!-- Booking Form -->
  <section class="main-booking-section container">
    <form id="bookingForm" class="main-booking-form" action="room_page.php" method="POST">
      <div class="input-group location-input">
        <img src="img/icons/bed.png" alt="Location Icon" class="input-icon" />
        <input type="text" name="location" placeholder="Location" value="<?php echo $_SESSION['location']?>" required />
        <span class="clear-input">✖</span>
      </div>
      
      <div class="input-group date-input">
        <img src="img/icons/calender.png" alt="Calendar Icon" class="input-icon" />
        <input type="date" name="checkIn" value="<?php echo $_SESSION['checkIn'] ?>" required />
        <span>—</span>
        <input type="date" name="checkOut" value="<?php echo $_SESSION['checkOut'] ?>" required />
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

  <!-- Available Rooms Section -->
  <section class="room-results container">
    <h2>Available Rooms in <?php echo $_SESSION['location'] ?></h2>

    <!-- Room 1 -->
    <div class="room-card">
      <img src="img/galle_room1/room1.jpg" alt="Room Image" />
      <div class="room-info">
        <a href="room_page.php" style="text-decoration: none;">
          <h3>Princely House Apartment</h3>
        </a>
        <p class="review-rating">Wonderful ★★★★★</p>
        <p>Entire apartment • 1 bedroom • 1 living room • 1 bathroom • 1 kitchen</p>
        <p>Size: 15 m²</p>
        <p class="highlight">300 m from beach</p>
        <div class="price-section">
          <p><strong>Price:</strong> US$27/night</p>
          <p><strong>Breakfast included</strong></p>
          <p><strong>Free cancellation</strong></p>
        </div>
        <p class="highlight">1 night, 1 adult US$27 taxes and fees</p> 
        <a href="room1_details.php" class="btn search-btn">See availability</a> 
      </div>
    </div>

    <!-- Room 2 -->
    <div class="room-card">
      <img src="img/galle_room2/room1.jpg" alt="Room Image" />
      <div class="room-info">
        <a href="room_page.php" style="text-decoration: none;">
          <h3>Villa Pearl Breez</h3>
        </a>
        <p class="review-rating">Superb ★★★★★</p>
        <p>Entire suite • 2 bedrooms • 1 living room • 2 bathrooms • 1 kitchen</p>
        <p>Size: 25 m²</p>
        <p class="highlight">500 m from beach </p>
        <div class="price-section">
          <p><strong>Price:</strong> US$40/night</p>
          <p><strong>Breakfast included</strong></p>
          <p><strong>Free cancellation</strong></p>
        </div>
        <p class="highlight">1 night, 1 adult US$40 taxes and fees</p> 
        <a href="room2_details.php" class="btn search-btn">See availability</a> 
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container footer-container">
      <div class="footer-col">
        <h3>Vacation Rental</h3>
        <p>A small river named Duden flows by their place and supplies it with the necessary regelialia.</p>
        <a href="#" class="read-more">Read more &rarr;</a>
      </div>
      <div class="footer-col">
        <h3>Services</h3>
        <ul>
          <li><a href="#">Map Direction</a></li>
          <li><a href="#">Accommodation Services</a></li>
          <li><a href="#">Great Experience</a></li>
          <li><a href="#">Perfect central location</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>Tag cloud</h3>
        <div class="tag-cloud">
          <span>APARTMENT</span><span>VACATION</span><span>RENTAL</span><span>HOUSE</span>
        </div>
      </div>
      <div class="footer-col">
        <h3>Subscribe</h3>
        <form class="subscribe-form">
          <input type="email" placeholder="Enter email address" required />
          <button type="submit" class="btn primary-btn subscribe-btn">&#9993;</button>
        </form>
        <div class="social-icons">
          <a href="#"><img src="img/icons/twitter.png" alt="Twitter" /></a>
          <a href="#"><img src="img/icons/fb.png" alt="Facebook" /></a>
          <a href="#"><img src="img/icons/insta.png" alt="Instagram" /></a>
        </div>
      </div>
    </div>
  </footer>

  <script src="room_page.js"></script>
</body>
</html>
