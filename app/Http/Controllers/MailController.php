<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Send mail function
     *
     */
    public function sendMail(Request $request)
    {
        //$name = 'User name';
        //$email = 'pruebaLaravel@gmail.com';
        //$content = 'Este es el mensaje del correo, usando controlador y variables.';

        $to_name = 'EasyTravel';
        $to_email = 'easytraveluem@gmail.com';
        $data=array("name"=>$request->name, "mail"=>$request->email, "body"=>$request->message);
        Mail::send('mail', $data, function($message) use ($to_name, $to_email){
            $message -> to($to_email)
                ->subject('Contact form message');
        });

        if (Mail::failures()) {
            // return with failed message
            return response()->json(["error" => "Unable to send the message"], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        // return with success message
        return response()->json(["msg" => "Email sent correctly"], JsonResponse::HTTP_OK);
    }
}
