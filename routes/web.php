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
    
    // Notifications Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear', [NotificationController::class, 'clear'])->name('clear');
    });
    
    // CPB Routes
    Route::prefix('cpb')->name('cpb.')->group(function () {
        Route::get('/', [CPBController::class, 'index'])->name('index');
        Route::get('/create', [CPBController::class, 'create'])->name('create');
        Route::post('/', [CPBController::class, 'store'])->name('store');
        
        // Export Rute (Letakkan di atas rute {cpb} agar tidak dianggap ID)
        Route::get('/export-all-pdf', [CPBController::class, 'exportAllPdf'])->name('export-all-pdf');
        Route::get('/export-pdf', [CPBController::class, 'exportPdf'])->name('export-pdf');

        Route::get('/{cpb}', [CPBController::class, 'show'])->name('show');
        Route::get('/{cpb}/edit', [CPBController::class, 'edit'])->name('edit');
        Route::put('/{cpb}', [CPBController::class, 'update'])->name('update');
        Route::delete('/{cpb}', [CPBController::class, 'destroy'])->name('destroy');
        
        // Handover & Reject
        Route::get('/{cpb}/handover', [CPBController::class, 'handoverForm'])->name('handover.form');
        Route::post('/{cpb}/handover', [CPBController::class, 'handover'])->name('handover');
        Route::post('/{cpb}/reject', [CPBController::class, 'reject'])->name('reject');
        
        // Attachments
        Route::post('/{cpb}/upload', [CPBController::class, 'uploadAttachment'])->name('upload');
        Route::delete('/{cpb}/attachment/{attachment}', [CPBController::class, 'destroyAttachment'])->name('attachment.destroy');
        
        // QA & Release
        Route::post('/{cpb}/request', [CPBController::class, 'requestToQA'])->name('request');
        Route::post('/{cpb}/release', [CPBController::class, 'release'])->name('release');
    });
    
    // Handover Routes (History & Legacy compatibility)
    Route::prefix('handover')->name('handover.')->group(function () {
        Route::get('/create/{cpb}', [CPBController::class, 'handoverForm'])->name('create');
        Route::post('/store/{cpb}', [CPBController::class, 'handover'])->name('store');
        Route::get('/history/{cpb}', [HandoverController::class, 'history'])->name('history');
    });
    
    // REPORTS & AUDIT ROUTES
    Route::prefix('reports')->name('reports.')->group(function () {
        // 1. Diakses semua role (Visibilitas data difilter di ReportController@index)
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/audit', [ReportController::class, 'audit'])->name('audit');
        
        // 2. Data Manajerial & Sensitif (Hanya Superadmin & QA)
        Route::middleware(['role:superadmin,qa'])->group(function () {
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        });
    });
    
    // USER MANAGEMENT ROUTES (Super Admin Only)
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');
        
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

/*
|--------------------------------------------------------------------------
| Internal API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->group(function () {
    // API No. Batch Terakhir
    Route::get('/cpb/last-number', [CPBController::class, 'getLastNumber'])->name('cpb.last-number');
    
    // API Statistik Dashboard
    Route::get('/dashboard/stats', function () {
        return response()->json([
            'total' => \App\Models\CPB::count(),
            'active' => \App\Models\CPB::where('status', '!=', 'released')->count(),
            'overdue' => \App\Models\CPB::where('is_overdue', true)->count(),
            'rework' => \App\Models\CPB::where('is_rework', true)->count(),
        ]);
    });

    // API Personil Penerima
    Route::get('/cpb/{id}/receivers', function ($id) {
        $cpb = \App\Models\CPB::findOrFail($id);
        $nextStatus = $cpb->getNextDepartment();
        $receivers = $nextStatus ? \App\Models\User::where('role', $nextStatus)->get(['id', 'name', 'department']) : [];
        return response()->json(['receivers' => $receivers]);
    });
});

// Test/Debug Routes
Route::get('/test-auth', function () {
    $user = auth()->user();
    return $user ? response()->json([
        'authenticated' => true,
        'user' => $user->only(['id', 'name', 'role', 'email']),
        'is_superadmin' => $user->isSuperAdmin(),
    ]) : 'Not authenticated';
})->middleware('auth');

// Route fallback untuk handle 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});