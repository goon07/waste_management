<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogSupabaseConnection
{
    public function handle(Request $request, Closure $next)
    {
        try {
            DB::connection()->getPdo();
            Log::channel('supabase')->info('Supabase connection successful', [
                'database' => DB::connection()->getDatabaseName(),
            ]);
        } catch (\Exception $e) {
            Log::channel('supabase')->error('Supabase connection failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}