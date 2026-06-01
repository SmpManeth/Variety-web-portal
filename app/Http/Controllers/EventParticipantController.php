<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventParticipantRequest;
use App\Http\Requests\UpdateEventParticipantRequest;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Services\EventParticipantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class EventParticipantController extends Controller
{
    /**
     * Import participants from Excel file.
     */
    public function indexAjax(Request $request, Event $event): JsonResponse
    {
        if (Auth::user()->cannot("viewParticipants", Event::class)) {
            abort(403);
        }

        return response()->json([
            "success" => true,
            "participants" => $event->participants,
        ]);
    }

    public function store(
        StoreEventParticipantRequest $request,
        Event $event,
        EventParticipantService $service,
    ): RedirectResponse {
        if (Auth::user()->cannot("createParticipants", Event::class)) {
            abort(403);
        }

        $service->create($event, $request->validated());
        return back()->with("success", "Participant added successfully.");
    }

    public function destroy(
        Event $event,
        EventParticipant $participant,
    ): RedirectResponse {
        if (Auth::user()->cannot("deleteParticipants", Event::class)) {
            abort(403);
        }

        abort_unless($participant->event_id === $event->id, 403);

        $participant->delete();

        return back()->with("success", "Participant deleted.");
    }

    public function bulkDestroy(
        Request $request,
        Event $event,
    ): RedirectResponse {
        if (Auth::user()->cannot("deleteParticipants", Event::class)) {
            abort(403);
        }

        $validated = $request->validate([
            "participant_ids" => ["required", "array", "min:1"],
            "participant_ids.*" => ["integer"],
        ]);

        $ids = array_values(array_unique($validated["participant_ids"]));

        $countInEvent = $event->participants()->whereIn("id", $ids)->count();

        abort_unless($countInEvent === count($ids), 403);

        $deleted = $event->participants()->whereIn("id", $ids)->delete();

        return back()->with("success", "{$deleted} participant(s) deleted.");
    }

    public function downloadTemplate()
    {
        // Create new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Participants Template");

        // Define header columns
        $headers = [
            "First Name",
            "Last Name",
            "Email",
            "Phone",
            "Vehicle",
            "Status (active/inactive)",
            "Emergency Contact Name",
            "Emergency Contact Phone",
            "Emergency Contact Relationship",
            "Roles (comma separated, exclude admin/superadmin)",
        ];

        // Write headers to first row
        $col = "A";
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}1", $header);
            $col++;
        }

        // Auto-size columns
        foreach (range("A", $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Prepare for download
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), "participants_template_");
        $writer->save($tempFile);

        return response()
            ->download($tempFile, "participants_template.xlsx")
            ->deleteFileAfterSend(true);
    }

    public function update(
        UpdateEventParticipantRequest $request,
        Event $event,
        EventParticipant $participant,
    ) {
        if (Auth::user()->cannot("updateParticipants", Event::class)) {
            abort(403);
        }

        $validated = $request->validated();
        $participant->update($validated);

        // Handle role assignment
        if (isset($validated["roles"]) && is_array($validated["roles"])) {
            // Filter out admin/superadmin roles
            $allowedRoles = \App\Models\Role::whereNotIn("name", [
                "Super Admin",
                "Administrator",
            ])
                ->whereIn("id", $validated["roles"])
                ->pluck("id");

            $participant->roles()->sync($allowedRoles);
        }

        return back()->with("success", "Participant updated successfully.");
    }

    /**
     * Import participants from Excel file.
     */
    public function import(Request $request, Event $event): RedirectResponse
    {
        if (Auth::user()->cannot("importParticipants", Event::class)) {
            abort(403);
        }

        $request->validate([
            "file" => "required|mimes:xlsx,xls", // max 5 MB
        ]);

        try {
            $file = $request->file("file");
            $path = $file->getRealPath();

            // Load Excel using PhpSpreadsheet directly
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            // Remove header row
            unset($rows[1]);

            $importedCount = 0;

            DB::transaction(function () use ($rows, $event, &$importedCount) {
                foreach ($rows as $row) {
                    $first_name = trim($row["A"] ?? "");
                    $last_name = trim($row["B"] ?? "");
                    $email = trim($row["C"] ?? "");
                    $phone = trim($row["D"] ?? "");
                    $vehicle = trim($row["E"] ?? "");
                    $emergencyName = trim($row["G"] ?? "");
                    $emergencyRelation = trim($row["I"] ?? "");
                    $status = trim($row["F"] ?? "");

                    $participant = EventParticipant::create([
                        "event_id" => $event->id,
                        "first_name" => $first_name,
                        "last_name" => $last_name,
                        "email" => $email ?: null,
                        "phone" => $phone ?: null,
                        "vehicle" => $vehicle ?: null,
                        "emergency_contact_name" => $emergencyName ?: null,
                        "emergency_contact_relationship" =>
                            $emergencyRelation ?: null,
                        "status" => $status,
                    ]);

                    // Handle roles if present in the import
                    if (isset($row["J"]) && trim($row["J"])) {
                        $roleNames = explode(",", trim($row["J"]));
                        $roles = \App\Models\Role::whereIn("name", $roleNames)
                            ->whereNotIn("name", ["Super Admin", "Admin"])
                            ->pluck("id");

                        $participant->roles()->sync($roles);
                    }

                    $importedCount++;
                }
            });

            return back()->with(
                "success",
                "✅ Successfully imported {$importedCount} participants.",
            );
        } catch (\Throwable $e) {
            Log::error("❌ Participant import failed", [
                "error" => $e->getMessage(),
            ]);
            return back()->with(
                "error",
                "❌ Import failed. Please check the Excel file format.",
            );
        }
    }
}
