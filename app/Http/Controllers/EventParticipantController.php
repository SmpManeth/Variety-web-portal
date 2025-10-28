<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventParticipantRequest;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Services\EventParticipantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;

final class EventParticipantController extends Controller
{
    public function store(StoreEventParticipantRequest $request,Event $event,EventParticipantService $service): RedirectResponse {
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
            'Age',
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
}
