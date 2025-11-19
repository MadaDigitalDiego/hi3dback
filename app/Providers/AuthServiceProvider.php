<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\File;
use App\Models\StripeConfiguration;
use App\Policies\FilePolicy;
use App\Policies\StripeConfigurationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        File::class => FilePolicy::class,
        StripeConfiguration::class => StripeConfigurationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
