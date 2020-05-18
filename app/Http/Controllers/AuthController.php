<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{

    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="authRegister",
     *      tags={"auth"},
     *      summary="Create a new user.",
     *      description="Register a new user in database.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="User name."
     *                  ),
     *                  @OA\Property(
     *                      property="surnames",
     *                      type="string",
     *                      description="User surnames."
     *                  ),
     *                  @OA\Property(
     *                      property="dni",
     *                      type="string",
     *                      description="User id."
     *                  ),
     *                  @OA\Property(
     *                      property="phoneNumber",
     *                      type="string",
     *                      description="User phone number."
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email."
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password."
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      type="string",
     *                      description="User password confirmation."
     *                  ),
     *              @OA\Property(
     *                      property="role",
     *                      type="integer",
     *                      description="User role (default 2)"
     *                  ),
     *                  example={
     *                      "name": "Cient",
     *                      "surnames": "EasyTravel",
     *                      "dni": "00000000a",
     *                      "phoneNumber": "000000001",
     *                      "email": "client@easytravel.com",
     *                      "password": "client",
     *                      "password_confirmation": "client",
     *                      "role": 2
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Created.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="access_token",
     *                          type="string",
     *                          description="User name."
     *                      ),
     *                      @OA\Property(
     *                          property="token_type",
     *                          type="string",
     *                          description="User surnames."
     *                      ),
     *                      @OA\Property(
     *                          property="expires_in",
     *                          type="integer",
     *                          description="Alive time of the token in seconds. Default 3600s"
     *                      ),
     *                      example={
     *                              "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9yZWdpc3RlciIsImlhdCI6MTU4ODQ5Mjk5MSwiZXhwIjoxNTg4NDk2NTkxLCJuYmYiOjE1ODg0OTI5OTEsImp0aSI6ImRMWjNQcmN0Tm5UUEdtanMiLCJzdWIiOjUsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.mNh-Rfalspe1SBH_ltfz_ErIAhExJwDIA8td69fZvWA",
     *                              "token_type": "bearer",
     *                              "expires_in": 3600
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
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
     *                              "The dni field is required.",
     *                              "The name field is required.",
     *                              "The surnames must be a string.",
     *                              "The phoneNumber field is required.",
     *                              "The email field is required.",
     *                              "The password field is required.",
     *                              "The role field must be numeric.",
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => ['required', 'size:9', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            'surnames' => ['string', 'max:255', 'required'],
            'phoneNumber' => ['required', 'numeric', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',              // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!_%*#?&]/', // must contain a special character
                'confirmed'],
            'role' => ['required', 'digits:1', 'integer'],
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'surnames' => $request->get('surnames'),
            'dni' => $request->get('dni'),
            'email' => $request->get('email'),
            'phoneNumber' => $request->get('phoneNumber'),
            'role' => $request->get('role'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,], JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="authLogin",
     *      tags={"auth"},
     *      summary="User login.",
     *      description="Generate a JWT token with credentials.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="email",
     *                      type="string",
     *                      description="User email."
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string",
     *                      description="User password."
     *                  ),
     *                  example={
     *                      "email": "client@easytravel.com",
     *                      "password": "client",
     *                  }
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
     *                          property="access_token",
     *                          type="string",
     *                          description="User name."
     *                      ),
     *                      @OA\Property(
     *                          property="token_type",
     *                          type="string",
     *                          description="User surnames."
     *                      ),
     *                      @OA\Property(
     *                          property="expires_in",
     *                          type="integer",
     *                          description="Alive time of the token in seconds. Default 3600s"
     *                      ),
     *                      example={
     *                              "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9yZWdpc3RlciIsImlhdCI6MTU4ODQ5Mjk5MSwiZXhwIjoxNTg4NDk2NTkxLCJuYmYiOjE1ODg0OTI5OTEsImp0aSI6ImRMWjNQcmN0Tm5UUEdtanMiLCJzdWIiOjUsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.mNh-Rfalspe1SBH_ltfz_ErIAhExJwDIA8td69fZvWA",
     *                              "token_type": "bearer",
     *                              "expires_in": 3600
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
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
     *                              "The email field is required.",
     *                              "The password field is required.",
     *                          }
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized.",
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
     *                          "errors": "Invalid credentials.",
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
     *                          property="errors",
     *                          type="string",
     *                          description="Token error."
     *                      ),
     *                      example={
     *                          "errors": "Could not create access token.",
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     * @param Request $request
     * @return JsonResponse
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return failValidation($validator);
        }

        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['errors' => 'Invalid credentials.'], JsonResponse::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            return response()->json(['errors' => 'Could not create access token'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,],
            JsonResponse::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/refresh",
     *      operationId="authRefresh",
     *      tags={"auth"},
     *      summary="Refresh token.",
     *      description="Refresh JWT token.",
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="access_token",
     *                          type="string",
     *                          description="User name."
     *                      ),
     *                      @OA\Property(
     *                          property="token_type",
     *                          type="string",
     *                          description="User surnames."
     *                      ),
     *                      @OA\Property(
     *                          property="expires_in",
     *                          type="integer",
     *                          description="Alive time of the token in seconds. Default 3600s"
     *                      ),
     *                      example={
     *                              "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9hdXRoXC9yZWdpc3RlciIsImlhdCI6MTU4ODQ5Mjk5MSwiZXhwIjoxNTg4NDk2NTkxLCJuYmYiOjE1ODg0OTI5OTEsImp0aSI6ImRMWjNQcmN0Tm5UUEdtanMiLCJzdWIiOjUsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.mNh-Rfalspe1SBH_ltfz_ErIAhExJwDIA8td69fZvWA",
     *                              "token_type": "bearer",
     *                              "expires_in": 3600
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized.",
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
     *                          "errors": "Invalid credentials.",
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
     *                          property="errors",
     *                          type="string",
     *                          description="Token error."
     *                      ),
     *                      example={
     *                          "errors": "Could not create access token.",
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh()
    {
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token);
        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,],
            JsonResponse::HTTP_OK);
    }

    public function logout()
    {
        $token = JWTAuth::getToken();

        try {
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'Token invalidated successfully.',], JsonResponse::HTTP_OK);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'message' => 'Failed to logout, please try again.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
