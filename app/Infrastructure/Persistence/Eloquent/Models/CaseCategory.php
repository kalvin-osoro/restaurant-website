<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseCategory extends Model
{
    protected $table = 'tbl_case_categories';

    protected $fillable = ['slug', 'name', 'display_order'];

    public function caseStudies(): HasMany
    {
        return $this->hasMany(CaseStudy::class);
    }
}
