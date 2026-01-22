<?php

namespace App\Notifications;

use App\Filament\Resources\Gigs\GigResource;
use App\Models\GigAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GigAssignmentDeclined extends Notification implements ShouldQueue
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

        $message = (new MailMessage)
            ->subject("Gig Assignment Declined: {$gig->name}")
            ->greeting('Assignment Declined')
            ->line('A musician has declined a gig assignment.')
            ->line("**Gig:** {$gig->name}")
            ->line("**Date:** {$gig->date->format('l, F j, Y')}")
            ->line("**Musician:** {$musician->name}")
            ->line('**Instrument:** '.($instrument?->name ?? 'Not specified'));

        if ($this->assignment->decline_reason) {
            $message->line("**Reason:** {$this->assignment->decline_reason}");
        }

        return $message
            ->action('View Gig in Admin', GigResource::getUrl('view', ['record' => $gig]))
            ->line('You may need to find a replacement musician for this assignment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'gig_id' => $this->assignment->gig_id,
            'user_id' => $this->assignment->user_id,
            'decline_reason' => $this->assignment->decline_reason,
        ];
    }
}
