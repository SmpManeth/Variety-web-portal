<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\JobImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JobImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Event $event)
    {
        if ($request->search) {
            $jobImages = $event
                ->jobImages()
                ->where("name", "LIKE", "%" . $request->search . "%");
        } else {
            $jobImages = $event->jobImages();
        }

        $jobImages = $jobImages->paginate(5);

        return view("pages.job-images.index", compact("event", "jobImages"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            "images" => "required|array",
            "images.*" => "required|file|mimes:jpeg,jpg,jpg,webp|max:4096",
        ]);

        if ($validator->fails()) {
            return back()->with("error", $validator->errors()->first());
        }

        // Get file details
        $files = $request->file("images");
        $originalNames = [];

        foreach ($files as $file) {
            $originalNames[] = $file->getClientOriginalName();
        }

        // Check for conflicting filenames
        $conflicts = $event
            ->jobImages()
            ->whereIn("name", $originalNames)
            ->pluck("name")
            ->toArray();

        if (!empty($conflicts)) {
            return back()->with(
                "error",
                "Duplicate filename(s): " . implode(", ", $conflicts),
            );
        }

        // Store the file
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs("/jobs/$event->id", $originalName, "public");
            $event->jobImages()->create([
                "name" => $originalName,
                "path" => $path,
            ]);
        }

        // Return back with success
        return back()->with("success", "Image(s) added succesfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, JobImage $jobImage)
    {
        if (!$event->jobImages()->where("id", $jobImage->id)->exists()) {
            return back()->with("error", "Image not found in event.");
        }

        Storage::delete($jobImage->path);

        $jobImage->delete();
        return back()->with("success", "Image deleted successfully.");
    }
}
