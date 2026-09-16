<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MobileController extends Controller
{
    public function app(): \Symfony\Component\HttpFoundation\Response
    {
        // Demo web: kalau build Vue sudah ada di public/mobile, sajikan itu
        // (UI sama persis dengan APK). Fallback ke Blade lama kalau belum ada.
        $vueIndex = public_path('mobile/index.html');

        if (is_file($vueIndex)) {
            // C5: index.html hasil build memakai path RELATIF (./assets/...).
            // Di bawah subpath /mobile, browser butuh base href agar aset
            // resolve ke /mobile/assets/... — disuntik saat serve, bukan lewat
            // redirect (Laravel menormalkan trailing slash, jadi membedakan
            // '/mobile' vs '/mobile/' tidak andal). APK memuat dist sendiri
            // tanpa route ini, jadi tidak terpengaruh.
            $html = (string) file_get_contents($vueIndex);

            if (! str_contains($html, '<base ')) {
                $html = preg_replace('/<head>/i', '<head><base href="/mobile/">', $html, 1);
            }

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Cache-Control' => 'no-store',
            ]);
        }

        return response()->view('mobile.app');
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
const CACHE = 'fsm-mobile-v12';
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
