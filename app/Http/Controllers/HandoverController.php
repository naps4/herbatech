<?php

namespace App\Http\Controllers;

use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\CPBHandover;

class HandoverController extends Controller
{
    public function create(CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $nextStatus = $cpb->getNextDepartment();
        
        // Cek apakah status selanjutnya adalah 'released' (tahap akhir)
        if (!$nextStatus || $nextStatus === 'released') {
            return back()->with('error', 'Tahap selanjutnya adalah Pelulusan (Release). Silakan gunakan tombol Release Product.');
        }
        
        $nextUsers = User::where('role', $nextStatus)->get();
        
        return view('handover.create', compact('cpb', 'nextStatus', 'nextUsers'));
    }
    
    public function store(Request $request, CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $user = auth()->user();
        $nextStatus = $cpb->getNextDepartment();
        
        // Validasi alur: Jika QA Final mencoba handover lewat form (bukan tombol release)
        if ($nextStatus === 'released') {
            return back()->with('error', 'Gunakan tombol "Release Product" untuk menyelesaikan batch ini.');
        }
        
        if (!$nextStatus) {
            return back()->with('error', 'Tidak dapat melakukan handover. CPB sudah di status akhir.');
        }
        
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $receiver = User::findOrFail($request->receiver_id);
            
            // Validasi departemen penerima
            if ($receiver->role !== $nextStatus) {
                throw new \Exception('Penerima tidak berada di departemen yang benar (' . strtoupper($nextStatus) . ').');
            }
            
            $oldStatus = $cpb->status;

            // 1. Update CPB status dan pemegang saat ini
            $cpb->update([
                'status' => $nextStatus,
                'current_department_id' => $receiver->id,
                'entered_current_status_at' => now(),
                'is_overdue' => false,
                'overdue_since' => null,
                'is_rework' => false, 
                'rework_note' => null,
            ]);
            
            // 2. Create handover log (Sistem langsung menganggap "Diterima" saat diserahkan)
            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $nextStatus,
                'handed_by' => $user->id,
                'received_by' => $receiver->id, // Langsung isi receiver
                'handed_at' => now(),
                'received_at' => now(), // Otomatis diterima untuk efisiensi
                'was_overdue' => $cpb->is_overdue,
                'notes' => $request->notes,
            ]);
            
            // Trigger event notifikasi
            event(new CPBHandover($cpb, $user, $receiver));
            
            DB::commit();
            
            return redirect()->route('cpb.show', $cpb)
                            ->with('success', 'CPB berhasil diserahkan ke ' . strtoupper($nextStatus));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan handover: ' . $e->getMessage());
        }
    }

    public function rework(Request $request, CPB $cpb)
    {
        $request->validate(['rework_note' => 'required|string|min:10']);
        
        $targetStatus = $cpb->getPreviousDepartment();
        
        // CARI USER: Ambil orang terakhir yang memegang batch ini di departemen PPIC
        $lastHandler = $cpb->handoverLogs()
            ->where('to_status', $targetStatus)
            ->orderBy('handed_at', 'desc')
            ->first();

        // Jika tidak ketemu (misal data lama), kembalikan ke creator
        $receiverId = $lastHandler ? $lastHandler->received_by : $cpb->created_by;

        DB::transaction(function() use ($cpb, $targetStatus, $receiverId, $request) {
            $oldStatus = $cpb->status;
            
            $cpb->update([
                'status' => $targetStatus,
                'current_department_id' => $receiverId, // SANGAT PENTING: Pindah kepemilikan ke PPIC
                'entered_current_status_at' => now(),
                'is_rework' => true,
                'rework_note' => $request->rework_note,
                'is_overdue' => false // Reset waktu karena ini pengerjaan ulang
            ]);

            \App\Models\HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $targetStatus,
                'handed_by' => auth()->id(),
                'received_by' => $receiverId,
                'handed_at' => now(),
                'received_at' => now(),
                'notes' => '[REWORK] ' . $request->rework_note
            ]);
        });

        return redirect()->route('cpb.show', $cpb)->with('success', 'Batch dikembalikan ke ' . strtoupper($targetStatus));
    }
    
    public function receive(Request $request, HandoverLog $handoverLog)
    {
        // Method ini opsional jika Anda menggunakan sistem otomatis diterima di store
        $this->authorize('receive', $handoverLog);
        
        if ($handoverLog->received_at) {
            return back()->with('error', 'Handover sudah diterima sebelumnya.');
        }
        
        $handoverLog->update([
            'received_by' => auth()->id(),
            'received_at' => now(),
            'duration_in_hours' => now()->diffInHours($handoverLog->handed_at)
        ]);
        
        return back()->with('success', 'Handover berhasil dikonfirmasi.');
    }
}