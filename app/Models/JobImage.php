<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobImage extends Model
{
    protected $fillable = ["event_id", "name", "path"];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
