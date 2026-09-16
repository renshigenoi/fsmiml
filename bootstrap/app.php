<?php

use App\Modules\Assignment\Exceptions\InvalidAssignment;
use App\Modules\Tracking\Exceptions\InvalidTrackingToken;
use App\Modules\WorkOrder\Exceptions\InvalidWorkOrderTransition;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // D1: percayai ONLY reverse proxy lokal (nginx aaPanel -> php-fpm).
        // Tanpa ini, kalau nanti dipasang CDN/proxy eksternal, request()->ip()
        // = IP proxy untuk semua user: throttle login/pin jadi per-CDN (self-DoS),
        // IP di audit_logs & signed URL scheme jadi salah. 'at:' khusus loopback
        // agar tidak bisa dispoof via header X-Forwarded-By dari klien.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidAssignment|InvalidTrackingToken|InvalidWorkOrderTransition $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }
        });
    })->create();
