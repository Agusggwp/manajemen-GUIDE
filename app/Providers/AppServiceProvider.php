<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\Gate::define('admin', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('staff', fn(\App\Models\User $user) => $user->isAdmin() || $user->isStaff());
        \Illuminate\Support\Facades\Gate::define('guide', fn(\App\Models\User $user) => $user->isGuide());

        \Illuminate\Support\Facades\Gate::define('manage-guides', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('manage-bookings', fn(\App\Models\User $user) => $user->isAdmin() || $user->isStaff());
        \Illuminate\Support\Facades\Gate::define('manage-payroll', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('manage-settings', fn(\App\Models\User $user) => $user->isAdmin());

        \Illuminate\Support\Facades\Gate::define('view-guide', function (\App\Models\User $user, \App\Models\Guide $guide) {
            if ($user->isAdmin() || $user->isStaff()) {
                return true;
            }
            return $user->isGuide() && $user->guide && $user->guide->id === $guide->id;
        });

        \Illuminate\Support\Facades\Gate::define('view-assignment', function (\App\Models\User $user, \App\Models\GuideAssignment $assignment) {
            if ($user->isAdmin() || $user->isStaff()) {
                return true;
            }
            return $user->isGuide() && $user->guide && $user->guide->id === $assignment->guide_id;
        });

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                $appLogo = \App\Models\Setting::get('company_logo');
                $appName = \App\Models\Setting::get('company_name', \App\Models\Setting::get('app_name', 'ARTDEVATA'));
                $appTagline = \App\Models\Setting::get('company_tagline', \App\Models\Setting::get('app_tagline', 'Guide Management'));
                $appShortName = \App\Models\Setting::get('app_short_name', substr($appName, 0, 1));
                $guidePortalTitle = \App\Models\Setting::get('guide_portal_title', 'Portal Pemandu Wisata');
                $appFooterText = \App\Models\Setting::get('app_footer_text', "© " . date('Y') . " {$appName}. Hak cipta dilindungi.");
            } catch (\Throwable $e) {
                $appLogo = null;
                $appName = 'ARTDEVATA';
                $appTagline = 'Guide Management';
                $appShortName = 'A';
                $guidePortalTitle = 'Portal Pemandu Wisata';
                $appFooterText = '© ' . date('Y') . ' ARTDEVATA. Hak cipta dilindungi.';
            }
            $view->with(compact('appLogo', 'appName', 'appTagline', 'appShortName', 'guidePortalTitle', 'appFooterText'));
        });
    }
}
