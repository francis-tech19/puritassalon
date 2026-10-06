<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check()
    {
        $startTime = microtime(true);
        try {
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'success' => true,
                'system' => "Purita's Beauty Lounge Salon Management System",
                'status' => 'HEALTHY',
                'timestamp' => now()->toIso8601String(),
                'dbLatencyMs' => $latency,
                'aiApiKeyConfigured' => ! empty(env('GEMINI_API_KEY')),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'UNHEALTHY',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
