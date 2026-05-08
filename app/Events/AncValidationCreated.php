<?php

namespace App\Events;

use App\Models\AncValidation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AncValidationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AncValidation $validation
    ) {}
}
