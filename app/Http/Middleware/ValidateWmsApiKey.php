<?php

namespace App\Http\Middleware;

use Closure;

class ValidateWmsApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // ดึง API Key จาก Header หรือ Query Parameter
        $apiKey = $request->header('X-API-Key') 
                   ?? $request->query('api_key');

        // ตรวจสอบ API Key จาก .env
        // $validApiKey = env('WMS_API_KEY');
        $validApiKey = config('wms.api_key');

        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing API Key'
            ], 401);
        }

        return $next($request);
    }
}
