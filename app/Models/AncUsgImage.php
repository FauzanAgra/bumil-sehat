<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AncUsgImage extends Model
{
    protected $fillable = [
        'anc_record_id',
        'image_url',
        'uploaded_by'
    ];

    // Relationship
    public function ancRecord(): BelongsTo
    {
        return $this->belongsTo(AncRecord::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_url);
    }
}
