<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model { protected $fillable=['order_id','menu_item_id','name','unit_price','quantity','line_total']; protected function casts():array{return ['unit_price'=>'decimal:2','line_total'=>'decimal:2'];} public function menuItem(){return $this->belongsTo(MenuItem::class);} }
