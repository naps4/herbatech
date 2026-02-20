<!DOCTYPE html>
<html>
<head>
    <title>Laporan Daftar CPB</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>DAFTAR CATATAN PEMBUATAN BATCH (CPB)</h2>
        <p>PT HERBATECH INNOPHARMA INDUSTRY</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Batch</th>
                <th>Jenis</th>
                <th>Produk</th>
                <th>Status</th>
                <th>Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            {{-- Pastikan variabelnya $cpbs --}}
            @forelse($cpbs as $index => $cpb)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $cpb->batch_number }}</td>
                <td>{{ ucfirst($cpb->type) }}</td>
                <td>{{ $cpb->product_name }}</td>
                <td>{{ strtoupper($cpb->status) }}</td>
                <td>{{ $cpb->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">Data tidak ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>