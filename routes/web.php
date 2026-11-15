<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyJobController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KaryMatchAiController;
use App\Http\Controllers\KaryMatchSyncController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\SavedJobController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Publik
// Aplikasi utama: seluruh antarmuka dan alur fitur dari HTML referensi.
Route::view('/', 'karymatch-spa')->name('home');

// Penyimpanan server untuk data demo SPA. Dilindungi sesi + CSRF Laravel.
Route::middleware('throttle:180,1')->prefix('karymatch/sync')->name('karymatch.sync.')->group(function () {
    Route::get('/', [KaryMatchSyncController::class, 'index'])->name('index');
    Route::put('/{record}', [KaryMatchSyncController::class, 'upsert'])->where('record', '[A-Za-z0-9_~.-]+')->name('upsert');
});

// Perantara AI: kunci API tetap di server (.env), tidak pernah dikirim ke browser.
Route::post('/karymatch/ai', [KaryMatchAiController::class, 'complete'])->middleware('throttle:15,1')->name('karymatch.ai');

// Antarmuka Laravel CRUD yang sudah tersedia tetap dapat diakses terpisah.
Route::get('/laravel', [HomeController::class, 'index'])->name('laravel.home');
Route::get('/lowongan/{post}', [HomeController::class, 'show'])->name('jobs.show');

// Masuk dan daftar
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.post');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Pencari kerja
Route::middleware(['auth', RoleMiddleware::class.':pekerja'])->group(function () {
    Route::get('/cv', [CvController::class, 'edit'])->name('cv.edit');
    Route::put('/cv', [CvController::class, 'update'])->name('cv.update');

    Route::get('/lamaran', [ApplicationController::class, 'index'])->name('applications.index');
    Route::post('/lowongan/{post}/lamar', [ApplicationController::class, 'store'])->name('applications.store');
    Route::post('/lamaran/{application}/tarik', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
    Route::get('/lamaran/{application}/cv', [ApplicationController::class, 'cv'])->name('applications.cv');

    Route::get('/tersimpan', [SavedJobController::class, 'index'])->name('saved.index');
    Route::post('/lowongan/{post}/simpan', [SavedJobController::class, 'toggle'])->name('saved.toggle');
});

// Notifikasi (semua peran)
Route::get('/notifikasi', [NoticeController::class, 'index'])->middleware('auth')->name('notices.index');

// Perusahaan
Route::middleware(['auth', RoleMiddleware::class.':perusahaan'])
    ->prefix('perusahaan')->name('company.')->group(function () {
        Route::get('/', [CompanyJobController::class, 'dashboard'])->name('dashboard');
        Route::get('/lowongan/baru', [CompanyJobController::class, 'create'])->name('jobs.create');
        Route::post('/lowongan', [CompanyJobController::class, 'store'])->name('jobs.store');
        Route::get('/lowongan/{post}/edit', [CompanyJobController::class, 'edit'])->name('jobs.edit');
        Route::put('/lowongan/{post}', [CompanyJobController::class, 'update'])->name('jobs.update');
        Route::post('/lowongan/{post}/up-ulang', [CompanyJobController::class, 'renew'])->name('jobs.renew');
        Route::post('/lowongan/{post}/duplikat', [CompanyJobController::class, 'duplicate'])->name('jobs.duplicate');
        Route::post('/lowongan/{post}/tutup', [CompanyJobController::class, 'close'])->name('jobs.close');
    });

// Admin
Route::middleware(['auth', RoleMiddleware::class.':admin'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/perusahaan/{company}/setujui', [AdminController::class, 'approve'])->name('companies.approve');
        Route::post('/perusahaan/{company}/tolak', [AdminController::class, 'reject'])->name('companies.reject');
    });
