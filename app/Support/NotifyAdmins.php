<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyAdmins
{
    public static function send(Notification $notification): void
    {
        try {
            User::query()
                ->where('is_admin', true)
                ->get()
                ->each(fn (User $admin) => $admin->notify($notification));
        } catch (Throwable $exception) {
            Log::error('Failed to notify admins: ' . $exception->getMessage(), [
                'notification' => $notification::class,
            ]);
        }
    }
}
