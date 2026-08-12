<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        "created_by",
        "title",
        "description",
        "start_date",
        "end_date",
        "sponsor_image_path",
        "cover_image_path",
    ];

    protected $casts = [
        "start_date" => "date",
        "end_date" => "date",
    ];

    public function days()
    {
        return $this->hasMany(EventDay::class)->orderBy("sort_order");
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function admins()
    {
        return $this->belongsToMany(
            User::class,
            "event_admin",
            "event_id",
            "user_id",
        )->withTimestamps();
    }

    /**
     * User shown as organizer: the account that created the event, when set.
     * Falls back to earliest event_admin row for legacy rows with no created_by.
     */
    public function organizer(): ?User
    {
        if ($this->created_by) {
            return $this->relationLoaded("creator")
                ? $this->creator
                : $this->creator()->first();
        }

        if ($this->relationLoaded("admins")) {
            return $this->admins
                ->sortBy(fn(User $u) => $u->pivot->created_at)
                ->first();
        }

        return $this->admins()->orderBy("event_admin.created_at")->first();
    }

    public function organizerDisplayName(): string
    {
        $user = $this->organizer();
        if (!$user) {
            return "";
        }

        $name = trim((string) $user->name);

        return $name !== "" ? $name : $user->full_name;
    }

    public function isAdmin(User $user)
    {
        if ($user->hasRole("Super Admin")) {
            return true;
        }

        return $this->admins()->where("user_id", $user->id)->exists();
    }

    /**
     * Notificaions
     */
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, "notification_event");
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function permits()
    {
        return $this->hasMany(EventPermit::class);
    }

    public function forms()
    {
        return $this->hasMany(EventForm::class);
    }

    public function jobs()
    {
        return $this->hasMany(EventJob::class);
    }

    public function jobImages()
    {
        return $this->hasMany(JobImage::class);
    }
}
