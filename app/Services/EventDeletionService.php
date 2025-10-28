<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class EventDeletionService
{
    public function delete(Event $event): void
    {
        $event->load(['days.locations', 'days.details', 'days.resources', 'sponsors']);

        foreach ($event->days as $day) {
            // Remove images
            if ($day->image_path && Storage::disk('public')->exists($day->image_path)) {
                Storage::disk('public')->delete($day->image_path);
            }

            $day->locations()->delete();
            $day->details()->delete();
            $day->resources()->delete();
        }

        $event->days()->delete();
        $event->sponsors()->delete();
        $event->delete();
    }
}
