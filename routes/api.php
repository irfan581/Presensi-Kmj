<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\KunjunganController;
use App\Http\Controllers\Api\IzinController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ─── PUBLIC (Tanpa Login) ───────────────────────────────────
Route::post('/login', [AttendanceController::class, 'login'])
    ->middleware('throttle:20,1')
    ->name('api.login');

// ─── PROTECTED (Wajib Login) ────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── NOTIFIKASI ────────────────────────────────────────
    Route::prefix('notifications')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/update-token', [NotificationController::class, 'updateToken'])->name('notifications.update-token');
});

    // ── AUTH ──────────────────────────────────────────────
    Route::post('/logout', [AttendanceController::class, 'logout'])->name('api.logout');

    // ── PROFIL ────────────────────────────────────────────
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile'])->name('api.profile.show');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('api.profile.update');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('api.profile.change-password');
    });

    // ── PRESENSI ──────────────────────────────────────────
    Route::middleware('throttle:40,1')->group(function () {
        Route::get('/presensi-hari-ini', [AttendanceController::class, 'presensiHariIni'])->name('api.absen.hari-ini');
        Route::post('/absen-masuk', [AttendanceController::class, 'absenMasuk'])->name('api.absen.masuk');
        Route::post('/absen-pulang', [AttendanceController::class, 'absenPulang'])->name('api.absen.pulang');
    });
    Route::get('/riwayat-presensi', [AttendanceController::class, 'riwayat'])->name('api.absen.riwayat');

    // ── KUNJUNGAN ─────────────────────────────────────────
    Route::post('/kunjungan-toko', [KunjunganController::class, 'store'])
        ->middleware('throttle:50,1')
        ->name('api.kunjungan.store');
    Route::get('/riwayat-kunjungan', [KunjunganController::class, 'history'])->name('api.kunjungan.riwayat');

    // ── IZIN ──────────────────────────────────────────────
    Route::prefix('izin')->group(function () {
        Route::post('/ajukan', [IzinController::class, 'ajukanIzin'])
            ->middleware('throttle:10,1')
            ->name('api.izin.ajukan');
        Route::get('/riwayat', [IzinController::class, 'riwayat'])->name('api.izin.riwayat');
        Route::get('/cek-hari-ini', [IzinController::class, 'cekIzinHariIni'])->name('api.izin.cek-hari-ini');
    });
});