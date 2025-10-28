<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventDayDetail;
use App\Models\EventDayLocation;
use App\Models\EventDayResource;
use App\Models\EventSponsor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with('sponsors')
            ->latest()
            ->get();

        return view('pages.events.index', compact('events'));
    }

    public function create()
    {
        return view('pages.events.create');
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $event = Event::create([
                'title'            => $data['title'],
                'description'      => $data['description'],
                'start_date'       => $data['start_date'],
                'end_date'         => $data['end_date'],
                'max_participants' => $data['max_participants'],
            ]);

            // Days
            foreach (($data['days'] ?? []) as $i => $dayData) {
                $imagePath = null;

                // Handle file upload (if any) — note: input name is days[index][image]
                if ($request->hasFile("days.$i.image")) {
                    $imagePath = $request->file("days.$i.image")
                        ->store('events/days', 'public'); // => storage/app/public/events/days
                }

                $day = EventDay::create([
                    'event_id'   => $event->id,
                    'title'      => $dayData['title'],
                    'date'       => $dayData['date'],
                    'subtitle'   => $dayData['subtitle'] ?? null,
                    'image_path' => $imagePath,
                    'sort_order' => $i,
                ]);

                // Locations
                foreach (($dayData['locations'] ?? []) as $j => $loc) {
                    if (!empty($loc['name'])) {
                        EventDayLocation::create([
                            'event_day_id' => $day->id,
                            'name'         => $loc['name'],
                            'link_title'   => $loc['link_title'] ?? null,
                            'link_url'     => $loc['link_url'] ?? null,
                            'sort_order'   => $j,
                        ]);
                    }
                }

                // Details
                foreach (($dayData['details'] ?? []) as $k => $det) {
                    if (!empty($det['title']) || !empty($det['description'])) {
                        EventDayDetail::create([
                            'event_day_id' => $day->id,
                            'title'        => $det['title'] ?? '',
                            'description'  => $det['description'] ?? null,
                            'sort_order'   => $k,
                        ]);
                    }
                }

                // Resources
                foreach (($dayData['resources'] ?? []) as $r => $res) {
                    if (!empty($res['title']) || !empty($res['url'])) {
                        EventDayResource::create([
                            'event_day_id' => $day->id,
                            'title'        => $res['title'] ?? '',
                            'url'          => $res['url'] ?? '',
                            'sort_order'   => $r,
                        ]);
                    }
                }
            }

            // Sponsors
            foreach (($data['sponsors'] ?? []) as $s => $sponsor) {
                if (!empty($sponsor['name'])) {
                    EventSponsor::create([
                        'event_id'  => $event->id,
                        'name'      => $sponsor['name'],
                        'logo_url'  => $sponsor['logo_url'] ?? null,
                        'sort_order' => $s,
                    ]);
                }
            }
        });

        return redirect()
            ->route('events.create')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // Load all relationships in correct order
        $event->load([
            'days.locations',
            'days.details',
            'days.resources',
            'sponsors',
        ]);

        // Calculate duration
        $durationDays = \Carbon\Carbon::parse($event->start_date)
            ->diffInDays(\Carbon\Carbon::parse($event->end_date)) + 1;

        // Prepare days data for Alpine.js (just like before)
        $days = $event->days->map(function ($day) {
            return [
                'id'        => $day->id,
                'title'     => $day->title,
                'date'      => optional($day->date)->format('l d F Y'),
                'date_short' => optional($day->date)->format('d/m/Y'),
                'subtitle'  => $day->subtitle,
                'image'     => $day->image_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($day->image_path)
                    : null,
                'locations' => $day->locations->map(fn($l) => [
                    'name'       => $l->name,
                    'link_title' => $l->link_title,
                    'link_url'   => $l->link_url,
                ])->values(),
                'details'   => $day->details->map(fn($v) => [
                    'title'       => $v->title,
                    'description' => $v->description,
                ])->values(),
                'resources' => $day->resources->map(fn($r) => [
                    'title' => $r->title,
                    'url'   => $r->url,
                ])->values(),
            ];
        })->values();

        return view('pages.events.show', [
            'event'         => $event,
            'daysJson'      => $days->toJson(), // for Alpine.js
            'durationDays'  => $durationDays,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }
}
