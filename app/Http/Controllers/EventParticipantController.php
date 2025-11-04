<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventParticipantRequest;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Services\EventParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class EventParticipantController extends Controller
{
    public function store(StoreEventParticipantRequest $request, Event $event, EventParticipantService $service): RedirectResponse
    {

        $service->create($event, $request->validated());

        return back()->with('success', 'Participant added successfully.');
    }

    public function destroy(Event $event, EventParticipant $participant): RedirectResponse
    {
        abort_unless($participant->event_id === $event->id, 403);

        $participant->delete();

        return back()->with('success', 'Participant deleted.');
    }


    public function downloadTemplate()
    {
        // Create new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Participants Template');

        // Define header columns
        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Vehicle',
            'Status (active/inactive)',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Emergency Contact Relationship'
        ];

        // Write headers to first row
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}1", $header);
            $col++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Prepare for download
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'participants_template_');
        $writer->save($tempFile);

        return response()->download($tempFile, 'participants_template.xlsx')->deleteFileAfterSend(true);
    }

    public function update(Request $request, Event $event, EventParticipant $participant)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vehicle' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:255',
        ]);

        $participant->update($validated);

        return back()->with('success', 'Participant updated successfully.');
    }
    /**
     * Import participants from Excel file.
     */
    public function import(Request $request, Event $event): RedirectResponse
    {

        $request->validate([
            'file' => 'required|mimes:xlsx,xls', // max 5 MB
        ]);

        try {
            $file = $request->file('file');
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
                    $first_name = trim($row['A'] ?? '');
                    $last_name = trim($row['B'] ?? '');
                    $email = trim($row['C'] ?? '');
                    $phone = trim($row['D'] ?? '');
                    $vehicle = trim($row['E'] ?? '');
                    $emergencyName = trim($row['G'] ?? '');
                    $emergencyRelation = trim($row['I'] ?? '');
                    $status = trim($row['F'] ?? '');

                

                    EventParticipant::create([
                        'event_id' => $event->id,
                        'first_name' => $first_name,
                        'last_name'=> $last_name,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                        'vehicle' => $vehicle ?: null,
                        'emergency_contact_name' => $emergencyName ?: null,
                        'emergency_contact_relationship' => $emergencyRelation ?: null,
                        'status' => $status,
                    ]);

                    $importedCount++;
                }
            });

            return back()->with('success', "✅ Successfully imported {$importedCount} participants.");
        } catch (\Throwable $e) {
            Log::error('❌ Participant import failed', ['error' => $e->getMessage()]);
            return back()->with('error', '❌ Import failed. Please check the Excel file format.');
        }
    }
}
