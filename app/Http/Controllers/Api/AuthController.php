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
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected function getUserModelByIdentifier($identifier)
{
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $models = [
            \App\Models\Admin::class,
            \App\Models\Deputy::class,
            \App\Models\Head::class,
            \App\Models\Enforcer::class,
        ];

        foreach ($models as $model) {
            $user = $model::where('email', $identifier)->first();
            if ($user) return $user;
        }
    }

    return null;
}

    /**
     * Login for Head, Deputy, Admin, Enforcer, and Violator
     */
    public function login(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'password' => 'required|string',
    ]);

    $identifier = $request->identifier;
    $password = $request->password;

    // Check Admin / Deputy / Head / Enforcer
    $user = $this->getUserModelByIdentifier($identifier);
    Log::info('Login attempt', [
    'identifier' => $identifier,
    'password_entered' => $password,
    'user_found' => $user ? true : false,
    'user_id' => $user->id ?? null,
]);
    if ($user && Hash::check($password, $user->password)) {
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive'
            ], 401);
        }
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'user_type' => class_basename($user) 
            ]
        ]);
    }

    // Violator login 
    $violator = Violator::where('email', $identifier)
        ->orWhere('mobile_number', $identifier)
        ->first();

    if ($violator && Hash::check($password, $violator->password)) {
          $token = $violator->createToken('violator-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'violator' => $violator,
                'token' => $token,
                'user_type' => 'Violator'
            ]
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid credentials'
    ], 200);
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
            $violator = Violator::where('email', $identifier)->first();
        } elseif (preg_match('/^09\\d{9}$/', $identifier)) {
            $violator = Violator::where('mobile_number', $identifier)->first();
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
    $user = $request->user('sanctum');
    if ($user) {
        $user->currentAccessToken()->delete();
    }

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
    $user = $request->user('sanctum');
    //Request profile by Admin, Deputy, Head, Enforcer, Violator
    if ($type = class_basename($user)){
        return response()->json([
        'success' => true,
        'data' => [
            strtolower($type) => $user,
            'user_type' => $type
        ]
    ]);
    } else{
         $violator = Violator::find($user->id);

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