<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;
use Illuminate\Database\Eloquent\Model;
class NotificationLog extends Model {
    protected $guarded = ['id'];
    public function consultation() { return $this->belongsTo(Consultation::class); }
}
