<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MenuItem extends Model { protected $fillable=['category_id','name','slug','description','price','image_path','is_available']; protected function casts():array{return ['price'=>'decimal:2','is_available'=>'boolean'];} public function category():BelongsTo{return $this->belongsTo(Category::class);} public function wishlistedBy(){return $this->belongsToMany(User::class,'wishlists')->withTimestamps();} }
