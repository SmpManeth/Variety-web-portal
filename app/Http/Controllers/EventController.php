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
use App\Services\EventDeletionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::latest()
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
            $sponsor_image_path = $request->hasFile("sponsor_image")
                ? $request->file("sponsor_image")->store('events/sponsors', 'public')
                : null;

            $event = Event::create([
                'title'              => $data['title'],
                'description'        => $data['description'],
                'start_date'         => $data['start_date'],
                'end_date'           => $data['end_date'],
                'sponsor_image_path' => $sponsor_image_path
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
                    'itinerary_title' => $dayData['itinerary_title'] ?? '',
                    'itinerary_description' => $dayData['itinerary_description'] ?? '',
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
            'days.resources'
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
                'itinerary_title'  => $day->itinerary_title,
                'itinerary_description'  => $day->itinerary_description->render(),
                'subtitle'  => $day->subtitle,
                'image'     => $day->image_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($day->image_path)
                    : null,
                'locations' => $day->locations->map(fn($l) => [
                    'name'       => $l->name,
                    'link_title' => $l->link_title,
                    'link_url'   => $l->link_url,
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


    public function edit(Event $event)
    {
        $event->load([
            'days.locations',
            'days.resources',
        ]);

        // Pre-format days for Alpine (include IDs + existing image URL)
        $days = $event->days->map(function ($day) {
            return [
                'id'         => $day->id,
                'title'      => $day->title,
                'date'       => $day->date ,
                'subtitle'   => $day->subtitle,
                'image_url'  => $day->image_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($day->image_path)
                    : null,
                'remove_image' => false,
                'sort_order' => $day->sort_order ?? 0,
                'itinerary_title' => $day->itinerary_title,
                'itinerary_description' => $day->itinerary_description->render(),

                'locations'  => $day->locations->map(fn($l) => [
                    'id'         => $l->id,
                    'name'       => $l->name,
                    'link_title' => $l->link_title,
                    'link_url'   => $l->link_url,
                    'sort_order' => $l->sort_order ?? 0,
                ])->values(),

                'details'    => $day->details->map(fn($d) => [
                    'id'          => $d->id,
                    'title'       => $d->title,
                    'description' => $d->description,
                    'sort_order'  => $d->sort_order ?? 0,
                ])->values(),

                'resources'  => $day->resources->map(fn($r) => [
                    'id'         => $r->id,
                    'title'      => $r->title,
                    'url'        => $r->url,
                    'sort_order' => $r->sort_order ?? 0,
                ])->values(),
            ];
        })->values();

        return view('pages.events.edit', [
            'event'        => $event,
            'daysJson'     => $days->toJson(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->validated();

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request, $event) {

            if($request->hasFile("sponsor_image")){
                $sponsor_image_path = $request->file("sponsor_image")->store('events/sponsors', 'public');
                Storage::disk('public')->delete($event->sponsor_image_path);
            }

            // 1) Update Event main fields
            $event->update([
                'title'              => $data['title'],
                'description'        => $data['description'],
                'start_date'         => $data['start_date'],
                'end_date'           => $data['end_date'],
                'sponsor_image_path' => $sponsor_image_path ?? $event->sponsor_image_path ?? null
            ]);

            // Track IDs to keep (for diff-delete)
            $keepDayIds        = [];
            $keepLocationIds   = [];
            $keepResourceIds   = [];

            // 2) Upsert Days + nested children
            foreach (($data['days'] ?? []) as $i => $dayData) {
                $dayAttrs = [
                    'event_id'   => $event->id,
                    'title'      => $dayData['title'] ?? '',
                    'date'       => $dayData['date'] ?? null,
                    'subtitle'   => $dayData['subtitle'] ?? null,
                    'sort_order' => $dayData['sort_order'] ?? $i,
                    'itinerary_title' => $dayData['itinerary_title'] ?? '',
                    'itinerary_description' => $dayData['itinerary_description'] ?? '',
                ];

                if (!empty($dayData['id'])) {
                    // Update existing Day
                    /** @var \App\Models\EventDay $day */
                    $day = \App\Models\EventDay::where('event_id', $event->id)
                        ->where('id', $dayData['id'])
                        ->firstOrFail();

                    // Handle image delete/replace
                    if (!empty($dayData['remove_image'])) {
                        if ($day->image_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($day->image_path);
                        }
                        $dayAttrs['image_path'] = null;
                    }

                    if ($request->hasFile("days.$i.image")) {
                        if ($day->image_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($day->image_path);
                        }
                        $dayAttrs['image_path'] = $request->file("days.$i.image")
                            ->store('events/days', 'public');
                    }

                    $day->update($dayAttrs);
                } else {
                    // Create new Day
                    if ($request->hasFile("days.$i.image")) {
                        $dayAttrs['image_path'] = $request->file("days.$i.image")
                            ->store('events/days', 'public');
                    }
                    $day = \App\Models\EventDay::create($dayAttrs);
                }

                $keepDayIds[] = $day->id;

                // Locations
                foreach (($dayData['locations'] ?? []) as $j => $loc) {
                    if (empty($loc['name']) && empty($loc['link_title']) && empty($loc['link_url'])) {
                        continue;
                    }

                    $locAttrs = [
                        'event_day_id' => $day->id,
                        'name'         => $loc['name'] ?? '',
                        'link_title'   => $loc['link_title'] ?? null,
                        'link_url'     => $loc['link_url'] ?? null,
                        'sort_order'   => $loc['sort_order'] ?? $j,
                    ];

                    if (!empty($loc['id'])) {
                        $location = \App\Models\EventDayLocation::where('event_day_id', $day->id)
                            ->where('id', $loc['id'])->firstOrFail();
                        $location->update($locAttrs);
                    } else {
                        $location = \App\Models\EventDayLocation::create($locAttrs);
                    }

                    $keepLocationIds[] = $location->id;
                }

                // Resources
                foreach (($dayData['resources'] ?? []) as $r => $res) {
                    if (empty($res['title']) && empty($res['url'])) {
                        continue;
                    }

                    $resAttrs = [
                        'event_day_id' => $day->id,
                        'title'        => $res['title'] ?? '',
                        'url'          => $res['url'] ?? null,
                        'sort_order'   => $res['sort_order'] ?? $r,
                    ];

                    if (!empty($res['id'])) {
                        $resource = \App\Models\EventDayResource::where('event_day_id', $day->id)
                            ->where('id', $res['id'])->firstOrFail();
                        $resource->update($resAttrs);
                    } else {
                        $resource = \App\Models\EventDayResource::create($resAttrs);
                    }

                    $keepResourceIds[] = $resource->id;
                }
            }

            // 4) Diff-delete removed items
            // Days not in $keepDayIds
            \App\Models\EventDay::where('event_id', $event->id)
                ->whereNotIn('id', $keepDayIds ?: [0])
                ->get()->each(function ($day) {
                    // deleting a Day cascades delete its children via model boot() if you added that,
                    // else delete children explicitly:
                    $day->locations()->delete();
                    $day->details()->delete();
                    $day->resources()->delete();
                    if ($day->image_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($day->image_path);
                    }
                    $day->delete();
                });

            // Children not in keep arrays
            if (!empty($keepDayIds)) {
                \App\Models\EventDayLocation::whereIn('event_day_id', $keepDayIds)
                    ->whereNotIn('id', $keepLocationIds ?: [0])->delete();
                \App\Models\EventDayResource::whereIn('event_day_id', $keepDayIds)
                    ->whereNotIn('id', $keepResourceIds ?: [0])->delete();
            }
        });

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, EventDeletionService $deleter)
    {
        try {
            DB::transaction(fn() => $deleter->delete($event));
    
            return redirect()
                ->route('events.index')
                ->with('success', 'Event and all related data deleted successfully.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['delete' => 'Failed to delete event. Please try again.']);
        }
    }
}
