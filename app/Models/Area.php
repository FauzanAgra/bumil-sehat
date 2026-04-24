<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    protected $fillable = ['subdistrict_id', 'rw_number'];

    // Relationships
    public function subdistrict(): BelongsTo
    {
        return $this->belongsTo(Subdistrict::class);
    }
}
