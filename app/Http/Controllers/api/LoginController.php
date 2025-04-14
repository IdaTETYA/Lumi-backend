<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request):\Illuminate\Http\JsonResponse
    {
        $validateData = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);


        if ($validateData->fails()) {
            return response()->json($validateData->errors()->getMessages(), 400);
        }

        try
        {
            $logInfo = $request->only('email', 'password');

            if (auth()->attempt($logInfo)) {
                $utilisateur = Auth::user();

                $token = $utilisateur->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'user' => $utilisateur,
                    'token' => $token,
                ]);

            }

        }catch(\Exception $e)
        {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
                'status' => 500,
            ]);
        }

    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

}
