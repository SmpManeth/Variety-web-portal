<?php

// app/Models/EventDay.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventDay extends Model {
    use HasFactory;

    protected $fillable = [
        'event_id','title','date','subtitle','image_path','sort_order'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function locations() {
        return $this->hasMany(EventDayLocation::class)->orderBy('sort_order');
    }

    public function details() {
        return $this->hasMany(EventDayDetail::class)->orderBy('sort_order');
    }

    public function resources() {
        return $this->hasMany(EventDayResource::class)->orderBy('sort_order');
    }
}
