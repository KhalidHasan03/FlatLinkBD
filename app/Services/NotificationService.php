<?php
namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendInApp(int $userId, string $type, string $title, string $message, ?array $data = []): void
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function sendEmail(int $userId, string $subject, string $view, array $data = []): void
    {
        $user = User::find($userId);
        if ($user && $user->email) {
            try {
                Mail::send($view, $data, function ($message) use ($user, $subject) {
                    $message->to($user->email)->subject($subject);
                });
            } catch (\Exception $e) {
                \Log::error('Email sending failed: ' . $e->getMessage());
            }
        }
    }
}
