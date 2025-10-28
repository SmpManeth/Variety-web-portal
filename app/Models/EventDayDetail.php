<?php  

// app/Models/EventDayDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventDayDetail extends Model {
    use HasFactory;

    protected $fillable = [
        'event_day_id','title','description','sort_order'
    ];

    public function day() {
        return $this->belongsTo(EventDay::class,'event_day_id');
    }
}
