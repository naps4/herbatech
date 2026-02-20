<?php

namespace App\Http\Controllers;

use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use App\Models\CPBAttachment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\CPBCreated;
use App\Events\CPBHandover;

class CPBController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CPB::query();

        // Fix logic index agar filter berjalan (Sebelumnya ada return di atas filter)
        if ($request->get('status') === 'active') {
            $query->where('status', '!=', 'released');
        } elseif ($request->get('status') === 'released') {
            $query->where('status', 'released');
        }

        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }

        //parameter untuk pengecekan overdue
        if ($request->has('overdue') && $request->overdue == 'true') {
            $query->where('is_overdue', true);
        }
    
        $cpbs = $query->latest()->paginate(10);
        return view('cpb.index', compact('cpbs'));

        // Role-based filtering
        if (!$user->isSuperAdmin() && !$user->isQA() && $user->role !== 'rnd') {
            $query->where(function ($q) use ($user) {
                $q->where('current_department_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('status', 'released');

                if ($user->role === 'ppic') {
                    $q->orWhere('status', 'qa');
                }
            });
        }

        $cpbs = $query->orderBy('is_overdue', 'desc')
            ->orderBy('entered_current_status_at', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('cpb.index', compact('cpbs'));
    }

    public function show(CPB $cpb)
    {
        $handoverLogs = $cpb->handoverLogs()->with(['sender', 'receiver'])->latest()->get();
        $nextDepartment = $cpb->getNextDepartment();
        $canHandover = Gate::allows('handover', $cpb);

        // FITUR: Cek apakah user saat ini sudah mengunggah dokumen di tahap ini
        $hasAttachment = $cpb->attachments()
            ->where('uploaded_by', auth()->id())
            ->exists();

        return view('cpb.show', compact('cpb', 'handoverLogs', 'nextDepartment', 'canHandover', 'hasAttachment'));
    }

    public function handoverForm(CPB $cpb)
    {
        if (!Gate::allows('handover', $cpb)) {
            abort(403);
        }

        // VALIDASI DOKUMEN: User tidak boleh masuk ke form jika belum upload file
        $hasAttachment = $cpb->attachments()->where('uploaded_by', auth()->id())->exists();
        if (!$hasAttachment && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('cpb.show', $cpb)
                ->with('error', 'Akses Ditolak! Harap unggah dokumen laporan di bagian Lampiran terlebih dahulu.');
        }

        $nextStatus = $cpb->getNextDepartment();
        if (!$nextStatus) {
            return back()->with('error', 'CPB sudah di status akhir.');
        }

        $nextUsers = User::where('role', $nextStatus)->get();

        // PASTIKAN FILE INI ADA: resources/views/handover/create.blade.php
        return view('handover.create', compact('cpb', 'nextStatus', 'nextUsers'));
    }

    public function handover(Request $request, CPB $cpb)
    {
        if (!Gate::allows('handover', $cpb)) {
            abort(403);
        }

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
            'file' => 'nullable|file|max:10240'
        ]);
        
        DB::beginTransaction();
        try {
            $receiver = User::findOrFail($request->receiver_id);
            
            // Simpan file jika dilampirkan langsung di form handover
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->storeAs('attachments/' . $cpb->id, time() . '_' . $file->getClientOriginalName(), 'public');
                $cpb->attachments()->create([
                    'uploaded_by' => auth()->id(),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'description' => 'Dokumen Serah Terima'
                ]);
            }
            
            $oldStatus = $cpb->status;
            $nextStatus = $cpb->getNextDepartment();

            $cpb->update([
                'status' => $nextStatus,
                'current_department_id' => $receiver->id,
                'entered_current_status_at' => now(),
                'is_rework' => false,
            ]);
            
            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $nextStatus,
                'handed_by' => auth()->id(),
                'received_by' => $receiver->id,
                'handed_at' => now(),
                'notes' => $request->notes,
            ]);
            
            event(new CPBHandover($cpb, auth()->user(), $receiver));
            DB::commit();
            
            return redirect()->route('dashboard')->with('success', 'CPB berhasil diserahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        if (!Gate::allows('create', CPB::class)) {
            abort(403);
        }

        $validated = $request->validate([
            'batch_number' => 'required|unique:cpbs,batch_number|max:50',
            'type' => 'required|in:pengolahan,pengemasan',
            'product_name' => 'required|max:100',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:20480', 
        ]);

        DB::beginTransaction();
        try {
            $cpb = CPB::create([
                'batch_number' => $validated['batch_number'],
                'type' => $validated['type'],
                'product_name' => $validated['product_name'],
                'created_by' => auth()->id(),
                'current_department_id' => auth()->id(),
                'status' => 'rnd',
                'entered_current_status_at' => now(),
            ]);

            // Simpan Dokumen Awal (RND)
            $file = $request->file('file');
            $path = $file->storeAs('attachments/' . $cpb->id, time() . '_' . $file->getClientOriginalName(), 'public');
            $cpb->attachments()->create([
                'uploaded_by' => auth()->id(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'description' => 'Dokumen Awal CPB'
            ]);

            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => 'created',
                'to_status' => 'rnd',
                'handed_by' => auth()->id(),
                'handed_at' => now(),
                'notes' => 'CPB dibuat oleh RND'
            ]);

            event(new CPBCreated($cpb));
            DB::commit();

            return redirect()->route('cpb.show', $cpb)->with('success', 'CPB Berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, CPB $cpb)
    {
        if (!Gate::allows('handover', $cpb)) {
            abort(403);
        }

        $request->validate(['rework_note' => 'required|string|max:1000']);
        $previousStatus = $cpb->getPreviousDepartment();

        $lastTransfer = $cpb->handoverLogs()->where('to_status', $cpb->status)->latest()->first();
        $receiverId = $lastTransfer ? $lastTransfer->handed_by : User::where('role', $previousStatus)->first()->id;

        DB::beginTransaction();
        try {
            $oldStatus = $cpb->status;
            $cpb->update([
                'status' => $previousStatus,
                'current_department_id' => $receiverId,
                'is_rework' => true,
                'rework_note' => $request->rework_note,
                'entered_current_status_at' => now(),
            ]);

            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $previousStatus,
                'handed_by' => auth()->id(),
                'received_by' => $receiverId,
                'handed_at' => now(),
                'notes' => '[REJECT/REWORK]: ' . $request->rework_note,
            ]);

            DB::commit();
            return redirect()->route('cpb.show', $cpb)->with('warning', 'Batch dikembalikan ke ' . $previousStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function create()
    {
        // Cek apakah user bisa create CPB
        if (!Gate::allows('create', CPB::class)) {
            abort(403, 'Unauthorized action.');
        }

        return view('cpb.create');
    }

    public function getLastNumber(Request $request)
    {
        $type = $request->query('type');
        $year = now()->year;
        $typeCode = ($type === 'pengolahan') ? 'P' : 'K';

        // Pola yang dicari: CPB-2026-P... atau CPB-2026-K...
        $prefix = "CPB-{$year}-{$typeCode}";

        // Cari batch_number TERBESAR yang mengandung prefix tersebut
        $lastBatch = \App\Models\CPB::where('batch_number', 'LIKE', $prefix . '%')
            ->orderBy('batch_number', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastBatch) {
            // Ambil 3 digit terakhir dari string batch_number
            // Contoh: CPB-2026-P005 -> substr(..., -3) menghasilkan "005"
            $lastNumber = (int) substr($lastBatch->batch_number, -3);
        }

        return response()->json([
            'last_number' => $lastNumber
        ]);
    }

    public function edit(CPB $cpb)
    {
        // Gunakan Gate untuk check permission
        if (!Gate::allows('update', $cpb)) {
            abort(403, 'Unauthorized action.');
        }

        return view('cpb.edit', compact('cpb'));
    }

    public function update(Request $request, CPB $cpb)
    {
        // Gunakan Gate untuk check permission
        if (!Gate::allows('update', $cpb)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'product_name' => 'required|max:100',
        ]);

        $cpb->update($validated);

        return redirect()->route('cpb.show', $cpb)
            ->with('success', 'CPB berhasil diperbarui.');
    }

    public function release(CPB $cpb)
    {
        // Gunakan Gate untuk check permission
        if (!Gate::allows('release', $cpb)) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $cpb->status;
            $cpb->update([
                'status' => 'released',
                'entered_current_status_at' => now(),
                'is_overdue' => false,
            ]);
            
            // Create handover log for release
            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => 'released',
                'handed_by' => auth()->id(),
                'handed_at' => now(),
                'notes' => 'CPB telah direlease',
            ]);

            DB::commit();

            return redirect()->route('cpb.show', $cpb)
                ->with('success', 'CPB berhasil direlease.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal me-release CPB: ' . $e->getMessage());
        }
    }

    public function destroy(CPB $cpb)
    {
        // Gunakan Gate untuk check permission
        if (!Gate::allows('delete', $cpb)) {
            abort(403, 'Unauthorized action.');
        }

        $cpb->delete();

        return redirect()->route('cpb.index')
            ->with('success', 'CPB berhasil dihapus.');
    }

    public function uploadAttachment(Request $request, CPB $cpb)
    {
        $user = auth()->user();

        if ($user->role !== $cpb->status && !$user->isSuperAdmin()) {
            return back()->with('error', 'Hanya bagian ' . strtoupper($cpb->status) . ' yang diizinkan mengunggah dokumen di tahap ini.');
        }

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getClientOriginalExtension();

            $path = $file->storeAs(
                'attachments/' . $cpb->id,
                time() . '_' . $fileName,
                'public'
            );

            $cpb->attachments()->create([
                'uploaded_by' => $user->id,
                'file_path' => $path,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'description' => $request->description
            ]);

            return back()->with('success', 'File berhasil diunggah.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunggah file: ' . $e->getMessage());
        }
    }

    public function destroyAttachment(CPB $cpb, CPBAttachment $attachment)
{
    // Cek izin (Hanya pemegang dokumen aktif atau SuperAdmin)
    if (auth()->user()->role !== $cpb->status && !auth()->user()->isSuperAdmin()) {
        abort(403, 'Anda tidak memiliki akses untuk menghapus lampiran ini.');
    }

    try {
        // 1. Hapus file fisik dari folder storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // 2. Hapus data dari database
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menghapus lampiran: ' . $e->getMessage());
    }
}

    public function requestToQA(CPB $cpb)
    {
        // Hanya PPIC yang boleh request
        if (auth()->user()->role !== 'ppic') {
            abort(403, 'Unauthorized. Only PPIC can request.');
        }

        // Hanya bisa request jika status sedang di QA
        if ($cpb->status !== 'qa') {
            return back()->with('error', 'CPB tidak sedang di QA.');
        }

        // Cari user QA
        $qaUsers = User::where('role', 'qa')->get();

        // Kirim notifikasi ke semua QA
        foreach ($qaUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'cpb_request',
                'message' => 'PPIC meminta CPB ' . $cpb->batch_number . ' untuk diproses.',
                'cpb_id' => $cpb->id,
            ]);
        }

        return back()->with('success', 'Permintaan telah dikirim ke QA Team.');
    }
}
