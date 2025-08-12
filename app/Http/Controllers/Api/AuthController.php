<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Single login for Admin, Enforcer, and Violator
     */
    public function login(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'password' => 'required|string',
    ]);

    $identifier = $request->identifier;
    $password = $request->password;

    // Check if it's a valid email
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        // Try Admin/Enforcer login via User model
        if (Auth::attempt(['email' => $identifier, 'password' => $password])) {
            $user = Auth::user();

            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive'
                ], 401);
            }

            $token = $user->createToken('user-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'user_type' => 'user'
                ]
            ]);
        }
    }

    // If it's a mobile number or a Violator email, try the Violator model
    $violator = Violator::where('email', $identifier)
        ->orWhere('mobile_number', $identifier)
        ->first();

    if ($violator && Hash::check($password, $violator->password)) {
        $token = $violator->createToken('violator-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'violator' => $violator,
                'token' => $token,
                'user_type' => 'violator'
            ]
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid credentials'
    ], 401);
}

    /**
     * Violator registration
     */
    public function violatorRegister(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', 
            'password' => 'required|string|min:6|confirmed',
        ]);

        $identifier = $request->identifier;
        $violator = null;
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $violator = \App\Models\Violator::where('email', $identifier)->first();
        } elseif (preg_match('/^09\\d{9}$/', $identifier)) {
            $violator = \App\Models\Violator::where('mobile_number', $identifier)->first();
        }

        if (!$violator) {
            return response()->json([
                'success' => false,
                'message' => 'No matching violator found in records.'
            ], 404);
        }

        if ($violator->password) {
            return response()->json([
                'success' => false,
                'message' => 'An account already exists for this violator.'
            ], 409);
        }

        $violator->password = Hash::make($request->password);
        $violator->save();
        $token = $violator->createToken('violator-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'violator' => $violator,
                'token' => $token,
                'user_type' => 'violator'
            ]
        ], 201);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if ($user instanceof User) {
            // Admin or Enforcer
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'user_type' => 'user'
                ]
            ]);
        } else {
            // Violator
            return response()->json([
                'success' => true,
                'data' => [
                    'violator' => $user,
                    'user_type' => 'violator'
                ]
            ]);
        }
    }
} 