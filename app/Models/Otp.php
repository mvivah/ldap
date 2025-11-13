<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate a random 6-digit OTP
     */
    public static function generateOtpCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for an email
     */
    public static function createOtp(string $email, int $expiryMinutes = 10): self
    {
        // Delete any existing unverified OTPs for this email
        self::where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        return self::create([
            'email' => $email,
            'otp' => self::generateOtpCode(),
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Check if OTP is valid
     */
    public function isValid(): bool
    {
        return $this->verified_at === null 
            && $this->expires_at->isFuture();
    }

    /**
     * Mark OTP as verified
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => Carbon::now()]);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get latest valid OTP for an email
     */
    public static function getValidOtp(string $email, string $otp): ?self
    {
        return self::where('email', $email)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }
}
