<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramErrorNotification extends Notification
{
    use Queueable;

    private string $title;
    private string $message;
    private array $context;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, array $context = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->context = $context;
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
        $content = "🚨 **{$this->title}**\n\n";
        $content .= "**Message**: {$this->message}\n\n";

        // Add context details
        if (!empty($this->context['file'])) {
            $content .= "**File**: `{$this->context['file']}:{$this->context['line']}`\n";
        }

        if (!empty($this->context['url'])) {
            $content .= "**URL**: `{$this->context['url']}`\n";
        }

        if (!empty($this->context['user_id'])) {
            $content .= "**User**: `ID: {$this->context['user_id']}`\n";
        }

        // Add truncated stack trace
        if (!empty($this->context['trace'])) {
            $trace = $this->context['trace'];
            $traceLines = explode("\n", $trace);
            $truncatedTrace = implode("\n", array_slice($traceLines, 0, 10));

            $content .= "\n**Trace**:\n```\n{$truncatedTrace}\n```";
        }

        return TelegramMessage::create()
            ->content($content)
            ->options(['parse_mode' => 'Markdown']);
    }
}
