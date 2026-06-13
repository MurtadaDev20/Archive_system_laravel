<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Status extends Model
{
    protected $fillable = ['name', 'slug', 'label_ar', 'sort_order', 'color'];

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'status_id');
    }

    public static function idForSlug(string $slug): ?int
    {
        return Cache::remember("status.slug.{$slug}", 3600, fn () => static::where('slug', $slug)->value('id'));
    }

    public static function idsForSlugs(array $slugs): array
    {
        return array_values(array_filter(array_map(fn (string $slug) => static::idForSlug($slug), $slugs)));
    }

    public function label(): string
    {
        return $this->label_ar ?: $this->name;
    }
}
