<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedHttpException) {
            $preException = $exception->getPrevious();
            if ($preException instanceof TokenExpiredException) {
                // return Redirect::action('AuthController@refresh', $request, JsonResponse::HTTP_TEMPORARY_REDIRECT);
                return response()->json(['error' => 'Expired token.'], JsonResponse::HTTP_UNAUTHORIZED);
            } else if ($preException instanceof TokenInvalidException) {
                return response()->json(['error' => 'Invalid token.'], JsonResponse::HTTP_UNAUTHORIZED);
            } else if ($preException instanceof TokenBlacklistedException) {
                return response()->json(['error' => 'Blacklisted token.'], JsonResponse::HTTP_UNAUTHORIZED);
            } else {
                return response()->json(['error' => $exception->getMessage()]);
            }
        }
        return parent::render($request, $exception);
    }
}
