<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attorney extends Model
{
    protected $fillable = ['slug', 'name', 'role', 'bio', 'image_url', 'email', 'phone', 'education', 'admissions', 'notable_cases', 'status', 'display_order'];

    protected function casts(): array
    {
        return ['education' => 'array', 'admissions' => 'array', 'notable_cases' => 'array'];
    }

    public function scopePubliclyVisible(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function practiceAreas(): BelongsToMany
    {
        return $this->belongsToMany(PracticeArea::class)->orderBy('display_order')->orderBy('slug');
    }
}
