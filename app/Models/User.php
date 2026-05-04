<?php

namespace App\Models;

use Filament\Tables\Columns\Layout\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_verified'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean'
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    // Relationships
    public function motherProfile(): HasOne
    {
        return $this->hasOne(MotherProfile::class);
    }

    public function medicalProfile(): HasOne
    {
        return $this->hasOne(MedicalProfile::class);
    }

    public function midwifeAreas(): HasMany
    {
        return $this->hasMany(MidwifeArea::class, 'midwife_id');
    }

    public function assignedMothers(): HasMany
    {
        return $this->hasMany(MotherProfile::class, 'assigned_midwife_id');
    }

    public function createdSchedules(): HasMany
    {
        return $this->hasMany(AncSchedule::class, 'created_by');
    }

    public function createdArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'created_by');
    }

    // Helper
    public function isMother(): bool
    {
        return $this->role === 'mother';
    }
    public function isMidwife(): bool
    {
        return $this->role === 'midwife';
    }
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMedicalStaff(): bool
    {
        return in_array($this->role, ['midwife', 'doctor']);
    }
}
