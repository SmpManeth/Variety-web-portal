<?php  

// app/Models/EventSponsor.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSponsor extends Model {
    use HasFactory;

    protected $fillable = [
        'event_id','name','logo_url','sort_order'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
