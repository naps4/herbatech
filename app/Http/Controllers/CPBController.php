<?php

namespace App\Http\Controllers;

use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Mpdf\Mpdf;
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

        // 1. Filter Status Aktif vs Released
        if ($request->get('status') === 'active') {
            $query->where('status', '!=', 'released');
        } elseif ($request->get('status') === 'released') {
            $query->where('status', 'released');
        }

        // 2. Filter Tanggal
        if ($request->filled('start_date')) {
            $date = $request->start_date;
            $query->where(function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                    ->orWhereHas('handoverLogs', function ($sub) use ($date) {
                        $sub->whereDate('handed_at', $date);
                    });
            });
        }

        if ($request->has('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }

        if ($request->get('rework') === 'true') {
            $query->where('is_rework', true);
        }

        if ($request->has('overdue') && $request->overdue == 'true') {
            $query->where('is_overdue', true)
                ->where('status', '!=', 'released');
        }

        // 3. Role-based filtering (MENGGUNAKAN ROLE, BUKAN ID)
        if (!$user->isSuperAdmin() && (!$user->isQA() || $user->role !== 'qa') && $user->role !== 'rnd') {
            $query->where(function ($q) use ($user) {
                // --- BAGIAN INI DIUBAH MENJADI MENGGUNAKAN $user->role ---
                $q->where('status', $user->role) // Status CPB ada di departemen role ini
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('handoverLogs', function ($subQuery) use ($user) {
                        $subQuery->where('from_status', $user->role) 
                            ->orWhere('to_status', $user->role);
                    });
            });
        }

        // 4. Eksekusi Paginate
        $cpbs = $query->orderBy('is_overdue', 'desc')
            ->orderBy('entered_current_status_at', 'asc')
            ->paginate(7)
            ->withQueryString();

        return view('cpb.index', compact('cpbs'));
    }

    public function exportPdf()
    {
        $user = auth()->user();
        $query = CPB::where('status', '!=', 'released')->latest();

        // Terapkan Filter Visibilitas Role (MENGGUNAKAN ROLE, AGAR DATA MUNCUL)
        if (!$user->isSuperAdmin() && (!$user->isQA() || $user->role !== 'qa') && $user->role !== 'rnd') {
            $query->where(function ($q) use ($user) {
                $q->where('status', $user->role) // Berdasarkan ROLE departemen
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('handoverLogs', function ($subQuery) use ($user) {
                        $subQuery->where('from_status', $user->role) // PERNAH di departemen ini
                            ->orWhere('to_status', $user->role);
                    });
            });
        }

        $cpbs = $query->get();

        $html = view('cpb.export-pdf', compact('cpbs'))->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('Daftar-CPB-Aktif.pdf', 'D');
    }

    public function exportAllPdf()
    {
        $user = auth()->user();
        $query = \App\Models\CPB::where('status', '!=', 'released')->latest();

        // Terapkan Filter Visibilitas Role (MENGGUNAKAN ROLE, AGAR DATA MUNCUL)
        if (!$user->isSuperAdmin() && (!$user->isQA() || $user->role !== 'qa') && $user->role !== 'rnd') {
            $query->where(function ($q) use ($user) {
                $q->where('status', $user->role)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('handoverLogs', function ($subQuery) use ($user) {
                        $subQuery->where('from_status', $user->role)
                            ->orWhere('to_status', $user->role);
                    });
            });
        }

        $cpbs = $query->get();

        $html = view('cpb.export-all-pdf', compact('cpbs'))->render();

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('Daftar-CPB-Aktif.pdf', 'D');
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

        $creator = auth()->user();
        $currentDeptId = $creator->id;

        if ($creator->role !== 'rnd') {
            $firstRndUser = User::where('role', 'rnd')->first();
            if ($firstRndUser) {
                $currentDeptId = $firstRndUser->id;
            }
        }

        DB::beginTransaction();
        try {
            $cpb = CPB::create([
                'batch_number' => $validated['batch_number'],
                'type' => $validated['type'],
                'product_name' => $validated['product_name'],
                'created_by' => $creator->id,
                'current_department_id' => $currentDeptId,
                'status' => 'rnd',
                'entered_current_status_at' => now(),
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->storeAs('attachments/' . $cpb->id, time() . '_' . $file->getClientOriginalName(), 'public');
                $cpb->attachments()->create([
                    'uploaded_by' => $creator->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'description' => 'Dokumen Awal CPB oleh ' . strtoupper($creator->role)
                ]);
            }

            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => 'created',
                'to_status' => 'rnd',
                'handed_by' => $creator->id,
                'received_by' => $currentDeptId,
                'handed_at' => now(),
                'notes' => 'CPB dibuat dan diteruskan ke bagian RND'
            ]);

            event(new CPBCreated($cpb));
            DB::commit();

            return redirect()->route('cpb.show', $cpb)->with('success', 'CPB berhasil dibuat dan dialokasikan ke bagian RND.');
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
        if (!$previousStatus) {
            return back()->with('error', 'Tidak dapat melakukan reject ke tahap sebelumnya.');
        }

        $lastTransfer = $cpb->handoverLogs()
            ->where('to_status', $previousStatus)
            ->latest('handed_at')
            ->first();

        $receiverId = null;
        if ($lastTransfer && $lastTransfer->received_by) {
            $receiverId = $lastTransfer->received_by;
        } else {
            $fallbackUser = User::where('role', $previousStatus)->first();
            $receiverId = $fallbackUser ? $fallbackUser->id : $cpb->created_by;
        }

        DB::beginTransaction();
        try {
            $oldStatus = $cpb->status;
            $cpb->update([
                'status' => $previousStatus,
                'current_department_id' => $receiverId,
                'is_rework' => true,
                'rework_note' => $request->rework_note,
                'entered_current_status_at' => now(),
                'is_overdue' => false
            ]);

            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $previousStatus,
                'handed_by' => auth()->id(),
                'received_by' => $receiverId,
                'handed_at' => now(),
                'received_at' => now(),
                'notes' => '[REJECT/REWORK]: ' . $request->rework_note,
            ]);

            DB::commit();
            return redirect()->route('cpb.show', $cpb)->with('warning', 'Batch dikembalikan ke ' . strtoupper($previousStatus));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function create()
    {
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

        $prefix = "CPB-{$year}-{$typeCode}";

        $lastBatch = \App\Models\CPB::where('batch_number', 'LIKE', $prefix . '%')
            ->orderBy('batch_number', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastBatch) {
            $lastNumber = (int) substr($lastBatch->batch_number, -3);
        }

        return response()->json([
            'last_number' => $lastNumber
        ]);
    }

    public function edit(CPB $cpb)
    {
        if (!Gate::allows('update', $cpb)) {
            abort(403, 'Unauthorized action.');
        }
        return view('cpb.edit', compact('cpb'));
    }

    public function update(Request $request, CPB $cpb)
    {
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
        // Gembok 1: Cek Permission Gate standar
        if (!Gate::allows('release', $cpb)) {
            abort(403, 'Unauthorized action.');
        }

        // Gembok 2: Pastikan status saat ini BUKAN rework
        if ($cpb->is_rework) {
            return back()->with('error', 'Gagal merilis produk! Dokumen ini masih dalam status Rework/Perbaikan.');
        }

        // Gembok 3: Pastikan dokumen ini benar-benar ada di tahap QA Final
        if (!$cpb->is_final_qa) {
            return back()->with('error', 'Dokumen belum memenuhi syarat QA Final (Harus melewati QC terlebih dahulu).');
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
                'notes' => 'CPB telah diluluskan (Released)',
            ]);

            DB::commit();
            return redirect()->route('cpb.show', $cpb)->with('success', 'CPB berhasil diluluskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal me-release CPB: ' . $e->getMessage());
        }
    }

    public function destroy(CPB $cpb)
    {
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
            'file' => 'required|file|max:10240',
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
        $user = auth()->user();
        $isOwner = ($user->id === $attachment->uploaded_by);
        $isAdmin = $user->isSuperAdmin();

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus lampiran ini.');
        }

        if (!$isAdmin) {
            $isCurrentRoleHolder = ($user->role === $cpb->status);
            $isReworkStatus = $cpb->is_rework;

            if (!$isCurrentRoleHolder && !$isReworkStatus) {
                return back()->with('error', 'Gagal! Anda tidak dapat menghapus dokumen karena batch sudah diteruskan ke departemen lain (Hanya bisa dihapus saat status Rework).');
            }
        }

        try {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            $attachment->delete();
            return back()->with('success', 'Lampiran berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus lampiran: ' . $e->getMessage());
        }
    }

    public function requestToQA(CPB $cpb)
    {
        if (auth()->user()->role !== 'ppic') {
            abort(403, 'Unauthorized. Only PPIC can request.');
        }

        if ($cpb->status !== 'qa') {
            return back()->with('error', 'CPB tidak sedang di QA.');
        }

        $qaUsers = User::where('role', 'qa')->get();

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