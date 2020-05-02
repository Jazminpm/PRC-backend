<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => ['required', 'size:9'],
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

        return response()->json(compact('user', 'token'), JsonResponse::HTTP_CREATED);
    }
}
