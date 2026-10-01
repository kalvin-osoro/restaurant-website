<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model { protected $fillable=['user_id','status','payment_status','payment_method','subtotal','delivery_fee','total','delivery_address','notes']; protected function casts():array{return ['subtotal'=>'decimal:2','delivery_fee'=>'decimal:2','total'=>'decimal:2'];} public function items(){return $this->hasMany(OrderItem::class);} public function user(){return $this->belongsTo(User::class);} }
