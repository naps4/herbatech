<?php

namespace App\Http\Controllers;

use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Events\CPBCreated;
use App\Events\CPBHandover;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CPBController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Hapus authorizeResource jika bermasalah, kita handle manual
        // $this->authorizeResource(CPB::class, 'cpb');
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CPB::query();
        
        // --- PERBAIKAN: Default hanya tampilkan yang belum released ---
        if ($request->get('status') === 'active') {
            $query->where('status', '!=', 'released');
        } elseif ($request->get('status') === 'released') {
            $query->where('status', 'released');
        } elseif ($request->get('status') === 'all') {
            // Jangan tambahkan where status apa pun agar semua muncul
        } else {
            $query->where('status', '!=', 'released');
        }
        
        // Filters lainnya
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', $request->start_date);
        }
        
        if ($request->has('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }
        
        // Role-based filtering (Tetap gunakan perbaikan role sebelumnya)
        if (!$user->isSuperAdmin() && !$user->isQA()) {
            $query->where(function($q) use ($user) {
                $q->where('status', $user->role) // Filter sesuai role departemen
                  ->orWhere('created_by', $user->id);
            });
        }
        
        // Overdue filter
        if ($request->has('overdue') && $request->overdue == 'true') {
            $query->where('is_overdue', true);
        }
        
        $cpbs = $query->orderBy('is_overdue', 'desc')
                      ->orderBy('entered_current_status_at', 'asc')
                      ->paginate(20)
                      ->withQueryString();
        
        return view('cpb.index', compact('cpbs'));
    }
    
    public function create()
    {
        // Cek apakah user bisa create CPB
        if (!Gate::allows('create', CPB::class)) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('cpb.create');
    }
    
    public function store(Request $request)
    {
        // Cek apakah user bisa create CPB
        if (!Gate::allows('create', CPB::class)) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'batch_number' => 'required|unique:cpbs|max:50',
            'type' => 'required|in:pengolahan,pengemasan',
            'product_name' => 'required|max:100',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:20480', // Limit 20MB
            'description' => 'nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        
        try {
            $cpb = CPB::create([
                'batch_number' => $validated['batch_number'],
                'type' => $validated['type'],
                'product_name' => $validated['product_name'],
                'schedule_duration' => 0, // Default 0 for flexible duration
                'created_by' => auth()->id(),
                'current_department_id' => auth()->id(),
                'status' => 'rnd',
                'entered_current_status_at' => now(),
            ]);

            // Handle Attachment
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $fileType = $file->getClientOriginalExtension();
                
                $path = $file->storeAs(
                    'attachments/' . $cpb->id,
                    time() . '_' . $fileName,
                    'public'
                );
        
                $cpb->attachments()->create([
                    'uploaded_by' => auth()->id(),
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'file_type' => $fileType,
                    'description' => 'Dokumen Awal CPB'
                ]);
            }
            
            // Create initial handover log
            HandoverLog::create([
                'cpb_id' => $cpb->id,
                'from_status' => 'created',
                'to_status' => 'rnd',
                'handed_by' => auth()->id(),
                'handed_at' => now(),
                'notes' => 'CPB dibuat'
            ]);
            
            // Trigger event
            event(new CPBCreated($cpb));
            
            DB::commit();
            
            return redirect()->route('cpb.show', $cpb)
                           ->with('success', 'CPB berhasil dibuat.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat CPB: ' . $e->getMessage());
        }
    }
    
    public function show(CPB $cpb)
    {
        // Debug information
        $user = auth()->user();
        Log::info('CPB Show Attempt:', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_email' => $user->email,
            'cpb_id' => $cpb->id,
            'cpb_status' => $cpb->status,
            'cpb_created_by' => $cpb->created_by,
            'cpb_current_dept' => $cpb->current_department_id,
        ]);
        
        // Temporary: Allow all for testing
        // if (!Gate::allows('view', $cpb)) {
        //     abort(403, 'Unauthorized action.');
        // }
        
        $handoverLogs = $cpb->handoverLogs()
                           ->with(['sender', 'receiver'])
                           ->orderBy('created_at', 'desc')
                           ->get();
        
        $nextDepartment = $cpb->getNextDepartment();
        $canHandover = Gate::allows('handover', $cpb); // Temporary
        
        return view('cpb.show', compact('cpb', 'handoverLogs', 'nextDepartment', 'canHandover'));
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
    
    public function handoverForm(CPB $cpb)
    {
        // Gunakan Gate untuk check permission
        if (!Gate::allows('handover', $cpb)) {
            abort(403, 'Unauthorized action.');
        }
        
        $nextStatus = $cpb->getNextDepartment();
        
        if (!$nextStatus) {
            return back()->with('error', 'Tidak dapat melakukan handover. CPB sudah di status akhir.');
        }
        
        $nextUsers = User::where('role', $nextStatus)->get();
        
        return view('cpb.handover', compact('cpb', 'nextStatus', 'nextUsers'));
    }
    
    public function handover(Request $request, CPB $cpb)
    {
        // Gunakan Gate untuk check permission
        if (!Gate::allows('handover', $cpb)) {
            abort(403, 'Unauthorized action.');
        }
        
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
                'is_rework' => false, 
                'rework_note' => null,
            ]);
            
            // Create handover log
            HandoverLog::create([
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
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240', // Tambahkan mimes
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs(
            'attachments/' . $cpb->id,
            time() . '_' . $fileName,
            'public'
        );

        // Create record
        $cpb->attachments()->create([
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'description' => $request->description
        ]);

        return back()->with('success', 'File berhasil diunggah.');
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