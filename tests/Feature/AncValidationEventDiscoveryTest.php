<?php

namespace Tests\Feature;

use App\Events\AncValidationCreated;
use App\Listeners\AutoActivateTtdOnValidation;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AncValidationEventDiscoveryTest extends TestCase
{
    public function test_auto_activate_ttd_listener_is_auto_discovered_for_anc_validation_created(): void
    {
        Event::fake();

        Event::assertListening(
            AncValidationCreated::class,
            AutoActivateTtdOnValidation::class,
        );
    }

    public function test_auto_activate_ttd_listener_is_registered_exactly_once(): void
    {
        $rawListeners = Event::getRawListeners()[AncValidationCreated::class] ?? [];

        $matches = array_filter($rawListeners, function ($listener): bool {
            if (is_string($listener)) {
                return str_starts_with($listener, AutoActivateTtdOnValidation::class);
            }

            if (is_array($listener)) {
                return ($listener[0] ?? null) === AutoActivateTtdOnValidation::class;
            }

            return false;
        });

        $this->assertCount(1, $matches);
    }
}
