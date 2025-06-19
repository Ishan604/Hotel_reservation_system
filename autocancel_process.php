<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Set up logging file
$log_file = 'C:\xampp\htdocs\Hotel_Reservation_System\logfile.txt'; // Change this path to a valid log file path

function log_message($message) 
{
    global $log_file;
    file_put_contents($log_file, date("Y-m-d H:i:s") . " - " . $message . PHP_EOL, FILE_APPEND);
}

$mail = new PHPMailer(true);

try 
{
    // Set the mailer to use SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to Gmail
    $mail->SMTPAuth = true;
    $mail->Username = 'ishanpathirana122133@gmail.com'; // Your Gmail address
    $mail->Password = 'esnk eksq wtth zsuv'; // Your Gmail password (consider using an app-specific password for security)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587; // TCP port to connect to

    // Set email parameters
    $mail->setFrom('ishanpathirana122133@gmail.com', 'The Crown Stays'); // Set the sender's email and name

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hotel_reservation_system";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) 
    {
        log_message("Connection failed: " . mysqli_connect_error());
    }

    // Get today's date
    $current_date = date("Y-m-d"); // Get today's date in Y-m-d format
    $current_time = date("H:i"); // Get current time in H:i format

    log_message("Script started at $current_time");
    log_message("Today's date is $current_date");

    if ($current_time) 
    {
        // Query to get reservations with 'pending' status, matching check-in date
        $query = "SELECT * FROM reservations WHERE status='pending' AND check_in_date='$current_date'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) 
        {
            // If there are any pending reservations without credit card details
            while ($row = mysqli_fetch_assoc($result)) 
            {
                $email = $row['customer_email'];
                $reservation_id = $row['reservation_id'];
                $customer_id = $row['customer_id'];
                
                //get room id of that exact customer
                $room_query = "SELECT room_id FROM rooms WHERE customer_id = '$customer_id'";
                $room_result = mysqli_query($conn, $room_query);

                if(mysqli_num_rows($room_result) > 0)
                {
                    $room_row = mysqli_fetch_assoc($room_result);
                    $room_id = $room_row['room_id'];

                    // Step 1: Delete the reservation from the reservations table
                    $delete_reservation_query = "DELETE FROM reservations WHERE reservation_id='$reservation_id'";
                    $result1 = mysqli_query($conn, $delete_reservation_query);
                    if ($result1) 
                    {
                        log_message("Reservation with ID {$reservation_id} has been deleted.");
                    } 
                    else 
                    {
                        log_message("Error deleting reservation: " . mysqli_error($conn));
                    }

                    // Step 2: Delete the room from the rooms table
                    $delete_room_query = "DELETE FROM rooms WHERE room_id='$room_id' AND customer_id='$customer_id'";
                    $result2 = mysqli_query($conn, $delete_room_query);
                    if ($result2) 
                    {
                        log_message("Room with ID {$room_id} has been deleted from the system.");
                    } 
                    else 
                    {
                        log_message("Error deleting room: " . mysqli_error($conn));
                    }

                    if ($result1 && $result2) 
                    {
                        // Step 3: Send cancellation email to the customer
                        $mail->addAddress($email);
                        $mail->Subject = 'Reservation Cancellation'; // Set email subject
                        $mail->Body = "Dear Customer,\n\nWe regret to inform you that your reservation with ID: {$reservation_id} has been canceled due to not adding your credit card details to the system.\n\nPlease ensure to update your payment details for future reservations.\n\nThank you.";

                        if (!$mail->send()) 
                        {
                            log_message("Message could not be sent to {$email}. Mailer Error: {$mail->ErrorInfo}");
                        } 
                        else 
                        {
                            log_message("Cancellation email has been sent to {$email} successfully.");
                        }

                        // Clear all recipients for the next iteration
                        $mail->clearAddresses();
                    }
                }
            }
        } 
        else 
        {
            log_message("No pending reservations without credit card details found for today.");
        }
    }

} catch (Exception $e) 
{
    log_message("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
} 
finally 
{
    // Close the database connection
    mysqli_close($conn);
    log_message("Script finished.");
}
?>
