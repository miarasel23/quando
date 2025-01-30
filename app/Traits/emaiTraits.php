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



    protected $emailUserName = 'tablebookings';
    protected $emailPassword = '4sH6JjNHhBVk3!m7x33';

    public function sendEmail( $request, $subject){


        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            // $mail->Host = 'email-smtp.eu-west-2.amazonaws.com';
            // $mail->SMTPAuth = true;
            // $mail->Username = 'AKIA4FDBYMQAVBBEBPPE';
            // $mail->Password = 'BEBqcbr8IM7BPtlFeGYHFpUCXwC580dlUJ8MVHdYNvo2';
            // $mail->SMTPSecure = 'tls'; // or 'STARTTLS'
            // $mail->Port = 587;


            $mail->Host = 'outbound.mailhop.org';
            $mail->SMTPAuth = true;
            $mail->Username =$this->emailUserName;
            $mail->Password = $this->emailPassword;
            $mail->SMTPSecure = 'tls'; // or 'STARTTLS'
            $mail->Port = 587;
           $mail->SMTPDebug = 0; // For debugging, remove in production

            $mail->setFrom('noreply@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($request->email, 'Recipient Name');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = view('email_template.account_activataion_template', compact('request'))->render();

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }





    public function sendEmailForgetPassword($request, $subject) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'outbound.mailhop.org';
            $mail->SMTPAuth = true;
            $mail->Username =$this->emailUserName;
            $mail->Password = $this->emailPassword;
            $mail->SMTPSecure = 'tls'; // or 'STARTTLS'
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // For debugging, remove in production

            $mail->setFrom('noreply@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($request->email, 'Recipient Name');


            // Generate OTP and store in cache
            $otp = rand(100000, 999999);
            Cache::put($request->email, $otp, Carbon::now()->addMinutes(5));

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = view('email_template.forget_password_otp', compact('request', 'otp'))->render();

            // Send the email
            $mail->send();  // No echo, just sending the email quietly

        } catch (Exception $e) {
            // Log the error instead of echoing it
            \Log::error("Mail error: {$mail->ErrorInfo}");
        }

    }
    public function sendEmailForReservation($reservation, $subject,$one_time_password) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'outbound.mailhop.org';
            $mail->SMTPAuth = true;
            $mail->Username =$this->emailUserName;
            $mail->Password = $this->emailPassword;
            $mail->SMTPSecure = 'tls'; // or 'STARTTLS'
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // For debugging, remove in production





            $mail->setFrom('reservations@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($reservation->guest_information->email, 'Recipient Name');
            $mail->isHTML(true);

            $mail->Subject = $subject;

            $mail->Body = view('reservation.confirmation_email', compact('reservation','one_time_password'))->render();
            $mail->send();


        } catch (Exception $e) {
            // Log the error instead of echoing it
            \Log::error("Mail error: {$mail->ErrorInfo}");
        }

    }
    public function sendEmailForEnquiry($data, $subject) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'outbound.mailhop.org';
            $mail->SMTPAuth = true;
            $mail->Username =$this->emailUserName;
            $mail->Password = $this->emailPassword;
            $mail->SMTPSecure = 'tls'; // or 'STARTTLS'
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // For debugging, remove in production
            $mail->setFrom('reservations@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($data->email, $data->first_name.' '.$data->last_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = view('email_template.enquiry', compact('data'))->render();
            $mail->send();
        } catch (Exception $e) {
            // Log the error instead of echoing it
            \Log::error("Mail error: {$mail->ErrorInfo}");
        }

    }


    public function sendEmailForReservationOwner($reservation, $subject) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'outbound.mailhop.org';
            $mail->SMTPAuth = true;
            $mail->Username =$this->emailUserName;
            $mail->Password = $this->emailPassword;
            $mail->SMTPSecure = 'tls'; // or 'STARTTLS'
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // For debugging, remove in production

            $mail->setFrom('reservations@tablebookings.co.uk', 'Table Bookings');
            $mail->addAddress($reservation->restaurant->email, 'Recipient Name');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = view('reservation.woner_confirmation_email', compact('reservation'))->render();
            $mail->send();


        } catch (Exception $e) {
            // Log the error instead of echoing it
            \Log::error("Mail error: {$mail->ErrorInfo}");
        }

    }
}
