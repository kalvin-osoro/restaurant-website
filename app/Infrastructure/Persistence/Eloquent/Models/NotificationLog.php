<?php
namespace App\Infrastructure\Persistence\Eloquent\Models;
use Illuminate\Database\Eloquent\Model;
class NotificationLog extends Model {
    protected $table = 'tbl_notification_logs';

    protected $guarded = ['id'];
    public function consultation() { return $this->belongsTo(Consultation::class); }
}
