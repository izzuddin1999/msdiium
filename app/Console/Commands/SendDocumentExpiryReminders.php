<?php

namespace App\Console\Commands;

use App\Models\PolicyDocument;
use App\Models\User;
use App\Notifications\DocumentExpiryReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDocumentExpiryReminders extends Command
{
    protected $signature = 'documents:send-expiry-reminders {--days=30 : Reminder window in days}';
    protected $description = 'Notify policy managers about published documents approaching expiry';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $documents = PolicyDocument::query()
            ->where('status', 'published')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays($days))
            ->get();
        $managers = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['msd_admin', 'kcdiom_liaison'])
            ->get();
        $dispatchCount = 0;

        foreach ($documents as $document) {
            $daysRemaining = (int) today()->diffInDays($document->expiry_date, false);
            foreach ($managers as $manager) {
                $inserted = DB::table('expiry_reminder_dispatches')->insertOrIgnore([
                    'policy_document_id' => $document->id,
                    'user_id' => $manager->id,
                    'expiry_date' => $document->expiry_date->toDateString(),
                    'reminder_days' => $days,
                    'dispatched_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($inserted) {
                    $manager->notify(new DocumentExpiryReminderNotification($document, $daysRemaining));
                    $dispatchCount++;
                }
            }
        }

        $this->info("Dispatched {$dispatchCount} expiry reminder(s).");

        return self::SUCCESS;
    }
}
