<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

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
     *     @OA\Response(
     *          response=400,
     *          description="Bad request.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="errors",
     *                          type="array",
     *                          description="List of errors.",
     *                          @OA\Items(type="string")
     *                      ),
     *                      example={
     *                          "errors": {
     *                              "The name field is required.",
     *                              "The email field is required.",
     *                              "The message field is required.",
     *                              "The email must be a valid email address.",
     *                          }
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
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that throws the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
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
        $validator = Validator::make($request->json()->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return failValidation($validator);
        } else {
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
            return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
        }
    }

    public static function sendMailScrapers($date, $msg, $subject)
    {
        try {
            $user = Auth::user();
            $args = array("name"=>$user->name, "mail"=>$user->email, "body"=>$msg);
        } catch (Throwable $e) {
            $args = array("name"=>'Scraper', "mail"=>'scraper@easytravel.com', "body"=>$msg);
        }

        $to_name = 'EasyTravel';
        $to_email = 'easytraveluem@gmail.com';
        // $data=array("name"=>$request->name, "mail"=>$request->email, "body"=>$request->message);
        Mail::send('mail', $args, function($message) use ($subject, $to_name, $to_email){
            $message -> to($to_email)
                ->subject($subject);
        });

        if (Mail::failures()) {
            return false;
        }
        return true;
    }

    public static function emailErrors($validator, $dateStr, $message){
        $errorsStr = "";
        $arrayErrors = $validator->getData();
        foreach ($arrayErrors as $errors){
            foreach ($errors as $error) {
                foreach ($error as $err) {
                    $errorsStr = $errorsStr . substr_replace($err, "", -1) . "; ";
                }
            }
        }
        MailController::sendMailScrapers($dateStr, $message.substr_replace($errorsStr, "", -2)."."
            ,'Scraper failed');
    }
}
