<?php

namespace App\Notifications;

use App\Models\PolicyDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CircularPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PolicyDocument $policyDocument)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('New Circular Published: '.$this->policyDocument->title)
            ->greeting('Assalamualaikum '.$notifiable->name.',')
            ->line('A new circular has been published in the Policy & Circular Management module.')
            ->line('Title: '.$this->policyDocument->title)
            ->line('Version: v'.$this->policyDocument->version_number)
            ->line($this->changeSummary())
            ->line('Preview: '.$this->previewExcerpt())
            ->action('View Circular', route('policy-documents.show', $this->policyDocument))
            ->line('Please review the circular through the Staff/Public portal.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'circular-publication',
            'category_label' => 'Circular Publication',
            'icon' => 'notifications_active',
            'policy_document_id' => $this->policyDocument->id,
            'title' => $this->policyDocument->title,
            'message' => 'A new circular is available for Staff/Public review.',
            'change_summary' => $this->changeSummary(),
            'preview_excerpt' => $this->previewExcerpt(),
            'version_number' => $this->policyDocument->version_number,
            'published_at' => optional($this->policyDocument->published_at)?->toDateTimeString(),
        ];
    }

    private function changeSummary(): string
    {
        if (filled($this->policyDocument->revision_summary)) {
            return 'Release summary: '.str($this->policyDocument->revision_summary)->squish()->limit(180)->toString();
        }

        if ($this->policyDocument->version_number > 1) {
            return 'Updated release: now available as version v'.$this->policyDocument->version_number.'.';
        }

        return 'Initial release: first published version is now available.';
    }

    private function previewExcerpt(): string
    {
        $content = trim((string) $this->policyDocument->content);

        if ($content === '') {
            return 'No preview text was provided for this circular.';
        }

        return str($content)->squish()->limit(120)->toString();
    }
}
