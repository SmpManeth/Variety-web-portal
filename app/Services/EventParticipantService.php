<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EventParticipantService
{

    /**
     * Create a single participant (used by StoreEventParticipantRequest).
     *
     * @param  Event  $event
     * @param  array  $data
     * @return EventParticipant|null
     */
    public function create(Event $event, array $data): ?EventParticipant
    {
        try {
            $data['event_id'] = $event->id;

            return EventParticipant::create($data);
        } catch (Throwable $e) {
            Log::error('❌ Failed to create event participant', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    
    /**
     * Delete a participant from an event.
     *
     * @param  EventParticipant  $participant
     * @return array
     */
    public function deleteParticipant(EventParticipant $participant): array
    {
        try {
            $participant->delete();

            return [
                'success' => true,
                'message' => 'Participant deleted successfully.',
            ];
        } catch (\Throwable $e) {
            Log::error('❌ Failed to delete participant', [
                'participant_id' => $participant->id,
                'error'          => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Could not delete participant: ' . $e->getMessage(),
            ];
        }
    }
}
