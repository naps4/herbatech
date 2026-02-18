<!-- Stats Cards -->
<div class="col-lg-3 col-6">
    <div class="small-box bg-info">
        <div class="inner">
            <h3>{{ $stats['total_cpbs'] ?? 0 }}</h3>
            <p>Total CPB</p>
        </div>
        <div class="icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <a href="{{ route('cpb.index') }}" class="small-box-footer">
            More info <i class="fas fa-arrow-circle-right"></i>
        </a>
    </div>
</div>

<div class="col-lg-3 col-6">
    <div class="small-box bg-success">
        <div class="inner">
            <h3>{{ $stats['active_cpbs'] }}</h3>
            <p>CPB Aktif</p>
        </div>
        <div class="icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <a href="{{ route('cpb.index', ['status' => 'active']) }}" class="small-box-footer">
            More info <i class="fas fa-arrow-circle-right"></i>
        </a>
    </div>
</div>

<div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
        <div class="inner">
            <h3>{{ $stats['overdue_cpbs'] ?? 0 }}</h3>
            <p>CPB Overdue</p>
        </div>
        <div class="icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <a href="{{ route('cpb.index', ['overdue' => 'true']) }}" class="small-box-footer">
            More info <i class="fas fa-arrow-circle-right"></i>
        </a>
    </div>
</div>

<div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
        <div class="inner">
            <h3>{{ $stats['today_cpbs'] ?? 0 }}</h3>
            <p>CPB Hari Ini</p>
        </div>
        <div class="icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <!-- {{-- Tambahkan status=all agar data yang sudah released tetap muncul untuk filter hari ini --}} -->
        <a href="{{ route('cpb.index', ['start_date' => date('Y-m-d'), 'status' => 'all']) }}" class="small-box-footer">
            More info <i class="fas fa-arrow-circle-right"></i>
        </a>
    </div>
</div>