<?php 

// app/Models/EventDayLocation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventDayLocation extends Model {
    use HasFactory;

    protected $fillable = [
        'event_day_id','name','link_title','link_url','sort_order'
    ];

    public function day() {
        return $this->belongsTo(EventDay::class,'event_day_id');
    }
}
