<?php

namespace App\Notifications;

use App\Models\PolicyDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PolicyDocument $document, private readonly int $daysRemaining)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Document Expiry Reminder: '.$this->document->title)
            ->greeting('Assalamualaikum '.$notifiable->name.',')
            ->line('A governed policy document is approaching its expiry date.')
            ->line('Title: '.$this->document->title)
            ->line('Reference: '.($this->document->reference_number ?: 'Not assigned'))
            ->line('Expiry date: '.$this->document->expiry_date->format('d M Y'))
            ->line('Days remaining: '.$this->daysRemaining)
            ->action('Review Document', route('policy-documents.show', $this->document));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'document-expiry',
            'category_label' => 'Document Expiry',
            'icon' => 'event_busy',
            'policy_document_id' => $this->document->id,
            'title' => $this->document->title,
            'message' => 'This document expires in '.$this->daysRemaining.' day(s).',
            'change_summary' => 'Expiry date: '.$this->document->expiry_date->format('d M Y'),
            'days_remaining' => $this->daysRemaining,
            'expiry_date' => $this->document->expiry_date->toDateString(),
        ];
    }
}
