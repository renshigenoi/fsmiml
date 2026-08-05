<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppVersionController extends Controller
{
    public function show(): JsonResponse
    {
        $bundleVersion = (int) config('mobile.bundle_version', 1);

        // Jika bundle_url tidak diset manual, arahkan ke endpoint bundle bawaan.
        $bundleUrl = config('mobile.bundle_url')
            ?: ($bundleVersion > 1 ? url("/api/v1/app/bundle/{$bundleVersion}") : null);

        return response()->json([
            // NATIVE: perlu install ulang APK bila berbeda.
            'version' => (string) config('mobile.native_version', '1.0.0'),
            'download_url' => config('mobile.download_url'),
            'min_version' => (string) config('mobile.min_native_version', '1.0.0'),
            'update_required' => (bool) config('mobile.update_required', false),

            // BUNDLE: bisa live-update tanpa install ulang.
            'bundle_version' => $bundleVersion,
            'bundle_url' => $bundleUrl,

            'checked_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Sajikan file bundle live-update (.zip) untuk Capgo self-hosted.
     * Simpan file di storage/app/bundles/{version}.zip.
     */
    public function bundle(int $version): StreamedResponse
    {
        $path = "bundles/{$version}.zip";

        abort_unless(Storage::disk('local')->exists($path), 404, 'Bundle tidak ditemukan.');

        return Storage::disk('local')->download($path, "fsm-bundle-{$version}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }
}
