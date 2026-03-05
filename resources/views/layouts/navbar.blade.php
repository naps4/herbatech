<nav class="main-header navbar navbar-expand-md navbar-dark navbar-custom-green shadow-sm border-0 position-relative">
    <div class="container-fluid px-md-4 d-flex align-items-center justify-content-between">

        <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center order-1">
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

        <button class="navbar-toggler order-3" type="button" id="manualToggler">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-4 order-md-2" id="navbarCollapse">
            <ul class="navbar-nav ml-2 mt-2 mt-md-0">
                @auth
                <li class="nav-item mx-lg-2">
                    <a href="{{ route('dashboard') }}" class="nav-link px-3 {{ request()->routeIs('dashboard') ? 'active font-weight-bold' : '' }}">
                        <i class="fas fa-seedling mr-1"></i> Dashboard
                    </a>
                </li>

                @php
                $pendingHandovers = \App\Models\CPB::where('current_department_id', auth()->id())
                ->where('status', '!=', 'released')
                ->get();
                @endphp
                @if($pendingHandovers->count() > 0)
                <li class="nav-item dropdown mx-lg-1">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('handover.*') ? 'active font-weight-bold' : '' }}">
                        Handover <span class="badge badge-pill badge-danger ml-1">{{ $pendingHandovers->count() }}</span>
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg mt-2">
                        @foreach($pendingHandovers->take(5) as $cpb)
                        <li><a href="{{ route('handover.create', $cpb) }}" class="dropdown-item py-2">{{ $cpb->batch_number }}</a></li>
                        @endforeach
                    </ul>
                </li>
                @endif

                <li class="nav-item dropdown mx-lg-1">
                    <a id="dropdownSubMenu2" href="#" data-toggle="dropdown" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('cpb.*') ? 'active font-weight-bold' : '' }}">
                        CPB Management
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg mt-2">
                        @can('create', App\Models\CPB::class)
                        <li><a href="{{ route('cpb.create') }}" class="dropdown-item py-2 text-success"><i class="fas fa-plus-circle mr-2"></i> Buat CPB Baru</a></li>
                        @endcan
                        <li><a href="{{ route('cpb.index', ['status' => 'active']) }}" class="dropdown-item py-2"><i class="fas fa-list mr-2"></i> Daftar CPB Aktif</a></li>
                        <li><a href="{{ route('cpb.index', ['status' => 'released']) }}" class="dropdown-item py-2"><i class="fas fa-check-double mr-2"></i> CPB Released</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown mx-lg-1">
                    <a id="dropdownSubMenu3" href="#" data-toggle="dropdown" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('reports.*') ? 'active font-weight-bold' : '' }}">
                        Laporan
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg mt-2">
                        <li><a href="{{ route('reports.audit') }}" class="dropdown-item py-2"><i class="fas fa-history mr-2"></i> Riwayat Audit</a></li>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isQA())
                        <div class="dropdown-divider"></div>
                        <li><a href="{{ route('reports.index') }}" class="dropdown-item py-2"><i class="fas fa-chart-line mr-2"></i> Analitik</a></li>
                        <li><a href="{{ route('reports.export') }}" class="dropdown-item py-2"><i class="fas fa-file-excel mr-2 text-success"></i> Export Excel</a></li>
                        @endif
                    </ul>
                </li>

                @if(auth()->user()->isSuperAdmin())
                <li class="nav-item dropdown mx-lg-1">
                    <a id="dropdownSubMenu4" href="#" data-toggle="dropdown" class="nav-link dropdown-toggle px-3 {{ request()->routeIs('admin.*') ? 'active font-weight-bold' : '' }}">
                        Admin Panel
                    </a>
                    <ul class="dropdown-menu border-0 shadow-lg mt-2">
                        <li><a href="{{ route('admin.dashboard') }}" class="dropdown-item py-2">Console Admin</a></li>
                        <li><a href="{{ route('admin.users.index') }}" class="dropdown-item py-2">Kelola Pengguna</a></li>
                        <li><a href="{{ route('admin.settings.index') }}" class="dropdown-item py-2">Pengaturan Situs</a></li>
                    </ul>
                </li>
                @endif
                @endauth
            </ul>
        </div>

        <ul class="order-2 order-md-3 navbar-nav navbar-no-expand ml-auto align-items-center flex-row">
            @auth
            <li class="nav-item d-none d-md-block mr-2">
                <form class="form-inline ml-0 ml-md-3 align-items-center" action="{{ route('cpb.index') }}" method="GET" style="height: 38px;">
                    <div class="input-group input-group-sm rounded-pill px-2 border search-container-navbar">
                        <input class="form-control border-0 bg-transparent text-white custom-placeholder"
                            type="search" name="batch_number" placeholder="Cari No. Batch..." aria-label="Search" value="{{ request('batch_number') }}">
                        <div class="input-group-append">
                            <button class="btn bg-transparent border-0 py-0" type="submit" style="box-shadow: none;">
                                <i class="fas fa-search text-white-50"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </li>

            <li class="nav-item d-md-none mr-1">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-search"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right p-3 shadow-lg" style="min-width: 250px;">
                    <form action="{{ route('cpb.index') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="search" name="batch_number" class="form-control" placeholder="Cari No. Batch...">
                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </li>

            <li class="nav-item dropdown mx-1">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unreadCount > 0)
                    <span class="badge badge-warning navbar-badge shadow-xs">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow-lg">
                    <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer text-primary font-weight-bold">LIHAT SEMUA</a>
                </div>
            </li>

            <li class="nav-item dropdown ml-2 border-left-md pl-md-2">
                <a class="nav-link d-flex align-items-center py-0" data-toggle="dropdown" href="#">
                    <div class="avatar-nav mr-2">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="d-none d-md-block text-left line-height-1">
                        <span class="d-block small font-weight-bold text-white">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                        <span class="d-block text-white-50" style="font-size: 10px;">{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg mt-2">
                    <a href="{{ route('profile.show') }}" class="dropdown-item py-2">Profil Saya</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item text-danger py-2" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> KELUAR
                    </a>
                </div>
            </li>
            @endauth
        </ul>
    </div>
</nav>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<style>
    /* CUSTOM STYLE FIXED */
    .navbar-custom-green {
        background: linear-gradient(135deg,rgb(17, 115, 46) 0%,rgb(37, 194, 97) 100%) !important;
        padding: 0.6rem 1.2rem; min-height: 64px;
    }
    .navbar-custom-green .nav-link { color: rgba(255, 255, 255, 0.8) !important; border-radius: 8px; transition: all 0.2s; }
    .navbar-custom-green .nav-link.active { background: rgba(255, 255, 255, 0.18); color: #fff !important; font-weight: 600; }
    .avatar-nav { width: 34px; height: 34px; background: rgba(255, 255, 255, 0.18); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; }
    .line-height-1 { line-height: 1.1; }

    /* Search Container */
    .search-container-navbar { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2) !important; }
    .custom-placeholder::placeholder { color: rgba(255, 255, 255, 0.6); }

    .content-wrapper {
        margin-top: 0 !important;
    }
    .content-header {
        padding-top: 15px !important; 
        padding-bottom: 0 !important;
    }

    .content-wrapper > .content {
        padding-top: 10px !important;
    }

    @media (max-width: 768px) {
        /* FLOATING MENU STYLE */
        #navbarCollapse {
            position: absolute; top: 65px; left: 10px; right: 10px;
            background: linear-gradient(135deg, rgb(17, 115, 46) 0%, rgb(30, 160, 80) 100%);
            border-radius: 12px; padding: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 1050; display: none;
        }
        #navbarCollapse.show { display: block !important; animation: fadeInMenu 0.2s ease-out; }
        @keyframes fadeInMenu { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .navbar-nav .nav-item { border-bottom: 1px solid rgba(255,255,255,0.1); width: 100%; }
        .dropdown-menu { background: white; border: 1px solid rgba(0,0,0,0.1); }
        .dropdown-item { color: #333 !important; }
    }

    @media (min-width: 768px) { .border-left-md { border-left: 1px solid rgba(255,255,255,0.2); } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggler = document.getElementById('manualToggler');
    const menu = document.getElementById('navbarCollapse');

    if (toggler && menu) {
        // 1. Fungsi Klik Toggler (Buka/Tutup)
        toggler.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        // 2. Klik di Luar Menu (Auto-Hide)
        document.addEventListener('click', function(e) {
            if (menu.classList.contains('show')) {
                if (!menu.contains(e.target) && !toggler.contains(e.target)) {
                    menu.classList.remove('show');
                }
            }
        });

        // 3. Klik Menu Link (Auto-Hide) - agar saat pindah halaman menu menutup
        menu.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', () => menu.classList.remove('show'));
        });
    }
});
</script>