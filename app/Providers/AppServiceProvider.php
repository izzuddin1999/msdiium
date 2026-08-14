<?php

namespace App\Providers;

use App\Models\PolicyDocument;
use App\Observers\PolicyDocumentObserver;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Temporary local fallback: expose the SQLite database under the
        // PostgreSQL schema name used by the application's table mappings.
        if (config('database.default') === 'sqlite' && ! app()->runningUnitTests()) {
            DB::statement("ATTACH DATABASE '".str_replace("'", "''", database_path('database.sqlite'))."' AS hr_intern");

            if (! Schema::hasTable('organizations')) {
                Schema::create('organizations', function ($table): void {
                    $table->id();
                    $table->string('code', 50)->unique();
                    $table->string('name', 180);
                    $table->string('organization_type', 30);
                    $table->unsignedBigInteger('parent_id')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
            }

            DB::table('organizations')->updateOrInsert(
                ['code' => 'MSD'],
                ['name' => 'Management Services Division', 'organization_type' => 'division', 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
            DB::table('organizations')->updateOrInsert(
                ['code' => 'KCDIOM'],
                ['name' => 'Kulliyyah, Centre, Division, Institute and Office Management', 'organization_type' => 'group', 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );

            $msdOrganizationId = DB::table('organizations')->where('code', 'MSD')->value('id');
            $kcdiomOrganizationId = DB::table('organizations')->where('code', 'KCDIOM')->value('id');

            foreach (['users' => 'unit', 'policy_documents' => 'owner_unit', 'form_templates' => 'owner_unit'] as $tableName => $unitColumn) {
                if (! Schema::hasTable($tableName)) {
                    continue;
                }
                if (! Schema::hasColumn($tableName, 'organization_id')) {
                    Schema::table($tableName, fn ($table) => $table->unsignedBigInteger('organization_id')->nullable());
                }

                DB::table($tableName)->where($unitColumn, 'kcdiom')->whereNull('organization_id')->update(['organization_id' => $kcdiomOrganizationId]);
                DB::table($tableName)->whereIn($unitColumn, ['all', 'msd'])->whereNull('organization_id')->update(['organization_id' => $msdOrganizationId]);
            }

            // Keep the temporary SQLite workspace compatible with the
            // organization-scoped lookup screens used by the application.
            if (Schema::hasTable('lookup_values')) {
                if (! Schema::hasColumn('lookup_values', 'owner_unit')) {
                    Schema::table('lookup_values', fn ($table) => $table->string('owner_unit', 20)->default('msd'));
                }
                if (! Schema::hasColumn('lookup_values', 'organization_id')) {
                    Schema::table('lookup_values', fn ($table) => $table->unsignedBigInteger('organization_id')->nullable());
                }

                DB::table('lookup_values')->whereNull('owner_unit')->orWhere('owner_unit', '')->update(['owner_unit' => 'msd']);
                DB::table('lookup_values')->where('owner_unit', 'kcdiom')->whereNull('organization_id')->update(['organization_id' => $kcdiomOrganizationId]);
                DB::table('lookup_values')->where('owner_unit', 'msd')->whereNull('organization_id')->update(['organization_id' => $msdOrganizationId]);
            }
        }

        Paginator::useBootstrapFive();

        PolicyDocument::observe(PolicyDocumentObserver::class);
        View::composer('*', function ($view): void {
            if (! Schema::hasTable('users')) {
                $view->with('viewerOptions', collect());
                $view->with('recentNotifications', collect());
                $view->with('publicOrganizations', collect());

                return;
            }

            $viewer = auth()->user();

            $view->with('viewerOptions', User::query()
                ->where('is_active', true)
                ->orderBy('role')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'unit', 'is_active']));

            $view->with('recentNotifications', $viewer?->notifications()->latest()->limit(5)->get() ?? collect());
            $view->with('unreadNotificationCount', $viewer?->unreadNotifications()->count() ?? 0);
            $view->with('publicOrganizations', Schema::hasTable('organizations')
                ? Organization::query()->where('is_active', true)->orderBy('name')->get()
                : collect());
        });
    }
}
