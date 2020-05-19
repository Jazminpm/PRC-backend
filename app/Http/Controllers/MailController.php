<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{

    /**
     * @OA\Post(
     *      path="/api/send-mail",
     *      operationId="sendMail",
     *      tags={"mail"},
     *      summary="Send a mail",
     *      description="After filling the contact form, it sends an email to our group Gmail account",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="name and surname"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="personal mail"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      description="The message you want to send on the mail"
     *                  ),
     *                  example={{"name": "Name Surname", "email": "test@mail.com", "message": "Test message"}}
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="output",
     *                          type="string",
     *                          description="Email sent correctly"
     *                      ),
     *                      example={
     *                          "msg": "Email sent correctly"
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      example={
     *                          "error": "Unable to send the message"
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */

    /*
     * Send mail function
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

    public static function sendMailScrapers($date, $msg)
    {
        $user = Auth::user();
        $args = array("name"=>$user->name, "mail"=>$user->email, "body"=>$msg);
        // dd($args);
        $to_name = 'EasyTravel';
        $to_email = 'easytraveluem@gmail.com';
        // $data=array("name"=>$request->name, "mail"=>$request->email, "body"=>$request->message);
        Mail::send('mail', $args, function($message) use ($to_name, $to_email){
            $message -> to($to_email)
                ->subject('Scraper finished');
        });

        if (Mail::failures()) {
            return false;
        }
        return true;
    }
}
