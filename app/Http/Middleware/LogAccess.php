<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $log = new \App\Models\LogAccess();

        $log->create([
            'endereco_ip' => $request->ip(),
            'rota_solicitada' => $request->path(),
            'user_id' => auth()->user() ? auth()->user()->id : 0
        ]);

        return $next($request);
    }
}
