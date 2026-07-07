<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventDayLocation;
use App\Models\EventDayResource;
use App\Services\EventDeletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $withOrganizer = ["creator"];

        if ($user->hasRole("Super Admin")) {
            $events = Event::query()
                ->with($withOrganizer)
                ->withCount("participants")
                ->latest()
                ->get();
        } else {
            $events = $user
                ->events()
                ->with($withOrganizer)
                ->withCount("participants")
                ->latest()
                ->get();
        }

        return view("pages.events.index", compact("events"));
    }

    public function create()
    {
        if (Auth::user()->cannot("create", Event::class)) {
            abort(403);
        }

        return view("pages.events.create");
    }

    public function store(StoreEventRequest $request)
    {
        if (Auth::user()->cannot("create", Event::class)) {
            abort(403);
        }

        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $sponsor_image_path = $request->hasFile("sponsor_image")
                ? $request
                    ->file("sponsor_image")
                    ->store("events/sponsors", "public")
                : null;

            $cover_image_path = $request->hasFile("cover_image")
                ? $request
                    ->file("cover_image")
                    ->store("events/covers", "public")
                : null;

            $event = Event::create([
                "created_by" => $request->user()->id,
                "title" => $data["title"],
                "description" => $data["description"],
                "start_date" => $data["start_date"],
                "end_date" => $data["end_date"],
                "sponsor_image_path" => $sponsor_image_path,
                "cover_image_path" => $cover_image_path,
            ]);

            // Add user as an admin of the event
            $event->admins()->syncWithoutDetaching($request->user()->id);

            $user = Auth::user();
            $user->assignedEvents()->sync($event->id ?? []);

            // Days
            foreach ($data["days"] ?? [] as $i => $dayData) {
                $imagePath = null;

                // Handle file upload (if any) — note: input name is days[index][image]
                if ($request->hasFile("days.$i.image")) {
                    $imagePath = $request
                        ->file("days.$i.image")
                        ->store("events/days", "public"); // => storage/app/public/events/days
                }

                $day = EventDay::create([
                    "event_id" => $event->id,
                    "title" => $dayData["title"],
                    "date" => $dayData["date"],
                    "subtitle" => $dayData["subtitle"] ?? null,
                    "image_path" => $imagePath,
                    "sort_order" => $i,
                    "itinerary_title" => $dayData["itinerary_title"] ?? "",
                    "itinerary_description" =>
                        $dayData["itinerary_description"] ?? "",
                ]);

                // Locations
                foreach ($dayData["locations"] ?? [] as $j => $loc) {
                    if (!empty($loc["name"])) {
                        EventDayLocation::create([
                            "event_day_id" => $day->id,
                            "name" => $loc["name"],
                            "link_title" => $loc["link_title"] ?? null,
                            "link_url" => $loc["link_url"] ?? null,
                            "sort_order" => $j,
                        ]);
                    }
                }

                // Resources
                foreach ($dayData["resources"] ?? [] as $r => $res) {
                    if (!empty($res["title"]) || !empty($res["url"])) {
                        EventDayResource::create([
                            "event_day_id" => $day->id,
                            "title" => $res["title"] ?? "",
                            "url" => $res["url"] ?? "",
                            "sort_order" => $r,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route("events.create")
            ->with("success", "Event created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        if (Auth::user()->cannot("view", $event)) {
            abort(403);
        }

        // Load all relationships in correct order
        $event->load(["days.locations", "days.resources", "creator"]);

        // Calculate duration
        $durationDays =
            \Carbon\Carbon::parse($event->start_date)->diffInDays(
                \Carbon\Carbon::parse($event->end_date),
            ) + 1;

        // Prepare days data for Alpine.js
        $days = $event->days
            ->map(
                fn($day) => [
                    "id" => $day->id,
                    "title" => $day->title,
                    "date" => $day->date
                        ? \Carbon\Carbon::parse($day->date)->format("l d F Y")
                        : null,
                    "date_short" => $day->date
                        ? \Carbon\Carbon::parse($day->date)->format("d/m/Y")
                        : null,
                    "subtitle" => $day->subtitle,
                    "itinerary_title" => $day->itinerary_title,
                    // "itinerary_description" => $day->itinerary_description,
                    "itinerary_description" => $day->itinerary_description->render(),
                    "image" => $day->image_path,
                    "locations" => $day->locations
                        ->map(
                            fn($l) => [
                                "name" => $l->name,
                                "link_title" => $l->link_title,
                                "link_url" => $l->link_url,
                            ],
                        )
                        ->values(),
                    "resources" => $day->resources
                        ->map(
                            fn($r) => [
                                "title" => $r->title,
                                "url" => $r->url,
                            ],
                        )
                        ->values(),
                ],
            )
            ->values();

        $daysJson = $days->toJson();

        $participantsJson = $event
            ->participants()
            ->with("roles")
            ->get()
            ->map(function ($p) {
                // Appends a readable dynamic role CSV string to feed search logic natively
                $p->role_names = $p->roles->pluck("name")->implode(", ");
                return $p;
            })
            ->toJson();

        return view(
            "pages.events.show",
            compact("event", "daysJson", "participantsJson", "durationDays"),
        );
    }

    public function edit(Event $event)
    {
        if (Auth::user()->cannot("update", $event)) {
            abort(403);
        }

        $event->load(["days.locations", "days.resources"]);

        // Pre-format days for Alpine (include IDs + existing image URL)
        $days = $event->days
            ->map(
                fn($day) => [
                    "id" => $day->id,
                    "title" => $day->title,
                    "date" => $day->date,
                    "subtitle" => $day->subtitle,
                    // Relative URL so images work regardless of APP_URL / domain (same as show page & cover preview)
                    "image_url" => $day->image_path
                        ? "/storage/" . ltrim($day->image_path, "/")
                        : null,
                    "remove_image" => false,
                    "sort_order" => $day->sort_order ?? 0,
                    "itinerary_title" => $day->itinerary_title,
                    "itinerary_description" => $day->itinerary_description->render(),

                    "locations" => $day->locations
                        ->map(
                            fn($l) => [
                                "id" => $l->id,
                                "name" => $l->name,
                                "link_title" => $l->link_title,
                                "link_url" => $l->link_url,
                                "sort_order" => $l->sort_order ?? 0,
                            ],
                        )
                        ->values(),

                    "resources" => $day->resources
                        ->map(
                            fn($r) => [
                                "id" => $r->id,
                                "title" => $r->title,
                                "url" => $r->url,
                                "sort_order" => $r->sort_order ?? 0,
                            ],
                        )
                        ->values(),
                ],
            )
            ->values();

        return view("pages.events.edit", [
            "event" => $event,
            "daysJson" => $days->values()->all(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $event) {
            if ($request->hasFile("sponsor_image")) {
                $sponsor_image_path = $request
                    ->file("sponsor_image")
                    ->store("events/sponsors", "public");
                if ($event->sponsor_image_path) {
                    Storage::disk("public")->delete($event->sponsor_image_path);
                }
            }

            if ($request->hasFile("cover_image")) {
                $cover_image_path = $request
                    ->file("cover_image")
                    ->store("events/covers", "public");
                if ($event->cover_image_path) {
                    Storage::disk("public")->delete($event->cover_image_path);
                }
            }

            // 1) Update Event main fields
            $event->update([
                "title" => $data["title"],
                "description" => $data["description"],
                "start_date" => $data["start_date"],
                "end_date" => $data["end_date"],
                "sponsor_image_path" =>
                    $sponsor_image_path ?? ($event->sponsor_image_path ?? null),
                "cover_image_path" =>
                    $cover_image_path ?? ($event->cover_image_path ?? null),
            ]);

            // Track IDs to keep (for diff-delete)
            $keepDayIds = [];
            $keepLocationIds = [];
            $keepResourceIds = [];

            // 2) Upsert Days + nested children
            foreach ($data["days"] ?? [] as $i => $dayData) {
                $dayAttrs = [
                    "event_id" => $event->id,
                    "title" => $dayData["title"] ?? "",
                    "date" => $dayData["date"] ?? null,
                    "subtitle" => $dayData["subtitle"] ?? null,
                    "sort_order" => $dayData["sort_order"] ?? $i,
                    "itinerary_title" => $dayData["itinerary_title"] ?? "",
                    "itinerary_description" =>
                        $dayData["itinerary_description"] ?? "",
                ];

                if (!empty($dayData["id"])) {
                    // Update existing Day
                    /** @var \App\Models\EventDay $day */
                    $day = EventDay::where("event_id", $event->id)
                        ->where("id", $dayData["id"])
                        ->firstOrFail();

                    // Handle image delete/replace
                    if (!empty($dayData["remove_image"])) {
                        if ($day->image_path) {
                            Storage::disk("public")->delete($day->image_path);
                        }
                        $dayAttrs["image_path"] = null;
                    }

                    if ($request->hasFile("days.$i.image")) {
                        if ($day->image_path) {
                            Storage::disk("public")->delete($day->image_path);
                        }
                        $dayAttrs["image_path"] = $request
                            ->file("days.$i.image")
                            ->store("events/days", "public");
                    }

                    $day->update($dayAttrs);
                } else {
                    // Create new Day
                    if ($request->hasFile("days.$i.image")) {
                        $dayAttrs["image_path"] = $request
                            ->file("days.$i.image")
                            ->store("events/days", "public");
                    }
                    $day = EventDay::create($dayAttrs);
                }

                $keepDayIds[] = $day->id;

                // Locations
                foreach ($dayData["locations"] ?? [] as $j => $loc) {
                    if (
                        empty($loc["name"]) &&
                        empty($loc["link_title"]) &&
                        empty($loc["link_url"])
                    ) {
                        continue;
                    }

                    $locAttrs = [
                        "event_day_id" => $day->id,
                        "name" => $loc["name"] ?? "",
                        "link_title" => $loc["link_title"] ?? null,
                        "link_url" => $loc["link_url"] ?? null,
                        "sort_order" => $loc["sort_order"] ?? $j,
                    ];

                    if (!empty($loc["id"])) {
                        $location = EventDayLocation::where(
                            "event_day_id",
                            $day->id,
                        )
                            ->where("id", $loc["id"])
                            ->firstOrFail();
                        $location->update($locAttrs);
                    } else {
                        $location = EventDayLocation::create($locAttrs);
                    }

                    $keepLocationIds[] = $location->id;
                }

                // Resources
                foreach ($dayData["resources"] ?? [] as $r => $res) {
                    if (empty($res["title"]) && empty($res["url"])) {
                        continue;
                    }

                    $resAttrs = [
                        "event_day_id" => $day->id,
                        "title" => $res["title"] ?? "",
                        "url" => $res["url"] ?? null,
                        "sort_order" => $res["sort_order"] ?? $r,
                    ];

                    if (!empty($res["id"])) {
                        $resource = EventDayResource::where(
                            "event_day_id",
                            $day->id,
                        )
                            ->where("id", $res["id"])
                            ->firstOrFail();
                        $resource->update($resAttrs);
                    } else {
                        $resource = EventDayResource::create($resAttrs);
                    }

                    $keepResourceIds[] = $resource->id;
                }
            }

            // 4) Diff-delete removed items
            // Days not in $keepDayIds
            EventDay::where("event_id", $event->id)
                ->whereNotIn("id", $keepDayIds ?: [0])
                ->get()
                ->each(function ($day) {
                    // deleting a Day cascades delete its children via model boot() if you added that,
                    // else delete children explicitly:
                    $day->locations()->delete();
                    $day->resources()->delete();
                    if ($day->image_path) {
                        Storage::disk("public")->delete($day->image_path);
                    }
                    $day->delete();
                });

            // Children not in keep arrays
            if (!empty($keepDayIds)) {
                EventDayLocation::whereIn("event_day_id", $keepDayIds)
                    ->whereNotIn("id", $keepLocationIds ?: [0])
                    ->delete();
                EventDayResource::whereIn("event_day_id", $keepDayIds)
                    ->whereNotIn("id", $keepResourceIds ?: [0])
                    ->delete();
            }
        });

        return redirect()
            ->route("events.show", $event)
            ->with("success", "Event updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, EventDeletionService $deleter)
    {
        if (Auth::user()->cannot("delete", $event)) {
            abort(403);
        }

        try {
            DB::transaction(fn() => $deleter->delete($event));

            return redirect()
                ->route("events.index")
                ->with(
                    "success",
                    "Event and all related data deleted successfully.",
                );
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                "delete" => "Failed to delete event. Please try again.",
            ]);
        }
    }

    /**
     * Get events as JSON for API calls
     */
    public function list()
    {
        $user = Auth::user();

        if ($user->hasRole("Super Admin")) {
            $events = Event::latest()->get(["id", "name"]);
        } else {
            $events = $user
                ->events()
                ->latest()
                ->get(["id", "name"]);
        }

        return response()->json($events);
    }
}
