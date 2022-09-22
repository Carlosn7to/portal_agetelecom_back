<?php

namespace App\Http\Middleware\AgeReport;

use App\Models\AgeReport\AccessPermission;
use Closure;
use Illuminate\Http\Request;

class AccessReport
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
        $access = AccessPermission::whereUserId(auth()->user()->id)->first();

        if(isset($access->id)) {
            return $next($request);
        } else {
            return response()->json(['Usuário não tem permissão para acessar o sistema!'], 403);
        }
    }
}
