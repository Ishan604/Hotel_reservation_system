<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$log_file = 'C:\xampp\htdocs\Hotel_Reservation_System\logfile2.txt'; // Change this path to a valid log file path

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

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hotel_reservation_system";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) 
    {
        log_message("Connection error! " . mysqli_connect_error($conn));
    }
    else
    {
        $current_date = date("Y-m-d"); // Get current date in Y-m-d format
        $current_time = date("H:i"); // Get current time in H:i format

        if($current_time) // Check if current time is 18:00
        {
            $query = "SELECT customer_email, reservation_id FROM reservations WHERE status='pending' AND check_in_date='$current_date'";
            $result = mysqli_query($conn, $query);

            if(mysqli_num_rows($result) > 0)
            {
                while($row = mysqli_fetch_assoc($result))
                {
                    $email = $row['customer_email'];
                    $reservation_id = $row['reservation_id'];

                    $mail->addAddress($email); // Add a recipient
                    $mail->Subject = 'Reminder: Confirm Your Reservation'; // Set email subject
                    $mail->Body    = "
Dear Valued Customer,

Thank you for choosing us! This is a friendly reminder about your upcoming reservation for today.

To ensure everything is ready for you, please confirm your reservation at your earliest convenience. If you need to make any changes or cancel, please let us know.

Need help? Reply to this email or contact us at ishanpathirana122133@gmail.com or 0771023456.

We look forward to serving you!

Best regards,
The Crwon Stays";
                    
                    // Send the email
                    if(!$mail->send())
                    {
                        log_message("Message could not be sent to {$email}. Mailer Error: {$mail->ErrorInfo}");
                    }
                    else
                    {
                        log_message("Message has been sent to {$email} successfully.");
                    }
                    
                    // Clear all recipients for the next iteration
                    $mail->clearAddresses();
                }
            }
            else
            {
                log_message("No pending reservations found.");
            }
        }
        else
        {
            log_message("Current time is not 18:00. No emails sent.");
        }
    }
}
catch(Exception $e)
{
    // Handle error
    log_message("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
}
finally 
{
    // Close the database connection
    mysqli_close($conn);
    log_message("Script finished.");
}

?>
