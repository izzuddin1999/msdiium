<?php

namespace App\Providers;

use App\Models\PolicyDocument;
use App\Observers\PolicyDocumentObserver;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
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
        Paginator::useBootstrapFive();

        PolicyDocument::observe(PolicyDocumentObserver::class);
        View::composer('*', function ($view): void {
            if (! Schema::hasTable('users')) {
                $view->with('viewerOptions', collect());
                $view->with('recentNotifications', collect());

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
        });
    }
}
