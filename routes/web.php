<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LokasiController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');

// Event routes
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('dashboard');

// Category routes (admin)
Route::prefix('admin')->name('categories.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/categories', [DashboardController::class, 'index'])->name('index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('destroy');
});

// Event management routes (admin)
Route::prefix('admin')->name('admin.events.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('index');
    Route::get('/events/export', [EventController::class, 'export'])->name('export');
    Route::post('/events/bulk-delete', [EventController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::get('/events/create', [EventController::class, 'create'])->name('create');
    Route::post('/events', [EventController::class, 'store'])->name('store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('destroy');
    Route::post('/events/{event}/clone', [EventController::class, 'clone'])->name('clone');
});

// Location management routes (admin)
Route::prefix('admin')->name('admin.lokasi.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/lokasi', [LokasiController::class, 'index'])->name('index');
    Route::post('/lokasi', [LokasiController::class, 'store'])->name('store');
    Route::put('/lokasi/{id}', [LokasiController::class, 'update'])->name('update');
    Route::delete('/lokasi/{id}', [LokasiController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
