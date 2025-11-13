<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendOtpNotification;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);
        try {
            //create user (unverified)
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'email_verified_at' => null, // User is not verified yet
            ]);

            // Generate and send OTP
            $otp = Otp::createOtp($user->email);
            
            // Send OTP via email
            Notification::route('mail', $user->email)
                ->notify(new SendOtpNotification($otp->otp, $user->first_name));

            return response()->json([
                'message' => 'Registration successful. Please check your email for the verification code.',
                'email' => $user->email,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify email with OTP
     */
    public function verifyEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        try {
            // Find the OTP
            $otp = Otp::getValidOtp($data['email'], $data['otp']);

            if (!$otp) {
                return response()->json([
                    'message' => 'Invalid or expired OTP code.'
                ], 400);
            }

            // Mark OTP as verified
            $otp->markAsVerified();

            // Update user's email_verified_at
            $user = User::where('email', $data['email'])->first();
            $user->update(['email_verified_at' => now()]);

            // Generate token for automatic login after verification
            $token = $user->createToken($request->userAgent() ?? 'default');

            return response()->json([
                'message' => 'Email verified successfully.',
                'user' => $user,
                'token' => $token->plainTextToken,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $data['email'])->first();

            // Check if user is already verified
            if ($user->email_verified_at) {
                return response()->json([
                    'message' => 'Email is already verified.'
                ], 400);
            }

            // Generate and send new OTP
            $otp = Otp::createOtp($user->email);

            Notification::route('mail', $user->email)
                ->notify(new SendOtpNotification($otp->otp, $user->first_name));

            return response()->json([
                'message' => 'A new verification code has been sent to your email.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to resend OTP',
                'error' => $e->getMessage()
            ], 500);
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
