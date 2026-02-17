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
        
        if (!$nextStatus) {
            return back()->with('error', 'Tidak dapat melakukan handover. CPB sudah di status akhir.');
        }
        
        $nextUsers = User::where('role', $nextStatus)->get();
        
        return view('handover.create', compact('cpb', 'nextStatus', 'nextUsers'));
    }
    
    public function store(Request $request, CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $user = auth()->user();
        $nextStatus = $cpb->getNextDepartment();
        
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
            
            // Verify receiver is in correct department
            if ($receiver->role !== $nextStatus) {
                throw new \Exception('Penerima tidak berada di departemen yang benar.');
            }
            
            // Update CPB status
            $oldStatus = $cpb->status;
            $cpb->update([
                'status' => $nextStatus,
                'current_department_id' => $receiver->id,
                'entered_current_status_at' => now(),
                'is_overdue' => false,
                'overdue_since' => null,
            ]);
            
            // Create handover log
            $handoverLog = HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => $oldStatus,
                'to_status' => $nextStatus,
                'handed_by' => $user->id,
                'handed_at' => now(),
                'was_overdue' => $cpb->is_overdue,
                'notes' => $request->notes,
            ]);
            
            // Trigger event
            event(new CPBHandover($cpb, $user, $receiver));
            
            DB::commit();
            
            return redirect()->route('dashboard')
                           ->with('success', 'CPB berhasil diserahkan ke ' . $nextStatus);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan handover: ' . $e->getMessage());
        }
    }
    
    public function receive(Request $request, HandoverLog $handoverLog)
    {
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