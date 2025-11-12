<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        try {
            //create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            //generate token
            // $user->tokens()->create([
            //     'name' => 'default',
            //     'token' => Hash::make(bin2hex(random_bytes(40))),
            //     'abilities' => ['*'],
            // ]);
            $token = $user->createToken($request->userAgent() ?? 'default');
            return [
                'user' => $user,
                // 'token' => $user->tokens()->first()->token,
                'token' => $token->plainTextToken,
            ];
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email|max:255|exists:users',
            'password' => 'required|string|min:8',
        ]);
        try {
            //get user by email and check password
            $user = User::where('email', $data['email'])->first();
            if (! $user || ! Hash::check($data['password'], $user->password)) {
                return ['message' => 'You have entered invalid credentials'];
            }
            //generate token
            $token = $user->createToken($request->userAgent() ?? 'default');
            //return user and token
            return [
                'user' => $user,
                'token' => $token->plainTextToken,
            ];
        } catch (Exception $e) {
            return ['message' => 'Login failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Display the specified resource.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ['message' => 'Logged out successfully'];
    }

    /**
     * Update the specified resource in storage.
     */
    public function forgotPassword(Request $request)
    {
        return 'forgot password';
    }

    /**
     * Remove the specified resource from storage.
     */
    public function resetPassword(Request $request, string $id)
    {
        return 'reset password';
    }
}
