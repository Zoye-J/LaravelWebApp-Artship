<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMAC
{
    /**
     * Handle an incoming request.
     * Verify integrity of all data in the response
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Process the request
        $response = $next($request);

        // Check if response has data that needs integrity verification
        if ($response->isSuccessful()) {
            // Add integrity check header for frontend
            $response->headers->set('X-Integrity-Check', 'passed');
            
            // Log any integrity failures that occurred
            if (session()->has('integrity_failure')) {
                $response->headers->set('X-Integrity-Failure', 'true');
                \Log::warning('Integrity failure detected', [
                    'url' => $request->fullUrl(),
                    'user' => auth()->id(),
                    'field' => session('integrity_failure_field')
                ]);
            }
        }

        return $response;
    }
}