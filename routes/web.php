<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware('auth')->group(function () {

    // App Routes
    Route::view('/', 'pages.dashboard')->name("dashboard");

    //Events
    Route::resource('events', EventController::class);

    // Event Participants 
    Route::prefix('events/{event}')->group(function () {
        Route::post('participants', [EventParticipantController::class, 'store'])->name('participants.store');
        Route::put('participants/{participant}', [EventParticipantController::class, 'update'])->name('participants.update');
        Route::delete('participants/{participant}', [EventParticipantController::class, 'destroy'])->name('participants.destroy');
        Route::post('participants/import', [EventParticipantController::class, 'import'])->name('participants.import');//todo
    });

    // Download Participant Template
    Route::get('/participants/template', [EventParticipantController::class, 'downloadTemplate'])
        ->name('participants.template')
        ->middleware('auth');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    Route::post('attachments', function (Request $request) {
        $request->validate([
            'attachment' => ['required', 'file'],
        ]);

        $path = $request->file('attachment')->store('attachments', 'public');

        return [
            'image_url' => '/storage/' . $path,
        ];
    })->name('attachments.store');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('roles', RoleController::class);
});


require __DIR__ . '/auth.php';
