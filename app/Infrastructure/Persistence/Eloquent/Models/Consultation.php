<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;
use Illuminate\Database\Eloquent\Model;
class Consultation extends Model {
    protected $fillable = ['reference', 'full_name', 'organization', 'email', 'phone', 'nature_of_inquiry', 'details', 'preferred_office'];
    protected $hidden = ['full_name', 'organization', 'email', 'phone', 'details'];
    protected function casts(): array {
        return array_fill_keys(['full_name', 'organization', 'email', 'phone', 'details'], 'encrypted');
    }
}
