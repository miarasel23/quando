<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use File;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
// use Intervention\Image\Facades\Image as Image;

trait emaiTraits {


    public function sendEmail(Request $request, $subject){

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.tablebookings.co.uk'; //  smtp host
    $mail->SMTPAuth = true;
    $mail->Username = 'reservations@tablebookings.co.uk'; // sender username
    $mail->Password = '1C4yb1s$8'; // sender password
    $mail->SMTPSecure = 'tls';  // encryption - ssl/tls
    $mail->Port = 587; // port - 587/465

    $mail->setfrom('reservations@tablebookings.co.uk', 'reservations@tablebookings.co.uk');
    $mail->addaddress('rasel.chefonline@gmail.com','rasel.chefonline@gmail.com');
    //$mail->addCC($request->emailCc);
    //$mail->addbcc('hello@salikandco.com');

    $mail->isHTML(true); // Set email content format to HTML
    // $body = view('email_template.enquiries_view', compact('request'))->render();
    $body = 'This is the HTML message body <b>in bold!</b>';
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = 'plain text version of email body';
    $mail->send();
    return true;

    }
}
