<?php

return [
    'welcome' => 'Selamat Datang di Sistem Manajemen CPB',
    
    // CPB Messages
    'cpb' => [
        'created' => 'CPB berhasil dibuat',
        'updated' => 'CPB berhasil diperbarui',
        'deleted' => 'CPB berhasil dihapus',
        'handover_success' => 'CPB berhasil diserahkan',
        'release_success' => 'CPB berhasil direlease',
        'overdue_alert' => 'CPB telah melebihi batas waktu',
    ],
    
    // Handover Messages
    'handover' => [
        'success' => 'Serah terima berhasil',
        'received' => 'Handover berhasil diterima',
        'cannot_handover' => 'Tidak dapat melakukan handover',
        'invalid_receiver' => 'Penerima tidak valid',
    ],
    
    // Notification Messages
    'notification' => [
        'new_cpb' => 'CPB baru telah dibuat',
        'cpb_overdue' => 'CPB telah melebihi batas waktu',
        'handover_received' => 'Anda menerima handover CPB',
        'marked_read' => 'Notifikasi ditandai sebagai dibaca',
        'all_marked_read' => 'Semua notifikasi ditandai sebagai dibaca',
        'cleared' => 'Semua notifikasi telah dihapus',
    ],
    
    // Validation Messages
    'validation' => [
        'required' => 'Field :attribute wajib diisi',
        'unique' => ':attribute sudah digunakan',
        'email' => ':attribute harus berupa email yang valid',
        'min' => ':attribute minimal :min karakter',
        'max' => ':attribute maksimal :max karakter',
        'numeric' => ':attribute harus berupa angka',
        'in' => ':attribute yang dipilih tidak valid',
    ],
    
    // Status Messages
    'status' => [
        'rnd' => 'RND',
        'qa' => 'QA Review',
        'ppic' => 'PPIC',
        'wh' => 'Warehouse',
        'produksi' => 'Production',
        'qc' => 'Quality Control',
        'qa_final' => 'QA Final',
        'released' => 'Released',
        'overdue' => 'Overdue',
        'on_time' => 'On Time',
        'warning' => 'Warning',
    ],
    
    // Time Messages
    'time' => [
        'hour' => 'jam',
        'day' => 'hari',
        'week' => 'minggu',
        'month' => 'bulan',
        'year' => 'tahun',
        'just_now' => 'baru saja',
        'minutes_ago' => ':count menit yang lalu',
        'hours_ago' => ':count jam yang lalu',
        'days_ago' => ':count hari yang lalu',
    ],
    
    // Action Messages
    'action' => [
        'create' => 'Buat',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'view' => 'Lihat',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'submit' => 'Kirim',
        'filter' => 'Filter',
        'export' => 'Export',
        'import' => 'Import',
        'download' => 'Download',
        'upload' => 'Upload',
        'confirm' => 'Konfirmasi',
        'back' => 'Kembali',
    ],
    
    // Report Messages
    'report' => [
        'generated' => 'Laporan berhasil dibuat',
        'exported' => 'Laporan berhasil diexport',
        'no_data' => 'Tidak ada data untuk ditampilkan',
        'filter_applied' => 'Filter telah diterapkan',
    ],
    
    // Error Messages
    'error' => [
        'general' => 'Terjadi kesalahan',
        'not_found' => 'Data tidak ditemukan',
        'unauthorized' => 'Anda tidak memiliki akses',
        'forbidden' => 'Akses ditolak',
        'server_error' => 'Kesalahan server',
        'validation_error' => 'Kesalahan validasi',
    ],
    
    // Success Messages
    'success' => [
        'general' => 'Operasi berhasil',
        'saved' => 'Data berhasil disimpan',
        'updated' => 'Data berhasil diperbarui',
        'deleted' => 'Data berhasil dihapus',
        'action_completed' => 'Aksi berhasil diselesaikan',
    ],
];