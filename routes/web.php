<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;


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
});


Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::resource('roles', RoleController::class);
});


require __DIR__ . '/auth.php';
