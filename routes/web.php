<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Models\CPB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CPBController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard & Home
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    });

    // CPB Routes
    Route::prefix('cpb')->name('cpb.')->group(function () {
        Route::get('/', [CPBController::class, 'index'])->name('index');
        Route::get('/create', [CPBController::class, 'create'])->name('create');
        Route::post('/', [CPBController::class, 'store'])->name('store');
        Route::get('/{cpb}', [CPBController::class, 'show'])->name('show');
        Route::get('/{cpb}/edit', [CPBController::class, 'edit'])->name('edit');
        Route::put('/{cpb}', [CPBController::class, 'update'])->name('update');
        Route::delete('/{cpb}', [CPBController::class, 'destroy'])->name('destroy');
        
        
        // Export PDF
        Route::get('/export-pdf', [CPBController::class, 'exportPdf'])->name('export-pdf');
        
        // Handover routes (Validasi dokumen dilakukan di handoverForm)
        Route::get('/{cpb}/handover', [CPBController::class, 'handoverForm'])->name('handover.form');
        Route::post('/{cpb}/handover', [CPBController::class, 'handover'])->name('handover.store');
        
        // Rework / Reject
        Route::post('/{cpb}/reject', [CPBController::class, 'reject'])->name('reject');
        
        // Attachment Management
        Route::post('/{cpb}/upload', [CPBController::class, 'uploadAttachment'])->name('upload');
        Route::delete('/{cpb}/attachment/{attachment}', [CPBController::class, 'destroyAttachment'])->name('attachment.destroy');
        
        // Request & Release
        Route::post('/{cpb}/request', [CPBController::class, 'requestToQA'])->name('request');
        Route::post('/{cpb}/release', [CPBController::class, 'release'])->name('release');
    });

    // Handover History & Legacy Routes
    Route::prefix('handover')->name('handover.')->group(function () {
        // Menyelaraskan name 'handover.create' yang dipanggil di view detail agar lari ke handoverForm
        Route::get('/create/{cpb}', [CPBController::class, 'handoverForm'])->name('create');
        Route::post('/store/{cpb}', [CPBController::class, 'handover'])->name('store');
        Route::get('/history/{cpb}', [HandoverController::class, 'history'])->name('history');
        Route::post('/receive/{handover}', [HandoverController::class, 'receive'])->name('receive');
    });

    // Notifications Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear', [NotificationController::class, 'clear'])->name('clear');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/audit', [ReportController::class, 'audit'])->name('audit');
        
        Route::middleware(['role:superadmin,qa'])->group(function () {
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        });
    });

    // Admin Management
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

/*
|--------------------------------------------------------------------------
| API & Utility Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // API untuk Auto-numbering di Form Create
    Route::get('/api/cpb/last-number', [CPBController::class, 'getLastNumber'])->name('cpb.last-number');

    Route::prefix('api')->group(function () {
        Route::get('/dashboard/stats', function () {
            return response()->json([
                'stats' => [
                    'total' => \App\Models\CPB::count(),
                    'active' => \App\Models\CPB::where('status', '!=', 'released')->count(),
                    'overdue' => \App\Models\CPB::where('is_overdue', true)->count(),
                    'today' => \App\Models\CPB::whereDate('created_at', today())->count(),
                ]
            ]);
        });

        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        
        Route::get('/cpb/{id}/receivers', function ($id) {
            $cpb = \App\Models\CPB::findOrFail($id);
            $nextStatus = $cpb->getNextDepartment();
            $receivers = $nextStatus ? \App\Models\User::where('role', $nextStatus)->get(['id', 'name', 'department']) : [];
            return response()->json(['receivers' => $receivers]);
        });
    });
});

// Fallback 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});