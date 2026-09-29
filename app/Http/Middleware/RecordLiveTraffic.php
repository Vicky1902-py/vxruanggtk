<?php

namespace App\Http\Middleware;

use App\Services\ServerTelemetryService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordLiveTraffic
{
    /**
     * Merekam request web aktif untuk live traffic telemetri Super Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 1);

        // Jangan rekam file asset statis atau panggilan polling telemetri internal
        if (!$request->is('css/*', 'js/*', 'img/*', 'fonts/*', 'favicon.ico', 'god/telemetry', 'god/server/live-traffic')) {
            ServerTelemetryService::recordRequest($request, $response->getStatusCode(), $durationMs);
        }

        return $response;
    }
}
