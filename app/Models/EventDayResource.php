<?php   
// app/Models/EventDayResource.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventDayResource extends Model {
    use HasFactory;

    protected $fillable = [
        'event_day_id','title','url','sort_order'
    ];

    public function day() {
        return $this->belongsTo(EventDay::class,'event_day_id');
    }
}
