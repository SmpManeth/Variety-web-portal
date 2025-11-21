<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'sponsor_image_path'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    public function days()
    {
        return $this->hasMany(EventDay::class)->orderBy('sort_order');
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }
}
