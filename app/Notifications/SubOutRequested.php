<?php

namespace App\Notifications;

use App\Filament\Resources\Gigs\GigResource;
use App\Models\GigAssignment;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubOutRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public GigAssignment $assignment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $gig = $this->assignment->gig;
        $musician = $this->assignment->user;
        $instrument = $this->assignment->instrument;
        $companyName = Setting::get('company_name', config('app.name'));

        $message = (new MailMessage)
            ->subject("[{$companyName}] URGENT: Sub-Out Request for {$gig->name}")
            ->greeting('Urgent: Sub-Out Request')
            ->line('A musician has requested to sub-out of a gig assignment and needs a replacement.')
            ->line("**Gig:** {$gig->name}")
            ->line("**Date:** {$gig->date->format('l, F j, Y')}")
            ->line("**Musician:** {$musician->name}")
            ->line('**Instrument:** '.($instrument?->name ?? 'Not specified'))
            ->line("**Reason:** {$this->assignment->subout_reason}")
            ->action('View Gig in Admin', GigResource::getUrl('view', ['record' => $gig]))
            ->line('Please find a replacement musician as soon as possible.');

        $notificationEmail = Setting::get('notification_email');
        if ($notificationEmail) {
            $message->cc($notificationEmail);
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'gig_id' => $this->assignment->gig_id,
            'user_id' => $this->assignment->user_id,
            'subout_reason' => $this->assignment->subout_reason,
        ];
    }
}
