<?php

namespace App\Providers;

use App\Events\AncValidationCreated;
use App\Listeners\AutoActivateTtdOnValidation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings.
     */
    protected $listen = [
        AncValidationCreated::class => [
            AutoActivateTtdOnValidation::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
