<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\CaseNoteController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CounselorController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::resource('students', StudentController::class)->except(['destroy']);

    // Counselors
    Route::resource('counselors', CounselorController::class)->except(['destroy']);

    // Appointments
    Route::resource('appointments', AppointmentController::class)->except(['edit', 'update', 'destroy']);
    Route::post('appointments/{appointment}/approve', [AppointmentController::class, 'approve'])->name('appointments.approve');
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/start-session', [AppointmentController::class, 'startSession'])->name('appointments.start-session');

    // Sessions
    Route::resource('sessions', SessionController::class)->only(['index', 'show']);
    Route::post('sessions/{session}/complete', [SessionController::class, 'complete'])->name('sessions.complete');

    // Case Notes
    Route::resource('case-notes', CaseNoteController::class)->except(['destroy']);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Settings
    Route::get('settings', function () {
        if (!auth()->user()->isSystemAdmin()) abort(403);
        return view('settings.index');
    })->name('settings.index');

    // Notifications
    Route::post('notifications/{notif}/read', function (\App\Models\Notification $notif) {
        if ($notif->user_id === auth()->id()) {
            $notif->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::post('notifications/read-all', function () {
        auth()->user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('notifications.read-all');
});