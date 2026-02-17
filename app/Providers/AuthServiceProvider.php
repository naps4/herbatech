<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\CPB;
use App\Policies\CPBPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Pastikan mapping ini benar
        CPB::class => CPBPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Define gates untuk testing
        Gate::before(function ($user, $ability) {
            // Jika user QA atau Super Admin, allow semua
            if ($user && ($user->isQA() || $user->isSuperAdmin())) {
                return true;
            }
        });
        
        // Define gates secara eksplisit
        Gate::define('view-cpb', [CPBPolicy::class, 'view']);
        Gate::define('create-cpb', [CPBPolicy::class, 'create']);
        Gate::define('update-cpb', [CPBPolicy::class, 'update']);
        Gate::define('delete-cpb', [CPBPolicy::class, 'delete']);
        Gate::define('handover-cpb', [CPBPolicy::class, 'handover']);
        Gate::define('release-cpb', [CPBPolicy::class, 'release']);
    }
}