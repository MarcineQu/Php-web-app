<?php

namespace PHPMailer\src\PHPMailer;

namespace PHPMailer\src\Exception;

//namespace PHPMailer\src\SMTP;


use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

//use PHPMailer\PHPMailer\Exception;
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\SMTP;


class M {

    public function send_email($address, $content) {

        try {
            $mail = new PHPMailer;
            $mail->isSMTP(); // Set mailer to use SMTP
            //$mail->Debugoutput;
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587; // Specify main and backup SMTP servers
            $mail->Username = 'garbaczmg@gmail.com'; // SMTP username
            $mail->Password = 'nivpmndfiunorlok'; // SMTP password
            // Enable encryption, 'ssl' also accepted
            $mail->From = 'garbaczmg@gmail.com';
            $mail->FromName = 'OTP source';
            $mail->addAddress($address); // Add a recipient
            $mail->WordWrap = 40; // Set word wrap to 40 characters
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Your security code';
            $mail->Body = 'This is your authentication code <B>' . $content . '</B>';
            $mail->AltBody = 'This is your authentication code ' . $content . '';
            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                echo 'Message has been sent';
            }
        } catch
        (Exception $e) {
            echo "Exception &nbsp" . $e->getMessage();
        }
    }

}
