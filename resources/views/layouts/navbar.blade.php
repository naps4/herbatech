<!-- resources/views/layouts/navbar.blade.php -->
<!-- Navbar -->
<nav class="main-header navbar navbar-expand-md navbar-dark navbar-custom-green">
    <div class="container">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            @if(isset($app_settings['app_logo']) && $app_settings['app_logo'])
                <img src="{{ asset($app_settings['app_logo']) }}" alt="App Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            @else
                <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="CPB Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            @endif
            <span class="brand-text font-weight-light">{{ $app_settings['app_name'] ?? 'CPB System' }}</span>
        </a>

        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                @auth
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    </li>

                    <!-- Serah Terima (Quick Handover) -->
                    @php
                        $pendingHandovers = \App\Models\CPB::where('current_department_id', auth()->id())
                            ->where('status', '!=', 'released')
                            ->get();
                    @endphp
                    @if($pendingHandovers->count() > 0)
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('handover.*') ? 'active' : '' }}">
                            Handover <span class="badge badge-danger">{{ $pendingHandovers->count() }}</span>
                        </a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @foreach($pendingHandovers->take(5) as $cpb)
                            <li>
                                <a href="{{ route('handover.create', $cpb) }}" class="dropdown-item">
                                    {{ $cpb->batch_number }}
                                    @if($cpb->is_overdue)
                                        <span class="badge badge-danger">Overdue</span>
                                    @endif
                                </a>
                            </li>
                            @endforeach
                            @if($pendingHandovers->count() > 5)
                            <li class="dropdown-divider"></li>
                            <li><a href="{{ route('cpb.index') }}" class="dropdown-item">Lihat Semua...</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- CPB Management -->
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu2" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('cpb.*') ? 'active' : '' }}">
                            CPB Management
                        </a>
                        <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                            @can('create', App\Models\CPB::class)
                            <li><a href="{{ route('cpb.create') }}" class="dropdown-item">Buat CPB Baru</a></li>
                            @endcan
                            <li><a href="{{ route('cpb.index') }}" class="dropdown-item">Daftar CPB</a></li>
                            <li>
                                <a href="{{ route('cpb.index', ['overdue' => 'true']) }}" class="dropdown-item">
                                    CPB Overdue
                                    @php
                                        $overdueCount = \App\Models\CPB::where('is_overdue', true)->count();
                                    @endphp
                                    @if($overdueCount > 0)
                                        <span class="badge badge-danger ml-2">{{ $overdueCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li><a href="{{ route('cpb.index', ['status' => 'released']) }}" class="dropdown-item">CPB Released</a></li>
                        </ul>
                    </li>

                    <!-- Reports & History -->
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu3" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            Laporan
                        </a>
                        <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                            <!-- Riwayat (Accessible to All) -->
                            <li><a href="{{ route('reports.audit') }}" class="dropdown-item">Riwayat</a></li>

                            <!-- Restricted Reports (Super Admin & QA) -->
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isQA())
                                <li class="dropdown-divider"></li>
                                <li><a href="{{ route('reports.index') }}" class="dropdown-item">Laporan CPB</a></li>
                                <li><a href="{{ route('reports.performance') }}" class="dropdown-item">Performance</a></li>
                                <li><a href="{{ route('reports.export') }}" class="dropdown-item">Export Excel</a></li>
                            @endif
                        </ul>
                    </li>

                    <!-- Admin Panel (Super Admin) -->
                    @if(auth()->user()->isSuperAdmin())
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu4" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            Admin
                        </a>
                        <ul aria-labelledby="dropdownSubMenu4" class="dropdown-menu border-0 shadow">
                            <li><a href="{{ route('admin.dashboard') }}" class="dropdown-item">Dashboard Admin</a></li>
                            <li><a href="{{ route('admin.users.index') }}" class="dropdown-item">Kelola User</a></li>
                            <li><a href="{{ route('admin.users.create') }}" class="dropdown-item">Tambah User</a></li>
                            <li><a href="{{ route('admin.settings.index') }}" class="dropdown-item">Pengaturan Website</a></li>
                            <li><a href="{{ route('register') }}" class="dropdown-item">Register User</a></li>
                        </ul>
                    </li>
                    @endif
                @endauth
            </ul>

            <!-- SEARCH FORM -->
            <form class="form-inline ml-0 ml-md-3" action="{{ route('cpb.index') }}" method="GET">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" name="batch_number" placeholder="Search CPB" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right navbar links -->
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            @auth
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge" id="notification-count">
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-header">{{ auth()->user()->unreadNotifications()->count() }} Notifikasi Baru</span>
                        <div class="dropdown-divider"></div>
                        
                        @foreach(auth()->user()->unreadNotifications()->take(5)->get() as $notification)
                        <a href="{{ route('notifications.show', $notification) }}" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> {{ Str::limit($notification->message, 20) }}
                            <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans() }}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        @endforeach
                        
                        <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">Lihat Semua Notifikasi</a>
                    </div>
                </li>
                
                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i>
                         <!-- Mobile only text can be hidden or just icon -->
                        <span class="d-none d-md-inline ml-1">{{ auth()->user()->name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <div class="dropdown-item py-3">
                            <div class="media">
                                <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        {{ auth()->user()->name }}
                                        <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm">{{ ucfirst(auth()->user()->role) }}</p>
                                    <p class="text-sm text-muted"><i class="far fa-building mr-1"></i> {{ auth()->user()->department }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#profileModal">
                            <i class="fas fa-id-card mr-2"></i> Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            @endauth

            @guest
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                </li>
            @endguest
        </ul>
    </div>
</nav>

<!-- Hidden Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>