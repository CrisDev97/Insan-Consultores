<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Site\HomeController;
use \App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;

Route::get('/', [HomeController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ✅ BLOQUE QUE TE FALTA (Breeze lo necesita)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:administrador'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('banners', BannerController::class)->except(['show']);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}/toggle-read', [ContactMessageController::class, 'toggleRead'])->name('messages.toggleRead');
        Route::patch('messages/{message}/toggle-contacted', [ContactMessageController::class, 'toggleContacted'])->name('messages.toggleContacted');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
        Route::patch('messages/mark-all-read', [ContactMessageController::class, 'markAllRead'])->name('messages.markAllRead');

    });

Route::prefix('plataforma')->name('student.')->group(function () {

    // Login estudiante
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login.post');

    // Logout estudiante
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');

    // Plataforma (protegida)
    Route::middleware(['auth', 'role:estudiante'])->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
    });
});

require __DIR__.'/auth.php';
