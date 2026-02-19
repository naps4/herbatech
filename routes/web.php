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
use App\Http\Controllers\Auth\RegisterController;
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

    // Notifications Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear', [NotificationController::class, 'clear'])->name('clear');
    });

    // CPB Management Routes
    Route::prefix('cpb')->name('cpb.')->group(function () {
        Route::get('/', [CPBController::class, 'index'])->name('index');
        Route::get('/create', [CPBController::class, 'create'])->name('create');
        Route::post('/', [CPBController::class, 'store'])->name('store');
        Route::get('/last-number', [CPBController::class, 'getLastNumber'])->name('last-number');
        Route::get('/export-pdf', [CPBController::class, 'exportPdf'])->name('export-pdf');
        
        Route::get('/{cpb}', [CPBController::class, 'show'])->name('show');
        Route::get('/{cpb}/edit', [CPBController::class, 'edit'])->name('edit');
        Route::put('/{cpb}', [CPBController::class, 'update'])->name('update');
        Route::post('/{cpb}/reject', [CPBController::class, 'reject'])->name('reject');
        Route::post('/{cpb}/upload', [CPBController::class, 'uploadAttachment'])->name('upload');
        Route::post('/{cpb}/request', [CPBController::class, 'requestToQA'])->name('request');
        Route::post('/{cpb}/release', [CPBController::class, 'release'])->name('release');
        Route::delete('/{cpb}/attachment/{attachment}', [CPBController::class, 'destroyAttachment'])->name('attachment.destroy');
        
        // Handover legacy routes (jika masih digunakan di controller)
        Route::get('/{cpb}/handover', [CPBController::class, 'handoverForm'])->name('handoverForm');
        Route::post('/{cpb}/handover', [CPBController::class, 'handover'])->name('handover');
    });
    
    // Handover Dedicated Routes
    Route::prefix('handover')->name('handover.')->group(function () {
        Route::get('/create/{cpb}', [HandoverController::class, 'create'])->name('create');
        Route::post('/store/{cpb}', [HandoverController::class, 'store'])->name('store');
        Route::post('/receive/{handover}', [HandoverController::class, 'receive'])->name('receive');
        Route::get('/history/{cpb}', [HandoverController::class, 'history'])->name('history');
    });
    
    // REPORTS & AUDIT ROUTES
    Route::prefix('reports')->name('reports.')->group(function () {
        // Akses publik untuk semua role yang login (RND, PPIC, WH, dll)
        Route::get('/audit', [ReportController::class, 'audit'])->name('audit');
        
        // Akses terbatas hanya untuk Superadmin dan QA
        Route::middleware(['role:superadmin,qa'])->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        });
    });
    
    // ADMIN PANEL - Khusus Super Admin
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');
        
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Alias register ke create user
        Route::get('register', function () {
            return redirect()->route('admin.users.create');
        })->name('register');
    });
});

/*
|--------------------------------------------------------------------------
| API & Test Routes
|--------------------------------------------------------------------------
*/
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard/stats', function () {
        $stats = [
            'total' => \App\Models\CPB::count(),
            'active' => \App\Models\CPB::where('status', '!=', 'released')->count(),
            'overdue' => \App\Models\CPB::where('is_overdue', true)->count(),
            'today' => \App\Models\CPB::whereDate('created_at', today())->count(),
        ];
        return response()->json(['stats' => $stats]);
    });
    
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    
    Route::middleware(['role:superadmin'])->get('/admin/users/stats', function () {
        return response()->json([
            'total' => \App\Models\User::count(),
            'by_role' => \App\Models\User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role')
        ]);
    });
});

// Route fallback 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});