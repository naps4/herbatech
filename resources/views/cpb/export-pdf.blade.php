<!DOCTYPE html>
<html>
<head>
    <title>Laporan CPB</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daftar Catatan Pembuatan Batch (CPB)</h2>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Batch</th>
                <th>Jenis</th>
                <th>Produk</th>
                <th>Status</th>
                <th>Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cpbs as $cpb)
            <tr>
                <td>{{ $cpb->batch_number }}</td>
                <td>{{ ucfirst($cpb->type) }}</td>
                <td>{{ $cpb->product_name }}</td>
                <td>{{ strtoupper($cpb->status) }}</td>
                <td>{{ $cpb->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>