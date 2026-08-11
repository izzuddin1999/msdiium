<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class TransferSqliteToPostgres extends Command
{
    protected $signature = 'database:transfer-sqlite-to-postgres
                            {--force : Replace data currently stored in the target application tables}';

    protected $description = 'Copy the existing SQLite application data into the configured PostgreSQL schema';

    private const TABLES = [
        'users',
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'lookup_values',
        'notifications',
        'password_reset_tokens',
        'sessions',
        'topic_categories',
        'topic_subtopics',
        'topic_details',
        'form_templates',
        'form_fields',
        'policy_documents',
        'directory_sync_runs',
        'document_form_responses',
        'document_histories',
        'document_attachments',
        'document_activity_logs',
        'expiry_reminder_dispatches',
    ];

    public function handle(): int
    {
        $schema = env('MIGRATION_PG_SCHEMA', 'public');
        if (! preg_match('/^[a-z_][a-z0-9_]*$/i', $schema)) {
            throw new RuntimeException('MIGRATION_PG_SCHEMA contains an invalid schema name.');
        }

        foreach (['HOST', 'DATABASE', 'USERNAME', 'PASSWORD'] as $key) {
            if ((string) env("MIGRATION_PG_{$key}") === '') {
                throw new RuntimeException("MIGRATION_PG_{$key} is required.");
            }
        }

        config()->set('database.connections.migration_pgsql', [
            ...config('database.connections.pgsql'),
            'host' => env('MIGRATION_PG_HOST'),
            'port' => env('MIGRATION_PG_PORT', '5432'),
            'database' => env('MIGRATION_PG_DATABASE'),
            'username' => env('MIGRATION_PG_USERNAME'),
            'password' => env('MIGRATION_PG_PASSWORD'),
            'search_path' => $schema,
        ]);

        DB::purge('migration_pgsql');
        $source = DB::connection('sqlite');
        $target = DB::connection('migration_pgsql');

        $existingRows = collect(self::TABLES)
            ->filter(fn (string $table): bool => Schema::connection('migration_pgsql')->hasTable($table))
            ->sum(fn (string $table): int => $target->table($table)->count());

        if ($existingRows > 0 && ! $this->option('force')) {
            $this->error("Target tables already contain {$existingRows} rows. Re-run with --force to replace them.");

            return self::FAILURE;
        }

        $qualifiedTables = collect(self::TABLES)
            ->map(fn (string $table): string => sprintf('"%s"."%s"', $schema, $table))
            ->implode(', ');

        $counts = [];
        $target->beginTransaction();

        try {
            $target->statement("TRUNCATE TABLE {$qualifiedTables} RESTART IDENTITY CASCADE");

            foreach (self::TABLES as $table) {
                $rows = $source->table($table)->get()->map(fn (object $row): array => (array) $row);
                $booleanColumns = collect(Schema::connection('migration_pgsql')->getColumns($table))
                    ->filter(fn (array $column): bool => in_array(strtolower($column['type_name']), ['bool', 'boolean'], true))
                    ->pluck('name');

                $rows->chunk(250)->each(function ($chunk) use ($target, $table, $booleanColumns): void {
                    $payload = $chunk->map(function (array $row) use ($booleanColumns): array {
                        foreach ($booleanColumns as $column) {
                            if (array_key_exists($column, $row) && $row[$column] !== null) {
                                $row[$column] = (bool) $row[$column];
                            }
                        }

                        return $row;
                    })->all();

                    if ($payload !== []) {
                        $target->table($table)->insert($payload);
                    }
                });

                $counts[$table] = $rows->count();
            }

            $this->resetSequences($target, $schema);
            $target->commit();
        } catch (Throwable $exception) {
            $target->rollBack();
            throw $exception;
        }

        $this->newLine();
        $this->table(
            ['Table', 'SQLite', 'PostgreSQL', 'Result'],
            collect($counts)->map(function (int $sourceCount, string $table) use ($target): array {
                $targetCount = $target->table($table)->count();

                return [$table, $sourceCount, $targetCount, $sourceCount === $targetCount ? 'OK' : 'MISMATCH'];
            })->values()->all()
        );

        $mismatches = collect($counts)->contains(
            fn (int $sourceCount, string $table): bool => $target->table($table)->count() !== $sourceCount
        );

        if ($mismatches) {
            $this->error('Transfer completed with row-count mismatches.');

            return self::FAILURE;
        }

        $this->info('All existing SQLite records were transferred and verified successfully.');

        return self::SUCCESS;
    }

    private function resetSequences($target, string $schema): void
    {
        $sequences = $target->select(
            <<<'SQL'
                SELECT table_name, column_name,
                       pg_get_serial_sequence(format('%I.%I', table_schema, table_name), column_name) AS sequence_name
                FROM information_schema.columns
                WHERE table_schema = ?
                  AND column_default LIKE 'nextval(%'
            SQL,
            [$schema]
        );

        foreach ($sequences as $sequence) {
            if (! $sequence->sequence_name) {
                continue;
            }

            $maximum = $target->table($sequence->table_name)->max($sequence->column_name);
            $target->select(
                'SELECT setval(?::regclass, ?, ?)',
                [$sequence->sequence_name, $maximum ?: 1, $maximum !== null]
            );
        }
    }
}
