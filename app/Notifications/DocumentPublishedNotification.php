<?php

namespace App\Notifications;

use App\Models\PolicyDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentPublishedNotification extends Notification
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
            ->subject('New Document Published: '.$this->policyDocument->title)
            ->greeting('Assalamualaikum '.$notifiable->name.',')
            ->line('A new policy or guideline has been published in the Policy & Circular Management module.')
            ->line('Title: '.$this->policyDocument->title)
            ->line('Type: '.ucfirst($this->policyDocument->document_type))
            ->line('Version: v'.$this->policyDocument->version_number)
            ->line($this->changeSummary())
            ->line('Preview: '.$this->previewExcerpt())
            ->action('View Document', route('policy-documents.show', $this->policyDocument))
            ->line('Please review the updated document through the Staff/Public portal.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'document-publication',
            'category_label' => 'Document Publication',
            'icon' => 'description',
            'policy_document_id' => $this->policyDocument->id,
            'title' => $this->policyDocument->title,
            'message' => 'A new published document is available for review.',
            'change_summary' => $this->changeSummary(),
            'preview_excerpt' => $this->previewExcerpt(),
            'document_type' => $this->policyDocument->document_type,
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
            return 'No preview text was provided for this document.';
        }

        return str($content)->squish()->limit(120)->toString();
    }
}
