<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Isi hanya blok milikmu agar tidak bentrok saat merge.

// ============ PUBLIK & AUTH — Husein (HSM) ============
Route::view('/', 'landing')->name('landing');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ============ PROFIL (user & admin) — Husein (HSM) ============
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// ============ JADWAL RUANGAN (user) — Aulia (AL) ============
Route::middleware('auth')->group(function () {
    // Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');
});

// ============ PENGAJUAN (user) — Reno (RS) ============
Route::middleware('auth')->group(function () {
    // Route::get('/beranda', [BookingController::class, 'home'])->name('home');
    // Route::get('/pengajuan/buat', [BookingController::class, 'create'])->name('bookings.create');
    // Route::post('/pengajuan', [BookingController::class, 'store'])->name('bookings.store');
    // Route::get('/pengajuan', [BookingController::class, 'index'])->name('bookings.index');
});

// ============ ADMIN ============
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // --- Kelola Ruangan — Aulia (AL) ---
    // Route::resource('ruangan', RoomController::class)->except('show')->parameters(['ruangan' => 'room']);

// --- Dashboard & Verifikasi — Fata (FI) ---
    Route::get('/', [VerificationController::class, 'dashboard'])->name('dashboard');
    Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verifications.index');
    Route::patch('/verifikasi/{booking}', [VerificationController::class, 'update'])->name('verifications.update');
});
