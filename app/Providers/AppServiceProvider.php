<?php

namespace App\Providers;

use App\Models\Dryer;
use Filament\Facades\Filament;
use App\Observers\DryerObserver;
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
        Dryer::observe(DryerObserver::class);
        //Mengatur letak group menu di sidebar
        Filament::registerNavigationGroups([
            'Dashboard',
            'Timbangan',
            'QC',
            'Kapasitas Lumbung',
            'Antar Pulau',
            'Kontrak',
            'Satpam',
        ]);
    }
}
