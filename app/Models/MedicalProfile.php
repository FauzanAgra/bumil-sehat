<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialization_id',
        'license_number',
        'degree_prefix',
        'degree_suffix',
        'phone',
        'availability'
    ];

    // Relation

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    // Helper
    public function getFullNameAttribute(): string
    {
        $prefix = $this->degree_prefix ? "{$this->degree_prefix}" : '';

        $suffix = $this->degree_suffix ? ", {$this->degree_suffix}" : '';
        return "{$prefix}{$this->user->name}{$suffix}";
    }

    // Number Whatsapp
    public function getWhatsappNumberAttribute(): string
    {
        return $this->phone ?? $this->user->phone;
    }
}
