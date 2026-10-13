<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStudy extends Model
{
    protected $fillable = ['slug', 'case_category_id', 'title', 'description', 'value', 'timeline', 'jurisdiction', 'outcome_summary', 'key_details', 'image_url', 'client_industry', 'confidential', 'status', 'display_order', 'featured_position'];

    protected function casts(): array
    {
        return ['key_details' => 'array', 'confidential' => 'boolean', 'featured_position' => 'integer'];
    }

    public function scopePubliclyVisible(Builder $query): void
    {
        $query->where('status', 'published')->where('confidential', false);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CaseCategory::class, 'case_category_id');
    }
}
