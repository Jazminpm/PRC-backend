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


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => ['required', 'size:9', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'phoneNumber' => ['required', 'string', 'size:9', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'digits:1', 'integer'],
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        }
        $user = User::create([
            'name' => $request->get('name'),
            'surname' => $request->get('surname'),
            'dni' => $request->get('dni'),
            'email' => $request->get('email'),
            'phoneNumber' => $request->get('phoneNumber'),
            'role' => $request->get('role'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,], JsonResponse::HTTP_CREATED);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,],
            JsonResponse::HTTP_OK);
    }

//    public function getAuthenticatedUser()
//    {
//        try {
//            if (!$user = JWTAuth::parseToken()->authenticate()) {
//                return response()->json(['user_not_found'], JsonResponse::HTTP_NOT_FOUND);
//            }
//        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
//            return response()->json(['token_expired'], $e->getStatusCode());
//        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
//            return response()->json(['token_invalid'], $e->getStatusCode());
//        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
//            return response()->json(['token_absent'], $e->getStatusCode());
//        }
//        return response()->json(compact('user'));
//    }
}
