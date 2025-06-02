<?php

namespace App\Services;

use App\Models\Otp;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Carbon\Carbon;

class OtpService {
    public function generateOtp(string $email): string {
        // invalidate previous OTPs
        Otp::where('email', $email)->update(['used' => true]);

        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'email' => $email,
            'otp' => $otpCode,
            'expired_at' => Carbon::now()->addMinutes(10)
        ]);

        $this->sendOtpEmail($email, $otpCode);

        return $otpCode;
    }

    public function verifyOtp(string $email, string $otp): bool {
        $otpRecord = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('used', false)
            ->first();

        if(!$otpRecord || $otpRecord->isExpired()) {
            return false;
        }

        $otpRecord->update(['used' => true]);
        return true;
    }

    public function sendOtpEmail(string $email, string $otp): bool {
        $mail = new PHPMailer(true);

        try {
            // server settings
            $mail->isSMTP();
            $mail->Host = env('GMAIL_SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('GMAIL_USERNAME');
            $mail->Password = env('GMAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('GMAIL_SMTP_PORT');

            // recipients
            $mail->setFrom(env('GMAIL_USERNAME'), 'laravel-firebase App');
            $mail->addAddress($email);

            // content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code for laravel-firebase App';
            $mail->Body = "
                <h2>Your OTP Code for laravel-firebase App</h2>
                <p>Your one-time password is: <strong style='font-size: 24px; color: #007bff;'>{$otp}</strong></p>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this code, please ignore this email.</p>
            ";

            $mail->send();
            return true;
        }
        catch(\Exception $e) {
            \Log::error("OTP Email failed: {$mail->ErrorInfo}");
            return false;
        }
    }
}
