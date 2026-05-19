<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalRecordController extends Controller
{
    private function authorizeEventAccess(Event $event): void
    {
        $user = Auth::user();

        if ($user->hasRole("Super Admin")) {
            return;
        }

        abort_unless(
            $user->events()->where("events.id", $event->id)->exists(),
            403,
        );
    }

    private function authorizeMedicalRecord(
        Event $event,
        MedicalRecord $record,
    ): void {
        abort_unless((int) $record->event_id === (int) $event->id, 404);
        $this->authorizeEventAccess($event);
    }

    private function blankToNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === "" ? null : $trimmed;
    }

    /**
     * Decode stored medical record JSON/blob into a plain array (handles cast quirks).
     */
    private function decodeRecordContentArray(MedicalRecord $record): array
    {
        $content = $record->content;

        if ($content instanceof \Illuminate\Support\Collection) {
            return $content->all();
        }

        if (\is_array($content)) {
            return $content;
        }

        if ($content instanceof \ArrayObject) {
            return $content->getArrayCopy();
        }

        if (\is_string($content)) {
            $decoded = json_decode($content, true);

            return \is_array($decoded) ? $decoded : [];
        }

        if (\is_object($content)) {
            return json_decode(json_encode($content), true) ?: [];
        }

        return [];
    }

    /**
     * Values for the edit form: decoded content plus participant fields when blob omits them
     * (same sources as the detail page).
     */
    private function recordContentForEditForm(
        Event $event,
        MedicalRecord $record,
    ): array {
        $record->loadMissing(["participant"]);

        $array = $this->decodeRecordContentArray($record);

        $participant = $record->participant;

        if ($participant !== null) {
            $fallbacks = [
                "first_name" => $participant->first_name,
                "last_name" => $participant->last_name,
                "mobile" => $participant->phone,
                "vehicle" => $participant->vehicle,
                "next_of_kin" => $participant->emergency_contact_name,
                "nok_phone" => $participant->emergency_contact_phone,
            ];

            foreach ($fallbacks as $key => $value) {
                $current = $array[$key] ?? null;

                if (
                    ($current === null || $current === "") &&
                    $value !== null &&
                    $value !== ""
                ) {
                    $array[$key] = $value;
                }
            }
        }

        $array["event_id"] = (string) $event->id;

        return $array;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace("/\D+/", "", $phone);
        $digits = $digits !== null ? trim($digits) : "";

        return $digits !== "" ? $digits : null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $events = $user->hasRole("Super Admin")
            ? Event::latest()->get()
            : $user->events()->latest()->get();

        return view("pages.medical-records.index", compact("events"));
    }

    /**
     * Import a collection of medical records from CSV and store them
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            "event_id" => "required|exists:events,id",
            "csv_file" => "required|file|mimes:csv,txt",
            "destroy_date" => "required|date",
            "acknowledge" => "required",
        ]);

        try {
            $file = $request->file("csv_file");
            $eventId = $validated["event_id"];
            $event = Event::find($eventId);
            $destroyDate = $validated["destroy_date"];
            $path = $file->getRealPath();

            // Start a transaction
            DB::beginTransaction();

            // Replace import: remove existing records for this event first so
            // participant_id unique constraint is not violated on re-import.
            $event->medicalRecords()->delete();

            $participants = EventParticipant::where("event_id", $eventId)
                ->select(["id", "phone"])
                ->get();

            $participantPhoneToId = [];
            foreach ($participants as $p) {
                $normalized = $this->normalizePhone($p->phone);
                if ($normalized) {
                    $participantPhoneToId[$normalized] = $p->id;
                }
            }

            // Parse CSV content
            $fileContent = file_get_contents($path);

            $bom = pack("H*", "EFBBBF");
            $fileContent = preg_replace("/^$bom/", "", $fileContent);

            $handle = fopen("php://memory", "rw");
            fwrite($handle, $fileContent);
            rewind($handle);

            $firstLine = fgets($handle);
            $delimiter = ",";
            if (strpos($firstLine, ";") !== false) {
                $delimiter = ";";
            } elseif (strpos($firstLine, "\t") !== false) {
                $delimiter = "\t";
            }
            rewind($handle);

            // Skip header
            fgetcsv($handle, 10000, $delimiter);

            $created = 0;
            $updated = 0;
            $skippedEmpty = 0;
            $skippedNoMobile = 0;
            $unlinked = 0;

            while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                if (empty(array_filter($row))) {
                    $skippedEmpty++;
                    continue;
                }

                if (
                    empty(trim($row[0])) &&
                    \count($row) > 22 &&
                    !empty(trim($row[1]))
                ) {
                    array_shift($row);
                }

                $row = array_map(
                    fn($value) => \is_string($value) ? trim($value) : $value,
                    $row,
                );

                $dob = null;

                if (isset($row[14]) && !empty($row[14])) {
                    $rawDate = str_replace("-", "/", trim($row[14]));

                    try {
                        $dob = Carbon::createFromFormat(
                            "d/m/Y",
                            $rawDate,
                        )->format("Y/m/d");
                    } catch (\Exception $e) {
                        $dob = null;
                    }
                }

                // Collect record content
                $content = [
                    "event_id" => $eventId,
                    "vehicle" => $row[0] ?? null,
                    "first_name" => $row[1] ?? null,
                    "last_name" => $row[2] ?? null,
                    "nickname" => $row[3] ?? null,
                    "address1" => $row[4] ?? null,
                    "address2" => $row[5] ?? null,
                    "address3" => $row[6] ?? null,
                    "address4" => $row[7] ?? null,
                    "address5" => $row[8] ?? null,
                    "address6" => $row[9] ?? null,
                    "mobile" => $row[10] ?? null,
                    "next_of_kin" => $row[11] ?? null,
                    "nok_phone" => $row[12] ?? null,
                    "nok_alt_phone" => $row[13] ?? null,
                    "dob" => $dob,
                    "allergies" => $row[15] ?? null,
                    "dietary_requirement" => $row[16] ?? null,
                    "past_medical_history" => $row[17] ?? null,
                    "current_medical_history" => $row[18] ?? null,
                    "current_medications" => $row[19] ?? null,
                ];

                $normalizedMobile = $this->normalizePhone(
                    is_string($content["mobile"]) ? $content["mobile"] : null,
                );

                if (!$normalizedMobile) {
                    $skippedNoMobile++;
                    $participantId = null;
                    $unlinked++;
                } else {
                    $participantId =
                        $participantPhoneToId[$normalizedMobile] ?? null;
                    if (!$participantId) {
                        $unlinked++;
                    }
                }

                // Linked participants are unique by participant_id (DB unique index).
                // The same phone can appear on multiple CSV rows — upsert instead of insert.
                if ($participantId !== null) {
                    $record = MedicalRecord::updateOrCreate(
                        ["participant_id" => $participantId],
                        [
                            "event_id" => $eventId,
                            "content" => json_encode($content),
                            "imported_at" => now(),
                            "expires_at" => $destroyDate,
                        ],
                    );
                    if ($record->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } else {
                    MedicalRecord::create([
                        "event_id" => $eventId,
                        "participant_id" => null,
                        "content" => json_encode($content),
                        "imported_at" => now(),
                        "expires_at" => $destroyDate,
                    ]);
                    $created++;
                }
            }

            fclose($handle);
            DB::commit();

            $message =
                "Import completed: {$created} created" .
                ($updated
                    ? ", {$updated} updated (duplicate rows for same participant)"
                    : "") .
                ($skippedEmpty ? ", {$skippedEmpty} empty rows skipped" : "") .
                ($skippedNoMobile
                    ? ", {$skippedNoMobile} rows missing mobile (imported but unlinked)"
                    : "") .
                ($unlinked
                    ? ", {$unlinked} records not linked to a participant"
                    : "") .
                ".";

            return back()->with("success", $message);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Medical record import failed", [
                "event_id" => $request->input("event_id"),
                "error" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
            ]);

            return back()->withErrors([
                "import" => "Medical record import failed: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id, Request $request)
    {
        $event = Event::findOrFail($id);

        if ($request->q) {
            $q = $request->q;
            $participant_ids = $event
                ->participants()
                ->where("first_name", "like", "%{$q}%")
                ->orWhere("last_name", "like", "%{$q}%")
                ->orWhere("phone", "like", "%{$q}%")
                ->get()
                ->pluck("id");

            $records = $event
                ->medicalRecords()
                ->whereIn("participant_id", $participant_ids)
                ->get();
        } else {
            $records = $event->medicalRecords;
        }

        return view("pages.medical-records.show", compact("records", "event"));
    }

    /**
     * Display individual record
     */
    public function showRecord(Event $event, MedicalRecord $record)
    {
        $this->authorizeMedicalRecord($event, $record);

        $record->loadMissing(["participant", "comments", "images"]);

        return view(
            "pages.medical-records.show-record",
            compact("event", "record"),
        );
    }

    /**
     * Show the form for editing an individual medical record.
     */
    public function editRecord(Event $event, MedicalRecord $record)
    {
        $this->authorizeMedicalRecord($event, $record);

        $recordContent = $this->recordContentForEditForm($event, $record);

        return view(
            "pages.medical-records.edit-record",
            compact("event", "record", "recordContent"),
        );
    }

    /**
     * Update an individual medical record (portal edit; preserves extra content keys).
     */
    public function updateRecord(
        Request $request,
        Event $event,
        MedicalRecord $record,
    ) {
        $this->authorizeMedicalRecord($event, $record);

        $validated = $request->validate([
            "vehicle" => "nullable|string|max:500",
            "first_name" => "nullable|string|max:255",
            "last_name" => "nullable|string|max:255",
            "nickname" => "nullable|string|max:255",
            "address1" => "nullable|string|max:255",
            "address2" => "nullable|string|max:255",
            "address3" => "nullable|string|max:255",
            "address4" => "nullable|string|max:255",
            "address5" => "nullable|string|max:255",
            "address6" => "nullable|string|max:255",
            "mobile" => "nullable|string|max:50",
            "next_of_kin" => "nullable|string|max:255",
            "nok_phone" => "nullable|string|max:50",
            "nok_alt_phone" => "nullable|string|max:50",
            "dob" => "nullable|date",
            "allergies" => "nullable|string|max:10000",
            "dietary_requirement" => "nullable|string|max:10000",
            "past_medical_history" => "nullable|string|max:10000",
            "current_medical_history" => "nullable|string|max:10000",
            "current_medications" => "nullable|string|max:10000",
            "expires_at" => "required|date",
        ]);

        $base = $this->decodeRecordContentArray($record);

        $dob = null;
        if (!empty($validated["dob"])) {
            try {
                $dob = Carbon::parse($validated["dob"])->format("Y-m-d");
            } catch (\Exception $e) {
                $dob = $base["dob"] ?? null;
            }
        }

        $merged = array_merge($base, [
            "event_id" => (string) $event->id,
            "vehicle" => $this->blankToNull($validated["vehicle"] ?? null),
            "first_name" => $this->blankToNull(
                $validated["first_name"] ?? null,
            ),
            "last_name" => $this->blankToNull($validated["last_name"] ?? null),
            "nickname" => $this->blankToNull($validated["nickname"] ?? null),
            "address1" => $this->blankToNull($validated["address1"] ?? null),
            "address2" => $this->blankToNull($validated["address2"] ?? null),
            "address3" => $this->blankToNull($validated["address3"] ?? null),
            "address4" => $this->blankToNull($validated["address4"] ?? null),
            "address5" => $this->blankToNull($validated["address5"] ?? null),
            "address6" => $this->blankToNull($validated["address6"] ?? null),
            "mobile" => $this->blankToNull($validated["mobile"] ?? null),
            "next_of_kin" => $this->blankToNull(
                $validated["next_of_kin"] ?? null,
            ),
            "nok_phone" => $this->blankToNull($validated["nok_phone"] ?? null),
            "nok_alt_phone" => $this->blankToNull(
                $validated["nok_alt_phone"] ?? null,
            ),
            "dob" => $dob,
            "allergies" => $this->blankToNull($validated["allergies"] ?? null),
            "dietary_requirement" => $this->blankToNull(
                $validated["dietary_requirement"] ?? null,
            ),
            "past_medical_history" => $this->blankToNull(
                $validated["past_medical_history"] ?? null,
            ),
            "current_medical_history" => $this->blankToNull(
                $validated["current_medical_history"] ?? null,
            ),
            "current_medications" => $this->blankToNull(
                $validated["current_medications"] ?? null,
            ),
        ]);

        $record->content = json_encode($merged);
        $record->expires_at = $validated["expires_at"];
        $record->save();

        return redirect()
            ->route("medical-records.show-record", [$event, $record])
            ->with("success", "Medical record updated.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $event = Event::findOrFail($id);
        $event->medicalRecords()->delete();

        return redirect()
            ->route("medical-records.index")
            ->with("success", "Medical records deleted");
    }
}
