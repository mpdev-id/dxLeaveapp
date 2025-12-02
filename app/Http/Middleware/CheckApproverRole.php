<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApproverRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $approverRoles = ['Super Admin', 'SL', 'SPV', 'ASMEN', 'TL', 'Manager'];
        
        $hasApproverRole = $user->roles()
            ->whereIn('name', $approverRoles)
            ->exists();
        
        // Note: This middleware should ONLY be used with auth:sanctum on API routes
        // Do NOT use on web routes that return views (they don't have session auth)
        if (!$hasApproverRole) {
            abort(403, 'Unauthorized. Only approvers can access this page.');
        }
        
        return $next($request);
    }
}
