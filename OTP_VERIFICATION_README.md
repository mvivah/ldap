# Email OTP Verification System

## Overview
This implementation adds email verification using One-Time Password (OTP) codes during user registration.

## Features
- 6-digit OTP code generation
- Email notification with OTP
- OTP expiration (10 minutes)
- Resend OTP functionality
- Automatic login after successful verification

## Database Schema
The `otps` table includes:
- `email` - User's email address (indexed)
- `otp` - 6-digit verification code
- `expires_at` - Expiration timestamp
- `verified_at` - Verification timestamp (nullable)

## API Endpoints

### 1. Register User
**POST** `/api/v1/register`

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "message": "Registration successful. Please check your email for the verification code.",
  "email": "john@example.com"
}
```

### 2. Verify Email
**POST** `/api/v1/verify-email`

**Request Body:**
```json
{
  "email": "john@example.com",
  "otp": "123456"
}
```

**Response (200):**
```json
{
  "message": "Email verified successfully.",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    ...
  },
  "token": "1|abc123..."
}
```

**Error Response (400):**
```json
{
  "message": "Invalid or expired OTP code."
}
```

### 3. Resend OTP
**POST** `/api/v1/resend-otp`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response (200):**
```json
{
  "message": "A new verification code has been sent to your email."
}
```

**Error Response (400):**
```json
{
  "message": "Email is already verified."
}
```

## Usage Flow

1. **User Registration**
   - User submits registration form
   - System creates unverified user account
   - System generates 6-digit OTP
   - OTP is sent to user's email
   - Response includes email for verification step

2. **Email Verification**
   - User receives OTP via email
   - User submits email and OTP code
   - System validates OTP (checks expiration and correctness)
   - On success: user is marked as verified and receives auth token

3. **Resend OTP (if needed)**
   - User can request new OTP if expired or not received
   - Previous unverified OTPs are deleted
   - New OTP is generated and sent

## Configuration

### Mail Setup
Configure your mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### OTP Settings
Default OTP expiration is **10 minutes**. To change this, modify the `createOtp()` method in `app/Models/Otp.php`:
```php
public static function createOtp(string $email, int $expiryMinutes = 10): self
```

## Migration
Run the migration to create the `otps` table:
```bash
php artisan migrate
```

## Testing

### Using cURL

**Register:**
```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Verify Email:**
```bash
curl -X POST http://localhost/api/v1/verify-email \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "otp": "123456"
  }'
```

**Resend OTP:**
```bash
curl -X POST http://localhost/api/v1/resend-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com"
  }'
```

## Security Features

- OTPs expire after 10 minutes
- Only one active OTP per email at a time
- OTPs can only be used once
- Email must exist in users table
- Password confirmation required during registration
- OTP is 6 digits (1 million possible combinations)

## Model Methods

### Otp Model
- `generateOtpCode()` - Generate random 6-digit code
- `createOtp($email, $expiryMinutes)` - Create new OTP for email
- `isValid()` - Check if OTP is valid and not expired
- `isExpired()` - Check if OTP has expired
- `markAsVerified()` - Mark OTP as used
- `getValidOtp($email, $otp)` - Get valid OTP for verification

## Notes

- User cannot login until email is verified
- The notification is queued for better performance
- Old unverified OTPs are automatically deleted when requesting new one
- After successful verification, user automatically receives auth token
