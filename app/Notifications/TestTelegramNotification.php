<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TestTelegramNotification extends Notification
{
    use Queueable;

    private string $message;

    public function __construct(string $message = 'Test notification from Laravel Blog API')
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->content("🧪 **Test Notification**\n\n{$this->message}\n\n✅ Telegram integration is working!")
            ->options(['parse_mode' => 'Markdown']);
    }
}
