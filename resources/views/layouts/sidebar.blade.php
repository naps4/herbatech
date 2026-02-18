<!-- resources/views/layouts/sidebar.blade.php -->
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="CPB Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">CPB System</span>
    </a>

    <div class="sidebar">
        @auth
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                    <small class="text-muted">{{ ucfirst(auth()->user()->role) }}</small>
                    <div class="small">
                        @php
                            // Perbaikan: Hanya hitung CPB yang belum released untuk status menunggu
                            $handoverCount = \App\Models\CPB::where('current_department_id', auth()->id())
                                ->where('status', '!=', 'released')
                                ->count();
                        @endphp
                        <span class="badge badge-info">{{ $handoverCount }} CPB menunggu</span>
                    </div>
                </div>
            </div>

            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" placeholder="Search CPB..." aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>
                                Dashboard
                                @if($handoverCount > 0)
                                    <span class="badge badge-warning right">{{ $handoverCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>

                    @php
                        $pendingHandovers = \App\Models\CPB::where('current_department_id', auth()->id())
                            ->where('status', '!=', 'released')
                            ->get();
                    @endphp
                    
                    @if($pendingHandovers->count() > 0)
                    <li class="nav-item {{ request()->routeIs('handover.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('handover.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>
                                Serah Terima
                                <i class="right fas fa-angle-left"></i>
                                <span class="badge badge-danger right">{{ $pendingHandovers->count() }}</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach($pendingHandovers->take(5) as $cpb)
                            <li class="nav-item">
                                <a href="{{ route('handover.create', $cpb) }}" class="nav-link">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>
                                        {{ $cpb->batch_number }}
                                        @if($cpb->is_overdue)
                                            <span class="badge badge-danger">Overdue</span>
                                        @endif
                                    </p>
                                </a>
                            </li>
                            @endforeach
                            @if($pendingHandovers->count() > 5)
                            <li class="nav-item">
                                <a href="{{ route('cpb.index', ['status' => 'active']) }}" class="nav-link">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Lihat Semua...</p>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <li class="nav-item {{ request()->routeIs('cpb.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('cpb.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>
                                CPB Management
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('create', App\Models\CPB::class)
                            <li class="nav-item">
                                <a href="{{ route('cpb.create') }}" class="nav-link {{ request()->routeIs('cpb.create') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Buat CPB Baru</p>
                                </a>
                            </li>
                            @endcan
                            
                            <li class="nav-item">
                                {{-- PERBAIKAN: Gunakan parameter status=active agar sinkron dengan filter CPBController --}}
                                <a href="{{ route('cpb.index', ['status' => 'active']) }}" 
                                   class="nav-link {{ request()->fullUrlIs(route('cpb.index', ['status' => 'active'])) || (request()->routeIs('cpb.index') && !request()->has('status') && !request()->has('overdue')) ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Daftar CPB Aktif</p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="{{ route('cpb.index', ['overdue' => 'true']) }}" class="nav-link {{ request()->get('overdue') == 'true' ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-danger"></i>
                                    <p>
                                        CPB Overdue
                                        @php
                                            $overdueCountGlobal = \App\Models\CPB::where('is_overdue', true)->count();
                                        @endphp
                                        @if($overdueCountGlobal > 0)
                                            <span class="badge badge-danger">{{ $overdueCountGlobal }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('cpb.index', ['status' => 'released']) }}" class="nav-link {{ request()->get('status') == 'released' ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>CPB Released</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('reports.audit') }}" class="nav-link {{ request()->routeIs('reports.audit') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Riwayat Handover</p>
                        </a>
                    </li>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isQA())
                    <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                Laporan & Analytics
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Laporan CPB</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.performance') }}" class="nav-link {{ request()->routeIs('reports.performance') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Performance</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.export') }}" class="nav-link {{ request()->routeIs('reports.export') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Export Excel</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                    <li class="nav-header">ADMINISTRATOR</li>
                    <li class="nav-item {{ request()->routeIs('admin.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>
                                Admin Panel
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Admin Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kelola User</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users.create') }}" class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Tambah User Baru</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>
                                Notifikasi
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="badge badge-danger right" id="notification-count">{{ $unreadCount }}</span>
                                @endif
                            </p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link" data-toggle="modal" data-target="#profileModal">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Profil Saya</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
        @endauth
        
        @guest
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="{{ route('login') }}" class="d-block">Login untuk mengakses sistem</a>
                </div>
            </div>
        @endguest
    </div>
</aside>

<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">Profil Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>  
            <div class="modal-body">
                @auth
                <div class="text-center mb-3">
                    <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image" width="100">
                </div>
                <table class="table table-bordered">
                    <tr><th width="30%">Nama</th><td>{{ auth()->user()->name }}</td></tr>
                    <tr><th>Email</th><td>{{ auth()->user()->email }}</td></tr>
                    <tr><th>Role</th><td><span class="badge badge-primary">{{ ucfirst(auth()->user()->role) }}</span></td></tr>
                    <tr><th>Department</th><td>{{ auth()->user()->department }}</td></tr>
                    <tr><th>Bergabung</th><td>{{ auth()->user()->created_at->format('d F Y') }}</td></tr>
                </table>
                @endauth
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh notification count every 30 seconds
    setInterval(function() {
        $.get('/api/notifications/unread-count', function(data) {
            $('#notification-count').text(data.count);
            if (data.count > 0) {
                $('#notification-count').addClass('badge-danger').removeClass('badge-warning');
            } else {
                $('#notification-count').addClass('badge-warning').removeClass('badge-danger');
            }
        });
    }, 30000);
    
    // Sidebar search functionality
    $('[data-widget="sidebar-search"]').on('keyup', function(e) {
        if (e.keyCode === 13) {
            const query = $(this).find('input').val();
            if (query.length > 2) {
                window.location.href = '{{ route("cpb.index") }}?batch_number=' + encodeURIComponent(query);
            }
        }
    });
});
</script>
@endpush