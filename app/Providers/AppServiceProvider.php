<?php

namespace App\Providers;

use App\Models\GEOrder;
use App\Models\PurchaseOrder;
use App\Policies\GEOrderPolicy;
use App\Policies\PurchaseOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

//use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        GEOrder::class => GEOrderPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
    ];

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
        $this->registerPolicies();
        Gate::before(fn($user) => $user->hasRole('super_admin') ? true : null);
    }
}
