<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PracticeArea extends Model
{
    protected $table = 'tbl_practice_areas';

    protected $fillable = ['slug', 'name', 'display_order'];

    public function attorneys(): BelongsToMany
    {
        return $this->belongsToMany(Attorney::class, 'tbl_attorney_practice_area');
    }
}
