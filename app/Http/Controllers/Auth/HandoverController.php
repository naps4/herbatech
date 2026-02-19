<?php
// app/Http/Controllers/HandoverController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Events\CPBHandover;

class HandoverController extends Controller
{
    /**
     * Menampilkan form handover
     */
    public function create(CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $nextStatus = $cpb->getNextDepartment();

        if (!$nextStatus) {
            return redirect()->back()
                ->with('error', 'Tidak dapat melakukan handover. CPB sudah di status akhir.');
        }

        // LOGIKA PERBAIKAN:
        // Karena di database hanya ada role 'qa', tapi di alur sistem ada status 'qa_final'.
        // Jika tahap selanjutnya adalah 'qa_final', kita cari user dengan role 'qa'.
        $searchRole = ($nextStatus === 'qa_final') ? 'qa' : $nextStatus;
        
        $nextUsers = User::where('role', $searchRole)->get();
        
        if ($nextUsers->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada personil ditemukan untuk bagian ' . strtoupper($searchRole) . '. Pastikan user dengan role tersebut sudah terdaftar di database.');
        }
        
        return view('handover.create', compact('cpb', 'nextStatus', 'nextUsers'));
    }
    
    /**
     * Menyimpan data handover ke database
     */
    public function store(Request $request, CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $user = auth()->user();
        $nextStatus = $cpb->getNextDepartment();
        
        // Validasi input
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            // Jika batch overdue, catatan (notes) wajib diisi minimal 10 karakter
            'notes' => $cpb->is_overdue ? 'required|string|min:10|max:500' : 'nullable|string|max:500',
        ], [
            'notes.required' => 'Batch ini sedang status Overdue. Anda wajib mengisi alasan keterlambatan pada catatan handover.'
        ]);
        
        DB::beginTransaction();
        
        try {
            $receiver = User::findOrFail($validated['receiver_id']);
            
            // VERIFIKASI ROLE PENERIMA:
            // Sesuaikan ekspektasi role jika statusnya adalah 'qa_final'
            $expectedRole = ($nextStatus === 'qa_final') ? 'qa' : $nextStatus;
            
            if ($receiver->role !== $expectedRole) {
                throw new \Exception('Penerima tidak berada di departemen yang benar (Harusnya: ' . strtoupper($expectedRole) . ').');
            }
            
            $oldStatus = $cpb->status;
            $durationInHours = $cpb->duration_in_current_status;
            $wasOverdue = $cpb->is_overdue;
            
            // 1. Update Status CPB
            $cpb->update([
                'status' => $nextStatus,
                'current_department_id' => $receiver->id,
                'entered_current_status_at' => now(),
                'is_overdue' => false,
                'overdue_since' => null,
            ]);
            
            // 2. Buat Log Handover
            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $nextStatus,
                'handed_by' => $user->id,
                'received_by' => $receiver->id,
                'handed_at' => now(),
                'received_at' => now(), // Diasumsikan diterima langsung saat dikirim
                'duration_in_hours' => $durationInHours,
                'was_overdue' => $wasOverdue,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // 3. Trigger Event untuk Notifikasi
            event(new CPBHandover($cpb, $user, $receiver));
            
            DB::commit();
            
            return redirect()->route('dashboard')
                ->with('success', 'CPB berhasil diserahkan ke ' . $receiver->name . ' (' . strtoupper($nextStatus) . ')');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Gagal melakukan handover: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Mengonfirmasi penerimaan handover (jika alur manual)
     */
    public function receive($handoverId)
    {
        $handover = HandoverLog::findOrFail($handoverId);
        
        if ($handover->received_by !== auth()->id()) {
            abort(403, 'Anda bukan penerima yang ditunjuk untuk dokumen ini.');
        }
        
        if ($handover->received_at) {
            return redirect()->back()->with('error', 'Handover sudah diterima sebelumnya.');
        }
        
        $handover->update(['received_at' => now()]);
        
        return redirect()->route('cpb.show', $handover->cpb)
            ->with('success', 'Handover berhasil dikonfirmasi.');
    }
    
    /**
     * Menampilkan riwayat handover per batch
     */
    public function history(CPB $cpb)
    {
        $this->authorize('view', $cpb);
        
        $handovers = $cpb->handoverLogs()
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('handover.history', compact('cpb', 'handovers'));
    }

    /**
     * Menangani proses rework jika ditolak oleh QA
     */
    public function rework(Request $request, CPB $cpb)
    {
        $request->validate([
            'target_status' => 'required',
            'reason' => 'required|string|min:10',
        ]);

        // Cek permission khusus (Hanya QA atau Superadmin)
        if (auth()->user()->role !== 'qa' && auth()->user()->role !== 'superadmin') {
            return back()->with('error', 'Hanya bagian QA yang dapat memberikan instruksi rework.');
        }

        // Asumsi ada method rejectTo di model CPB
        $cpb->update([
            'status' => $request->target_status,
            'is_rework' => true,
            'rework_note' => $request->reason,
            'entered_current_status_at' => now()
        ]);

        return redirect()->route('cpb.show', $cpb)
            ->with('success', 'CPB telah berhasil dikembalikan ke bagian ' . strtoupper($request->target_status) . ' untuk rework.');
    }
}