<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use File;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
// use Intervention\Image\Facades\Image as Image;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

trait emaiTraits {



    public function sendEmail( $request, $subject){


        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = 'mail.tablebookings.co.uk'; // Specify main SMTP server
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'reservations@tablebookings.co.uk'; // SMTP username
            $mail->Password = 'uy!5a8Y47'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable SSL encryption
            $mail->Port = 465; // TCP port for SSL

            // Disable SSL certificate verification (for testing purposes only)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom('reservations@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($request->email, 'Recipient Name');

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = view('email_template.account_activataion_template', compact('request'))->render();
            //$mail->AltBody = 'This is the plain text version of the email body.';

            // Send the email
            if ($mail->send()) {
                echo 'Message has been sent';
            } else {
                echo 'Message could not be sent.';
            }

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }





    public function sendEmailForgetPassword( $request, $subject){


        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = 'mail.tablebookings.co.uk'; // Specify main SMTP server
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'reservations@tablebookings.co.uk'; // SMTP username
            $mail->Password = 'uy!5a8Y47'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable SSL encryption
            $mail->Port = 465; // TCP port for SSL

            // Disable SSL certificate verification (for testing purposes only)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom('reservations@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($request->email, 'Recipient Name');
            $otp = rand(100000, 999999);
            Cache::put($request->email, $otp, Carbon::now()->addMinutes(5));
            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = view('email_template.forget_password_otp', compact('request','otp'))->render();
            //$mail->AltBody = 'This is the plain text version of the email body.';

            // Send the email
            if ($mail->send()) {
                echo 'Message has been sent';
            } else {
                echo 'Message could not be sent.';
            }

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }
}
