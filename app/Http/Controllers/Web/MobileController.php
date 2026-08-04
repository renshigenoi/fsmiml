<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MobileController extends Controller
{
    public function app(): View
    {
        return view('mobile.app');
    }

    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'FSM Teknisi - Indomotor Lestari',
            'short_name' => 'FSM Teknisi',
            'description' => 'Aplikasi kerja teknisi Indomotor Lestari: terima pekerjaan, pantau perjalanan, dan laporkan progres pemasangan.',
            'start_url' => '/mobile',
            'display' => 'standalone',
            'background_color' => '#f4f6fb',
            'theme_color' => '#0b1f4b',
            'icons' => [
                [
                    'src' => '/assets/images/iml-logo.png',
                    'sizes' => '668x148',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
            ],
        ]);
    }

    public function serviceWorker(): Response
    {
        $script = <<<'JS'
const CACHE = 'fsm-mobile-v4';
const SHELL = ['/mobile', '/mobile/manifest.webmanifest', '/assets/images/iml-logo.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET' || !request.url.startsWith(self.location.origin)) {
        return;
    }
    if (request.url.includes('/api/')) {
        return; // jangan cache API
    }
    event.respondWith(
        caches.match(request).then((cached) => {
            const fetched = fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => cached);
            return cached || fetched;
        }),
    );
});
JS;

        return response($script, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
