<?php

namespace App\Http\Controllers\api;

use App\Events\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $logInfo = $request->only('email', 'password');

            Log::info('Login attempt', ['credentials' => $logInfo]);

            if (auth()->attempt($logInfo)) {
                $utilisateur = Auth::user();
                Log::info('User authenticated', ['user' => $utilisateur->toArray()]);
                $token = $utilisateur->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'user' => $utilisateur,
                    'token' => $token,
                ]);
            }

            Log::warning('Invalid credentials', ['credentials' => $logInfo]);
            return response()->json([
                'message' => 'Invalid credentials',
                'status' => 401,
            ], 401);
        } catch (\Exception $e) {
            Log::error('Login failed', ['error' => $e->getMessage(), 'credentials' => $logInfo]);
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

//        try {
//            $logInfo = $request->only('email', 'password');
//            if (auth()->attempt($logInfo)) {
//                $utilisateur = Auth::user();
//                event(new UserLoggedIn($utilisateur));
//                $token = $utilisateur->createToken('auth_token')->plainTextToken;
//
//                return response()->json([
//                    'message' => 'Login successful',
//                    'user' => $utilisateur,
//                    'token' => $token,
//                ]);
//
//            }
//
//            return response()->json([
//                'message' => 'Invalid credentials',
//                'status' => 401,
//            ]);
//
//        } catch (Exception $e) {
//            return response()->json([
//                'message' => 'Login failed',
//                'error' => $e->getMessage(),
//                'status' => 500,
//            ]);
//        }
//    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

}
