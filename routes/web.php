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

    // API Routes for Number Created
    Route::get('/cpb/last-number', [App\Http\Controllers\CPBController::class, 'getLastNumber']);
    
    // CPB Routes
    Route::prefix('cpb')->name('cpb.')->group(function () {
        Route::get('/', [CPBController::class, 'index'])->name('index');
        Route::get('/create', [CPBController::class, 'create'])->name('create');
        Route::post('/', [CPBController::class, 'store'])->name('store');
        
        // Route khusus Export (Daftar Semua) - Letakkan DI ATAS rute {cpb} agar tidak terbaca sebagai ID
        Route::get('/export-all-pdf', [CPBController::class, 'exportAllPdf'])->name('export-all-pdf');
        Route::get('/export-pdf', [CPBController::class, 'exportPdf'])->name('export-pdf');

        Route::get('/{cpb}', [CPBController::class, 'show'])->name('show');
        Route::get('/{cpb}/edit', [CPBController::class, 'edit'])->name('edit');
        Route::put('/{cpb}', [CPBController::class, 'update'])->name('update');
        Route::delete('/{cpb}', [CPBController::class, 'destroy'])->name('destroy');
        
        // Handover & Reject
        Route::get('/{cpb}/handover', [CPBController::class, 'handoverForm'])->name('handoverForm');
        Route::post('/{cpb}/handover', [CPBController::class, 'handover'])->name('handover');
        Route::post('/{cpb}/reject', [CPBController::class, 'reject'])->name('reject');
        
        // Attachment - HAPUS awalan /cpb agar tidak duplikat
        Route::post('/{cpb}/upload', [CPBController::class, 'uploadAttachment'])->name('upload');
        Route::delete('/{cpb}/attachment/{attachment}', [CPBController::class, 'destroyAttachment'])->name('attachment.destroy');
        
        // QA & Release
        Route::post('/{cpb}/request', [CPBController::class, 'requestToQA'])->name('request');
        Route::post('/{cpb}/release', [CPBController::class, 'release'])->name('release');
    });
    
    // Handover Routes
    Route::prefix('handover')->name('handover.')->group(function () {
        // Menyelaraskan name 'handover.create' yang dipanggil di view detail agar lari ke handoverForm
        Route::get('/create/{cpb}', [CPBController::class, 'handoverForm'])->name('create');
        Route::post('/store/{cpb}', [CPBController::class, 'handover'])->name('store');
        Route::get('/history/{cpb}', [HandoverController::class, 'history'])->name('history');
        Route::post('/receive/{handover}', [HandoverController::class, 'receive'])->name('receive');
        Route::get('/history/{cpb}', [HandoverController::class, 'history'])->name('history');
    });
    
    // REPORTS ROUTES - PERBAIKI INI
    Route::prefix('reports')->name('reports.')->group(function () {
        // Boleh diakses semua user yang login
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/audit', [ReportController::class, 'audit'])->name('audit');
        
        // Hanya superadmin dan QA
        Route::middleware(['role:superadmin,qa'])->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
        });
    });
    
    // USER MANAGEMENT ROUTES - Hanya untuk Super Admin
    Route::middleware(['role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        // User Management Resource
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/{user}/activity', [UserController::class, 'activity'])->name('users.activity');
        
        // Site Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    });
    
    // User Registration (Super Admin only) - OLD ROUTE, BISA DIHAPUS ATAU DIPERTAHANKAN
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('register', function () {
            return redirect()->route('admin.users.create');
        })->name('register');
    });
});

// API Routes
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard/stats', function () {
        $user = auth()->user();
        $stats = [
            'total' => \App\Models\CPB::count(),
            'active' => \App\Models\CPB::where('status', '!=', 'released')->count(),
            'overdue' => \App\Models\CPB::where('is_overdue', true)->count(),
            'today' => \App\Models\CPB::whereDate('created_at', today())->count(),
        ];
        return response()->json(['stats' => $stats]);
    });
    
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    
    Route::get('/cpb/{id}/receivers', function ($id) {
        $cpb = \App\Models\CPB::findOrFail($id);
        $nextStatus = $cpb->getNextDepartment();
        
        if (!$nextStatus) {
            return response()->json(['receivers' => []]);
        }
        
        $receivers = \App\Models\User::where('role', $nextStatus)->get(['id', 'name', 'department']);
        
        return response()->json(['receivers' => $receivers]);
    });
    
    // API untuk admin
    Route::middleware(['role:superadmin'])->prefix('admin')->group(function () {
        Route::get('/users/stats', function () {
            $totalUsers = \App\Models\User::count();
            $roleCounts = \App\Models\User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role');
            
            return response()->json([
                'total' => $totalUsers,
                'by_role' => $roleCounts
            ]);
        });
    });
});

// Test routes
Route::get('/test-auth', function () {
    $user = auth()->user();
    if (!$user) {
        return 'Not authenticated';
    }
    
    return response()->json([
        'authenticated' => true,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_role' => $user->role,
        'user_email' => $user->email,
        'is_superadmin' => $user->isSuperAdmin(),
        'is_qa' => $user->isQA(),
    ]);
})->middleware('auth');

Route::get('/test-gate/{cpb}', function (CPB $cpb) {
    $user = auth()->user();
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'role' => $user->role,
            'name' => $user->name,
        ],
        'cpb' => [
            'id' => $cpb->id,
            'batch_number' => $cpb->batch_number,
            'status' => $cpb->status,
            'created_by' => $cpb->created_by,
            'current_department_id' => $cpb->current_department_id,
        ],
        'gate_checks' => [
            'view' => Gate::allows('view', $cpb),
            'view-cpb' => Gate::allows('view-cpb', $cpb),
        ],
        'manual_checks' => [
            'is_creator' => $cpb->created_by === $user->id,
            'is_current_dept' => $cpb->current_department_id === $user->id,
            'user_is_superadmin' => $user->isSuperAdmin(),
            'user_is_qa' => $user->isQA(),
        ],
    ]);
})->middleware('auth');

// Route fallback untuk handle 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});