<?php

namespace App\Models;

use App\Services\HtmlSanitizerService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'thumbnail',
        'min_week',
        'max_week',
        'status',
        'created_by',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'min_week' => 'integer',
            'max_week' => 'integer',
        ];
    }

    // Relationship
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by')->withTrashed();
    }

    // Scope
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at');
    }

    public function scopeForWeek(Builder $query, int $week): Builder
    {
        return $query->where(function (Builder $q) use ($week) {
            $q->whereNull('min_week')->orWhere('min_week', '<=', $week);
        })->where(function (Builder $q) use ($week) {
            $q->whereNull('max_week')->orWhere('max_week', '>=', $week);
        });
    }

    // Helper
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function publish(User $admin): void
    {
        $this->update([
            'status' => 'published',
            'published_by' => $admin->id,
            'published_at' => now(),
        ]);
    }

    public function getSanitizedContentAttribute(): string
    {
        return app(HtmlSanitizerService::class)->sanitize($this->content);
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->sanitized_content), 160);
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if ($article->isDirty('content') && is_string($article->content)) {
                $article->content = app(HtmlSanitizerService::class)->sanitize($article->content);
            }
        });
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }

    public function getWeekRangeLabelAttribute(): string
    {
        if (! $this->min_week && ! $this->max_week) {
            return 'Semua usia kehamilan';
        }
        if ($this->min_week && $this->max_week) {
            return "Minggu {$this->min_week}–{$this->max_week}";
        }

        return $this->min_week
            ? "Mulai minggu {$this->min_week}"
            : "Hingga minggu {$this->max_week}";
    }
}
