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
    public function create(CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $nextStatus = $cpb->getNextDepartment();
        
        if (!$nextStatus) {
            return redirect()->back()
                ->with('error', 'CPB sudah di status akhir.');
        }
        
        $receivers = User::where('role', $nextStatus)->get();
        
        if ($receivers->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada user di departemen tujuan.');
        }
        
        return view('handover.create', compact('cpb', 'receivers', 'nextStatus'));
    }
    
    public function store(Request $request, CPB $cpb)
    {
        $this->authorize('handover', $cpb);
        
        $user = auth()->user();
        $nextStatus = $cpb->getNextDepartment();
        
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        DB::beginTransaction();
        
        try {
            $receiver = User::findOrFail($validated['receiver_id']);
            
            // Verify receiver is in the correct department
            if ($receiver->role !== $nextStatus) {
                throw new \Exception('Receiver is not in the correct department.');
            }
            
            // Calculate duration in current status
            $durationInHours = $cpb->duration_in_current_status;
            $wasOverdue = $cpb->is_overdue;
            
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
                'received_by' => $receiver->id,
                'handed_at' => now(),
                'received_at' => now(),
                'duration_in_hours' => $durationInHours,
                'was_overdue' => $wasOverdue,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // Trigger handover event
            event(new CPBHandover($cpb, $user, $receiver));
            
            DB::commit();
            
            // Log activity
            activity()
                ->causedBy($user)
                ->performedOn($cpb)
                ->withProperties([
                    'from_status' => $oldStatus,
                    'to_status' => $nextStatus,
                    'receiver' => $receiver->name,
                ])
                ->log('Handover CPB');
            
            return redirect()->route('dashboard')
                ->with('success', 'CPB berhasil diserahkan ke ' . $receiver->name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Gagal melakukan handover: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function receive($handoverId)
    {
        $handover = HandoverLog::findOrFail($handoverId);
        
        // Check if current user is the receiver
        if ($handover->received_by !== auth()->id()) {
            abort(403, 'Anda bukan penerima handover ini.');
        }
        
        // Check if not already received
        if ($handover->received_at) {
            return redirect()->back()
                ->with('error', 'Handover sudah diterima sebelumnya.');
        }
        
        $handover->update(['received_at' => now()]);
        
        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($handover->cpb)
            ->log('Menerima handover CPB');
        
        return redirect()->route('cpb.show', $handover->cpb)
            ->with('success', 'Handover berhasil dikonfirmasi.');
    }
    
    public function history(CPB $cpb)
    {
        $this->authorize('view', $cpb);
        
        $handovers = $cpb->handoverLogs()
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('handover.history', compact('cpb', 'handovers'));
    }
}