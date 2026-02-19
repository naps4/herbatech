<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CPB;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Super admin bypass all checks
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }
        
        // For CPB-related actions
        if ($request->route('cpb')) {
            $cpb = $request->route('cpb');
            
            if ($cpb instanceof CPB) {
                // RND can only edit their own CPBs before QA
                if ($user->role === 'rnd' && $request->isMethod('put') || $request->isMethod('patch')) {
                    if ($cpb->status !== 'rnd' || $cpb->created_by !== $user->id) {
                        abort(403, 'You can only edit CPBs you created while in RND status.');
                    }
                }
                
                // Users can only see CPBs in their department or passed through
                $allowedStatuses = $this->getAllowedStatuses($user);
                if (!in_array($cpb->status, $allowedStatuses)) {
                    abort(403, 'You do not have permission to view this CPB.');
                }
            }
        }
        
        return $next($request);
    }
    
    private function getAllowedStatuses($user)
    {
        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa', 'released'];
        $userIndex = array_search($user->role, $flow);
        
        if ($userIndex === false) {
            return [];
        }
        
        // Users can see current and previous statuses
        return array_slice($flow, 0, $userIndex + 2);
    }
}