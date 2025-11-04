<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

final class EventParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'vehicle',
        'status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'username',
        'password',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $participant): void {
            if ($participant->isDirty('password') && $participant->password) {
                $participant->password = Hash::make($participant->password);
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
