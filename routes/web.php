<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ContactController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ContactMessageController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\Student\AgendaController;
use App\Http\Controllers\Admin\AdvisorController;
use App\Http\Controllers\Admin\AdvisorAvailabilityController;
use \App\Http\Controllers\Admin\AdvisorEventController;
use App\Http\Controllers\Admin\AdminAppointmentCalendarController;

Route::get('/', [HomeController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeze lo necesita
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===============================
// ADMIN (protegido)
// ===============================
Route::middleware(['auth', 'role:administrador'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('banners', BannerController::class)->except(['show']);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('events', AdvisorEventController::class)->except(['show']);

        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}/toggle-read', [ContactMessageController::class, 'toggleRead'])->name('messages.toggleRead');
        Route::patch('messages/{message}/toggle-contacted', [ContactMessageController::class, 'toggleContacted'])->name('messages.toggleContacted');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
        Route::patch('messages/mark-all-read', [ContactMessageController::class, 'markAllRead'])->name('messages.markAllRead');
        Route::resource('advisors', AdvisorController::class)->except(['show']);
        Route::get('advisors/{advisor}/availability', [AdvisorAvailabilityController::class, 'edit'])->name('advisors.availability.edit');
        Route::post('advisors/{advisor}/availability', [AdvisorAvailabilityController::class, 'update'])->name('advisors.availability.update');
        Route::get('appointments/calendar', [AdminAppointmentCalendarController::class, 'index'])->name('appointments.calendar');
        Route::get('appointments/calendar/feed', [AdminAppointmentCalendarController::class, 'feed'])->name('appointments.calendar.feed');
        Route::patch('appointments/{appointment}/status', [AdminAppointmentCalendarController::class, 'updateStatus'])->name('appointments.status');
        Route::patch('appointments/{appointment}/payment', [AdminAppointmentCalendarController::class, 'updatePayment'])->name('appointments.payment');
        Route::get('appointments/{appointment}/payments', [AdminAppointmentCalendarController::class, 'payments'])->name('appointments.payments');
    });

// ===============================
// ESTUDIANTE (protegido)
// ===============================
Route::middleware(['auth', 'role:estudiante'])
    ->prefix('plataforma')
    ->as('student.')
    ->group(function () {

        Route::view('/', 'student.dashboard')->name('dashboard');

        // AGENDA
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');
        Route::get('/agenda/advisors', [AgendaController::class, 'advisors'])->name('agenda.advisors');
        Route::get('/agenda/sessions', [AgendaController::class, 'sessions'])->name('agenda.sessions');
        Route::get('/agenda/slots', [AgendaController::class, 'slots'])->name('agenda.slots');
        Route::post('/agenda/book', [AgendaController::class, 'book'])->name('agenda.book');
        Route::get('/agenda/feed', [AgendaController::class, 'feed'])->name('agenda.feed');

        // OTRAS SECCIONES
        Route::view('/mis-citas', 'student.appointments')->name('appointments');
        Route::view('/servicios', 'student.services')->name('services');
        Route::view('/perfil', 'student.profile')->name('profile');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });

require __DIR__ . '/auth.php';