<?php

use App\Http\Controllers\EventAdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventFormController;
use App\Http\Controllers\EventJobController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\EventPermitController;
use App\Http\Controllers\MedicalRecordCollectionController;
use App\Http\Controllers\MedicalRecordCommentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicalRecordImageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PermissionController;
use App\Models\Event;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use Illuminate\Http\Request;

Route::middleware("auth")->group(function () {
    // App Routes
    // Route::view("/", "pages.dashboard")->name("dashboard");
    Route::redirect("/", "/events")->name("dashboard");

    // Jobs
    Route::get("/jobs", [EventJobController::class, "index"])->name(
        "jobs.index",
    );

    Route::post("/jobs/", [EventJobController::class, "store"])->name(
        "jobs.store",
    );
    //to download template
    Route::get("/jobs/template", [
        EventJobController::class,
        "downloadTemplate",
    ])->name("jobs.template");

    Route::get("/jobs/{event}", [EventJobController::class, "show"])->name(
        "jobs.view",
    );

    Route::delete("/jobs/{event}", [
        EventJobController::class,
        "destroy",
    ])->name("jobs.destroy");

    Route::get("/job/{job}/edit", [EventJobController::class, "edit"])->name(
        "jobs.edit",
    );
    Route::put("/job/{job}", [EventJobController::class, "update"])->name(
        "jobs.update",
    );
    Route::post("/job/{job}/upload-image", [
        EventJobController::class,
        "uploadImage",
    ])->name("jobs.upload-image");
    //Events
    Route::resource("events", EventController::class);
    Route::resource("events.admins", EventAdminController::class)->only(
        "index",
        "store",
        "destroy",
    );

    // Event Participants
    Route::prefix("events/{event}")->group(function () {
        Route::get("participantsAjax", [
            EventParticipantController::class,
            "indexAjax",
        ])->name("participants.indexAjax");
        Route::post("participants", [
            EventParticipantController::class,
            "store",
        ])->name("participants.store");
        Route::delete("participants", [
            EventParticipantController::class,
            "bulkDestroy",
        ])->name("participants.bulkDestroy");
        Route::put("participants/{participant}", [
            EventParticipantController::class,
            "update",
        ])->name("participants.update");
        Route::delete("participants/{participant}", [
            EventParticipantController::class,
            "destroy",
        ])->name("participants.destroy");
        Route::post("participants/import", [
            EventParticipantController::class,
            "import",
        ])->name("participants.import"); //todo
    });

    // Download Participant Template
    Route::get("/participants/template", [
        EventParticipantController::class,
        "downloadTemplate",
    ])
        ->name("participants.template")
        ->middleware("auth");

    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit",
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update",
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy",
    );

    Route::resource("users", UserController::class);
    Route::resource("roles", RoleController::class);
    Route::resource("permissions", PermissionController::class);
    Route::get("events/list", [EventController::class, "list"])->name(
        "events.list",
    );

    //to download template
    Route::get("/medical-records/template", [
        MedicalRecordController::class,
        "downloadTemplate",
    ])->name("medical-records.template");

    Route::resource("medical-records", MedicalRecordController::class);

    Route::post("medical-records/import", [
        MedicalRecordController::class,
        "import",
    ])->name("medical-records.import");

    Route::get("medical-records/{event}/{record}", [
        MedicalRecordController::class,
        "showRecord",
    ])->name("medical-records.show-record");

    Route::get("medical-records/{event}/{record}/edit", [
        MedicalRecordController::class,
        "editRecord",
    ])->name("medical-records.edit-record");

    Route::put("medical-records/{event}/{record}", [
        MedicalRecordController::class,
        "updateRecord",
    ])->name("medical-records.update-record");

    Route::resource(
        "medical-record-comments",
        MedicalRecordCommentController::class,
    )->only(["store", "destroy"]);

    Route::resource(
        "medical-record-images",
        MedicalRecordImageController::class,
    )->only(["store", "destroy"]);

    Route::resource("events.passwords", PasswordController::class)->only(
        "index",
        "update",
    );
    Route::resource("events.forms", EventFormController::class);
    Route::resource("events.permits", EventPermitController::class);
    Route::resource("notifications", NotificationController::class)->only(
        "index",
        "store",
        "update",
        "destroy",
    );
    Route::post("/notifications/import", [
        NotificationController::class,
        "import",
    ])->name("notifications.import");

    Route::post("attachments", function (Request $request) {
        $request->validate([
            "attachment" => ["required", "file"],
        ]);

        $path = $request->file("attachment")->store("attachments", "public");

        return [
            "image_url" => "/storage/" . $path,
        ];
    })->name("attachments.store");

    // Settings Routes
    Route::get("/settings", [SettingsController::class, "show"])->name(
        "settings.show",
    );
    Route::post("/settings", [SettingsController::class, "store"])->name(
        "settings.store",
    );
});

Route::middleware(["auth"])->group(function () {
    Route::resource("roles", RoleController::class);
});

require __DIR__ . "/auth.php";
