<?php

/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 24/08/16
 * Time: 20:14
 */
class UamEmail
{
    //Called to send the validation token
    public static function sendValidationTokenEmail($email, $token, $message)
    {
        $f3 = \Base::instance();
        $subject = $f3->site . " - Email Verificaton";
        $txt = "You received this email because you have requested to " . $message . " at " . $f3->site . "\n Please click the link below to verify it\nhttp://" . $f3->domain . "/" . $f3->emailverificationroute . "?email=" . $email . "&token=" . $token;
        $headers = "From: " . $f3->email;
        mail($email, $subject, $txt, $headers);
    }
}