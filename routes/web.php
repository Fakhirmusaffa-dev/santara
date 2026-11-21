<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SensitiveTriageController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GuestComplaintController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->dashboardRoute())
        : view('landing');
})->name('home');

// Lapor tanpa login (publik)
Route::get('/lapor', [GuestComplaintController::class, 'create'])->name('guest.complaints.create');
Route::post('/lapor', [GuestComplaintController::class, 'store'])->name('guest.complaints.store')->middleware('throttle:5,1');
Route::get('/lapor/cek', [GuestComplaintController::class, 'track'])->name('guest.complaints.track')->middleware('throttle:20,1');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:login');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dashboard per role
    Route::get('/pelapor/dashboard', [DashboardController::class, 'pelapor'])->name('pelapor.dashboard')->middleware('role:pelapor');
    Route::get('/petugas/dashboard', [DashboardController::class, 'petugas'])->name('petugas.dashboard')->middleware('role:petugas,admin');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard')->middleware('role:admin');
    Route::get('/pimpinan/dashboard', [DashboardController::class, 'pimpinan'])->name('pimpinan.dashboard')->middleware('role:pimpinan,admin');

    // Pengaduan
    Route::get('/pengaduan', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/pengaduan/baru', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/pengaduan', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/pengaduan/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::get('/pengaduan/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::put('/pengaduan/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');

    Route::post('/pengaduan/{complaint}/verifikasi', [ComplaintController::class, 'verify'])->name('complaints.verify')->middleware('role:admin');
    Route::post('/pengaduan/{complaint}/tolak', [ComplaintController::class, 'reject'])->name('complaints.reject')->middleware('role:admin');
    Route::post('/pengaduan/{complaint}/tugaskan', [ComplaintController::class, 'assign'])->name('complaints.assign')->middleware('role:admin');
    // Perubahan status bebas hanya untuk admin; petugas/pimpinan lewat alur terkontrol
    // (terima, progres, selesai, persetujuan) yang tetap memeriksa policy.
    Route::post('/pengaduan/{complaint}/status', [ComplaintController::class, 'changeStatus'])->name('complaints.status')->middleware('role:admin');

    Route::post('/pengaduan/{complaint}/terima', [ComplaintController::class, 'acceptAssignment'])->name('complaints.accept')->middleware('role:petugas');
    Route::post('/pengaduan/{complaint}/progres', [ComplaintController::class, 'addHandlingLog'])->name('complaints.handling')->middleware('role:petugas,admin');
    Route::post('/pengaduan/{complaint}/selesai', [ComplaintController::class, 'submitCompletion'])->name('complaints.complete')->middleware('role:petugas');

    // ---------- Pengaduan (tambah: hapus) ----------
    Route::delete('/pengaduan/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');

    // Persetujuan pimpinan
    Route::get('/persetujuan', [ApprovalController::class, 'index'])->name('approvals.index')->middleware('role:admin,pimpinan');
    Route::post('/persetujuan/{approval}', [ApprovalController::class, 'decide'])->name('approvals.decide')->middleware('role:admin,pimpinan');
    Route::post('/pengaduan/{complaint}/akses-sensitif', [ApprovalController::class, 'grantSensitiveAccess'])->name('complaints.grant-access')->middleware('role:admin');

    // Lampiran privat
    Route::get('/lampiran/{photo}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/dokumen/{path}', [AttachmentController::class, 'handling'])->name('attachments.handling')->where('path', '.*');

    // Notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'read'])->name('notifications.read');

    // Feedback
    Route::post('/pengaduan/{complaint}/feedback', [FeedbackController::class, 'store'])->name('feedbacks.store');
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedbacks.index')->middleware('role:admin,pimpinan');

    // Laporan
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index')->middleware('role:admin,pimpinan');
    Route::get('/laporan/ekspor', [ReportController::class, 'export'])->name('reports.export')->middleware('role:admin,pimpinan');

    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/pengguna', [AdminController::class, 'users'])->name('users.index');
        Route::get('/pengguna/baru', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/pengguna', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/pengguna/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::get('/pengguna/{user}/ubah', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/pengguna/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/pengguna/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/pengguna/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');

        Route::get('/kategori', [AdminController::class, 'categories'])->name('categories.index');
        Route::post('/kategori', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::get('/kategori/{category}', [AdminController::class, 'showCategory'])->name('categories.show');
        Route::get('/kategori/{category}/ubah', [AdminController::class, 'editCategory'])->name('categories.edit');
        Route::put('/kategori/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/kategori/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/kategori/{category}/toggle', [AdminController::class, 'toggleCategory'])->name('categories.toggle');

        Route::get('/lokasi', [AdminController::class, 'locations'])->name('locations.index');
        Route::post('/lokasi', [AdminController::class, 'storeLocation'])->name('locations.store');
        Route::get('/lokasi/{location}', [AdminController::class, 'showLocation'])->name('locations.show');
        Route::get('/lokasi/{location}/ubah', [AdminController::class, 'editLocation'])->name('locations.edit');
        Route::put('/lokasi/{location}', [AdminController::class, 'updateLocation'])->name('locations.update');
        Route::delete('/lokasi/{location}', [AdminController::class, 'destroyLocation'])->name('locations.destroy');
        Route::post('/lokasi/{location}/toggle', [AdminController::class, 'toggleLocation'])->name('locations.toggle');

        Route::get('/monitoring', [AdminController::class, 'monitoring'])->name('monitoring');
        Route::get('/aktivitas', [AdminController::class, 'activities'])->name('activities');

        // Triase sensitif (need-to-know): metadata saja, isi tetap terkunci policy.
        Route::get('/sensitif-masuk', [SensitiveTriageController::class, 'index'])->name('sensitive.index');
    });
});
