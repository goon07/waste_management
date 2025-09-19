<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CollectionScheduled extends Notification
{
    use Queueable;

    protected $schedule;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    public function via($notifiable)
    {
        return $notifiable->notifications_enabled ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Collection Scheduled')
            ->line('A new collection has been scheduled.')
            ->line('Resident: ' . $this->schedule->collection->user->name)
            ->line('Waste Type: ' . $this->schedule->collection->wasteType->name)
            ->line('Date: ' . $this->schedule->scheduled_date->format('Y-m-d'))
            ->line('Collector: ' . ($this->schedule->collector->name ?? 'Unassigned'))
            ->action('View Dashboard', route('management.dashboard'));
    }
}