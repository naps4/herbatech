<nav class="main-header navbar navbar-expand-md navbar-dark navbar-custom-green shadow-sm border-0">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">{{-- Menggunakan fluid agar lebih luas dan konsisten dengan halaman profil/user --}}

        <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center">
            @if(isset($app_settings['app_logo']) && $app_settings['app_logo'])
            <img src="{{ Storage::url($app_settings['app_logo']) }}?v={{ time() }}"
                alt="App Logo"
                class="elevation-2 mr-2"
                style="height: 28px; width: 28px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);">
            @else
            <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="CPB Logo" class="brand-image img-circle elevation-2 mr-2" style="max-height: 33px;">
            @endif
            <span class="brand-text font-weight-bold">{{ $app_settings['app_name'] ?? 'CPB System' }}</span>
        </a>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#sidebarMenu"
            aria-controls="sidebarMenu"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3 d-none d-lg-flex" id="navbarCollapse">
            <ul class="navbar-nav ml-2"> {{-- Sedikit margin kiri agar tidak menempel logo --}}
                @auth
                <li class="nav-item mx-lg-4">
                    <a href="{{ route('dashboard') }}" class="nav-link px-3 {{ request()->routeIs('dashboard') ? 'active font-weight-bold' : '' }}">
                        <i class="fas fa-seedling"></i> Dashboard
                    </a>
                </li>

                @php
                $pendingHandovers = \App\Models\CPB::where('current_department_id', auth()->id())
                ->where('status', '!=', 'released')
                ->get();
                @endphp
                @if($pendingHandovers->count() > 0)
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('handover.*') ? 'active font-weight-bold' : '' }}">
                        Handover
                        <span class="badge badge-pill badge-danger ml-1 shadow-xs">{{ $pendingHandovers->count() }}</span>
                    </a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow-lg mt-2">
                        <li class="dropdown-header">PENDING HANDOVER</li>
                        @foreach($pendingHandovers->take(5) as $cpb)
                        <li>
                            <a href="{{ route('handover.create', $cpb) }}" class="dropdown-item py-2">
                                <i class="fas fa-exchange-alt mr-2 text-muted small"></i> {{ $cpb->batch_number }}
                                @if($cpb->is_overdue)
                                <span class="badge badge-danger float-right mt-1" style="font-size: 10px;">Overdue</span>
                                @endif
                            </a>
                        </li>
                        @endforeach
                        @if($pendingHandovers->count() > 5)
                        <div class="dropdown-divider"></div>
                        <li><a href="{{ route('cpb.index') }}" class="dropdown-item text-center text-primary font-weight-bold small">LIHAT SEMUA</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu2" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('cpb.*') ? 'active font-weight-bold' : '' }}">
                        CPB Management
                    </a>
                    <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow-lg mt-2">
                        @can('create', App\Models\CPB::class)
                        <li><a href="{{ route('cpb.create') }}" class="dropdown-item py-2"><i class="fas fa-plus-circle mr-2 text-success"></i> Buat CPB Baru</a></li>
                        @endcan
                        <li>
                            <a href="{{ route('cpb.index', ['status' => 'active']) }}" class="dropdown-item py-2">
                                <i class="fas fa-list-ul mr-2 text-primary"></i> Daftar CPB Aktif
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            {{-- Update link agar menyertakan status active secara otomatis --}}
                            <a href="{{ route('cpb.index', ['overdue' => 'true', 'status' => 'active']) }}" class="dropdown-item py-2">
                                <i class="fas fa-clock mr-2 text-danger"></i> CPB Overdue

                                @php
                                $user = auth()->user();
                                // Mulai query dasar untuk overdue yang belum released
                                $overdueQuery = \App\Models\CPB::where('is_overdue', true)
                                ->where('status', '!=', 'released');

                                // Terapkan filter hak akses yang sama dengan Dashboard agar angka sinkron
                                if (!$user->isSuperAdmin() && !$user->isQA() && $user->role !== 'rnd') {
                                $overdueQuery->where(function($q) use ($user) {
                                $q->where('current_department_id', $user->id)
                                ->orWhere('created_by', $user->id)
                                ->orWhereHas('handoverLogs', function($sub) use ($user) {
                                $sub->where('handed_by', $user->id)
                                ->orWhere('received_by', $user->id);
                                });
                                });
                                }
                                $overdueCount = $overdueQuery->count();
                                @endphp

                                @if($overdueCount > 0)
                                <span class="badge badge-danger float-right mt-1 shadow-xs">{{ $overdueCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li><a href="{{ route('cpb.index', ['status' => 'released']) }}" class="dropdown-item py-2"><i class="fas fa-check-double mr-2 text-info"></i> CPB Released</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu3" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('reports.*') ? 'active font-weight-bold' : '' }}">
                        Laporan
                    </a>
                    <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow-lg mt-2">
                        <li><a href="{{ route('reports.audit') }}" class="dropdown-item py-2"><i class="fas fa-history mr-2 text-secondary"></i> Riwayat Audit</a></li>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isQA())
                        <div class="dropdown-divider"></div>
                        <li class="dropdown-header small">ADMINISTRASI</li>
                        <li><a href="{{ route('reports.index') }}" class="dropdown-item py-2"><i class="fas fa-chart-line mr-2 text-info"></i> Analitik CPB</a></li>
                        <li><a href="{{ route('reports.performance') }}" class="dropdown-item py-2"><i class="fas fa-tachometer-alt mr-2 text-warning"></i> KPI Performance</a></li>
                        <li><a href="{{ route('reports.export') }}" class="dropdown-item py-2"><i class="fas fa-file-excel mr-2 text-success"></i> Export Report</a></li>
                        @endif
                    </ul>
                </li>

                @if(auth()->user()->isSuperAdmin())
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu4" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('admin.*') ? 'active font-weight-bold' : '' }}">
                        Admin Panel
                    </a>
                    <ul aria-labelledby="dropdownSubMenu4" class="dropdown-menu border-0 shadow-lg mt-2">
                        <li><a href="{{ route('admin.dashboard') }}" class="dropdown-item py-2"><i class="fas fa-th-large mr-2"></i> Console Admin</a></li>
                        <li><a href="{{ route('admin.users.index') }}" class="dropdown-item py-2"><i class="fas fa-users mr-2 text-primary"></i> Kelola Pengguna</a></li>
                        <li><a href="{{ route('admin.settings.index') }}" class="dropdown-item py-2"><i class="fas fa-cogs mr-2 text-dark"></i> Site Settings</a></li>
                    </ul>
                </li>
                @endif
                @endauth
            </ul>
        </div>

        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto align-items-center">
            @auth
            <li class="nav-item d-none d-md-block mr-2">
                <form class="form-inline ml-0 ml-md-3 align-items-center" action="{{ route('cpb.index') }}" method="GET" style="height: 38px;">
                    {{-- hapus d-none agar selalu muncul, atau sesuaikan container --}}
                    <div class="input-group input-group-sm rounded-pill px-2 border search-container-navbar">
                        <input class="form-control border-0 bg-transparent text-white custom-placeholder"
                            type="search"
                            name="batch_number"
                            placeholder="Cari No. Batch..."
                            aria-label="Search"
                            value="{{ request('batch_number') }}">

                        <div class="input-group-append">
                            <button class="btn bg-transparent border-0 py-0" type="submit" style="box-shadow: none;">
                                <i class="fas fa-search text-white-50"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </li>

            <li class="nav-item d-md-none">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-search"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right p-3">
                    <form action="{{ route('cpb.index') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="search"
                                name="batch_number"
                                class="form-control"
                                placeholder="Cari No. Batch...">
                            <div class="input-group-append">
                                <button class="btn btn-success">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </li>

            <li class="nav-item dropdown mx-1">
                <a class="nav-link" data-toggle="dropdown" href="#" title="Notifikasi">
                    <i class="far fa-bell icon-md"></i>
                    @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unreadCount > 0)
                    <span class="badge badge-warning navbar-badge shadow-xs">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow-lg">
                    <span class="dropdown-header font-weight-bold">{{ $unreadCount }} NOTIFIKASI BARU</span>
                    <div class="dropdown-divider"></div>
                    @foreach(auth()->user()->unreadNotifications()->take(5)->get() as $notification)
                    <a href="{{ route('notifications.show', $notification) }}" class="dropdown-item py-3">
                        <div class="media align-items-center">
                            <div class="bg-primary-soft p-2 rounded-circle mr-3">
                                <i class="fas fa-envelope text-primary small"></i>
                            </div>
                            <div class="media-body small">
                                <p class="mb-0 text-dark">{{ Str::limit($notification->message, 35) }}</p>
                                <span class="text-muted font-italic">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    @endforeach
                    <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer small font-weight-bold text-primary">LIHAT SEMUA NOTIFIKASI</a>
                </div>
            </li>

            <li class="nav-item dropdown ml-2 border-left pl-2">
                <a class="nav-link d-flex align-items-center py-0" data-toggle="dropdown" href="#">
                    <div class="avatar-nav mr-2">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block text-left line-height-1">
                        <span class="d-block small font-weight-bold text-white">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                        <span class="d-block text-white-50" style="font-size: 10px;">{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow-lg mt-2">
                    <div class="dropdown-item py-4 text-center bg-light">
                        <div class="avatar-lg mx-auto mb-2">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <h6 class="font-weight-bold mb-0">{{ auth()->user()->name }}</h6>
                        <p class="text-xs text-muted mb-0">{{ auth()->user()->email }}</p>
                        <span class="badge badge-primary-soft text-primary mt-2">{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.show') }}" class="dropdown-item py-2">
                        <i class="fas fa-id-card mr-3 text-muted"></i> Profil Saya
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item text-danger py-2 font-weight-bold" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-3"></i> KELUAR SISTEM
                    </a>
                </div>
            </li>
            @endauth
        </ul>
    </div>
</nav>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<style>
    /* ==============================
   NAVBAR CUSTOM GREEN FIXED
============================== */

    /* Target navbar kamu */
    .navbar-custom-green {
        background: linear-gradient(135deg,rgb(17, 115, 46) 0%,rgb(37, 194, 97) 100%) !important;
        padding: 0.6rem 1.2rem;
        min-height: 64px;
    }

    /* Collapse biar sejajar */
    .navbar-custom-green .navbar-collapse {
        display: flex;
        align-items: center;
    }

    /* Menu kiri */
    .navbar-custom-green .navbar-nav {
        align-items: center;
        gap: 6px;
    }

    /* Nav item */
    .navbar-custom-green .nav-item {
        display: flex;
        align-items: center;
    }

    /* Link menu */
    .navbar-custom-green .nav-link {
        font-size: 0.88rem;
        font-weight: 500;
        padding: 8px 14px !important;
        border-radius: 8px;
        color: rgba(255, 255, 255, 0.75) !important;
        white-space: nowrap;
        display: flex;
        align-items: center;
        height: 40px;
        transition: all 0.2s ease;
    }

    .navbar-custom-green .nav-link:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff !important;
    }

    .navbar-custom-green .nav-link.active {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff !important;
        font-weight: 600;
    }

    /* Brand */
    .navbar-custom-green .navbar-brand {
        margin-right: 24px;
    }

    .navbar-custom-green .brand-text {
        font-size: 1rem;
        color: #ffffff !important;
    }

    /* Search container */
    .search-container-navbar {
        height: 34px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 20px;
        padding-left: 10px;
        padding-right: 6px;
        border: none !important;
    }

    .search-container-navbar input {
        height: 30px;
        font-size: 0.85rem;
    }

    /* Notifikasi badge */
    .navbar-badge {
        background: #facc15 !important;
        color: #1e293b !important;
        font-weight: 600;
    }

    /* Avatar */
    .avatar-nav {
        width: 34px;
        height: 34px;
        background: rgba(255, 255, 255, 0.18);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #ffffff;
    }

    /* Supaya Dashboard tidak turun */
    .navbar-custom-green .nav-link i {
        margin-right: 6px;
    }

    @media (max-width: 768px) {
        .main-sidebar {
            font-size: 0.85rem;
        }

        .nav-sidebar .nav-link {
            padding: 6px 10px;
        }

        .brand-text {
            font-size: 0.9rem;
        }
    }
</style>

<ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto align-items-center flex-row">
    {{-- Tambahkan class 'flex-row' agar ikon notifikasi dan profil tetap sejajar horizontal di mobile --}}
    @auth
    {{-- Search bar: hapus d-none d-lg-block jika ingin muncul di semua ukuran, atau biarkan jika ingin hanya di desktop --}}
    <li class="nav-item d-none d-md-block mr-2">
        {{-- Form Search Anda --}}
    </li>

    {{-- Sisanya tetap sama --}}
    @endauth
</ul>