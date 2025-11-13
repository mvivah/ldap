<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $otp;
    protected string $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp, string $userName = 'User')
    {
        $this->otp = $otp;
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verification OTP')
            ->greeting('Hello ' . $this->userName . '!')
            ->line('Thank you for registering with us.')
            ->line('Your email verification OTP code is:')
            ->line('**' . $this->otp . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
        ];
    }
}
