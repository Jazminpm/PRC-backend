<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Send mail function
     *
     */
    public function sendMail()
    {
        $name = 'User name';
        $email = 'pruebaLaravel@gmail.com';
        $content = 'Este es el mensaje del correo, usando controlador y variables.';

        $to_name = 'EasyTravel';
        $to_email = 'easytraveluem@gmail.com';
        $data=array("name"=>$name, "mail"=>$email, "body"=>$content);
        Mail::send('mail', $data, function($message) use ($to_name, $to_email){
            $message -> to($to_email)
                ->subject('Contact form message');
        });

        if (Mail::failures()) {
            // return with failed message
            echo('Oops, an error has ocurred...');
        }
        // return with success message
        echo('Mail has been sent correctly!');
    }
}
