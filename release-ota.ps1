<#
.SYNOPSIS
    Rilis bundle OTA (Capgo self-hosted) dalam satu perintah.

.DESCRIPTION
    Script ini otomatis:
      1. Build web bundle (mobile/ -> dist/)
      2. Sync dist/ -> public/mobile/ (agar URL /mobile ikut terupdate) + bump cache SW
      3. Zip isi dist/ dengan pemisah path yang benar (forward-slash, index.html di root)
      4. Menentukan versi bundle berikutnya (tertinggi + 1, minimal 2)
      5. (Opsional) memperbarui MOBILE_BUNDLE_VERSION di .env lokal
      6. (Opsional) commit + push perubahan /mobile ke origin (VPS tinggal pull)
      7. Menampilkan langkah upload ke VPS

.PARAMETER NoGitPush
    Jangan auto commit+push perubahan public/mobile (sync & bump cache tetap jalan).

.PARAMETER Version
    Paksa nomor versi tertentu. Default: otomatis (tertinggi + 1).

.PARAMETER OutDir
    Folder output zip. Default: <root>\bundles

.PARAMETER NoEnvUpdate
    Jangan mengubah .env lokal (MOBILE_BUNDLE_VERSION).

.PARAMETER SkipBuild
    Lewati `npm run build` (pakai dist/ yang sudah ada). Untuk test cepat.

.EXAMPLE
    .\release-ota.ps1

.EXAMPLE
    .\release-ota.ps1 -Version 7
#>
[CmdletBinding()]
param(
    [int]$Version = 0,
    [string]$OutDir = '',
    [switch]$NoEnvUpdate,
    [switch]$SkipBuild,
    [switch]$NoGitPush
)

$ErrorActionPreference = 'Stop'
$Root = if ($PSScriptRoot) { $PSScriptRoot } else { $PWD.Path }
if ([string]::IsNullOrWhiteSpace($OutDir)) {
    $OutDir = Join-Path $Root 'bundles'
}
$Dist = Join-Path $Root 'mobile\dist'

# ---------- 1. Tentukan versi ----------
function Get-BundleVersion {
    $highest = 0
    if (Test-Path $OutDir) {
        $highest = Get-ChildItem -Path $OutDir -Filter '*.zip' -ErrorAction SilentlyContinue |
            ForEach-Object { $base = [System.IO.Path]::GetFileNameWithoutExtension($_.Name); [int]$base } |
            Measure-Object -Maximum |
            Select-Object -ExpandProperty Maximum
        if ($null -eq $highest) { $highest = 0 }
    }

    $envFile = Join-Path $Root '.env'
    $envVer = 0
    if (Test-Path $envFile) {
        $match = Select-String -Path $envFile -Pattern '^MOBILE_BUNDLE_VERSION=' -ErrorAction SilentlyContinue
        if ($match) {
            $raw = ($match.Line -replace '^MOBILE_BUNDLE_VERSION=', '').Trim()
            if ($raw -match '^\d+$') { $envVer = [int]$raw }
        }
    }

    $next = [Math]::Max($highest, $envVer) + 1
    if ($next -lt 2) { $next = 2 } # bundle versi 1 sengaja tidak memunculkan URL update
    return $next
}

if ($Version -lt 2) {
    $Version = Get-BundleVersion
    Write-Host "Versi bundle: $Version (otomatis)" -ForegroundColor Cyan
} else {
    Write-Host "Versi bundle: $Version (dipaksa)" -ForegroundColor Cyan
}

# ---------- 2. Build ----------
if (-not $SkipBuild) {
    Write-Host 'Build web bundle...' -ForegroundColor Cyan
    Push-Location (Join-Path $Root 'mobile')
    try {
        npm run build
        if ($LASTEXITCODE -ne 0) { throw "npm run build gagal (exit $LASTEXITCODE)" }
    } finally {
        Pop-Location
    }
}

if (-not (Test-Path (Join-Path $Dist 'index.html'))) {
    throw 'dist/index.html tidak ditemukan. Jalankan npm run build dulu.'
}

# ---------- 2b. Sync /mobile (public/mobile) + bump cache service worker ----------
$PublicMobile = Join-Path $Root 'public\mobile'
Write-Host 'Sync public/mobile (agar /mobile via browser ikut terupdate)...' -ForegroundColor Cyan
if (Test-Path $PublicMobile) { Remove-Item -Recurse -Force $PublicMobile }
New-Item -ItemType Directory -Force -Path $PublicMobile | Out-Null
Copy-Item -Path (Join-Path $Dist '*') -Destination $PublicMobile -Recurse -Force
if (-not (Test-Path (Join-Path $PublicMobile 'index.html'))) {
    throw 'Sync public/mobile gagal: index.html tidak terkopi.'
}

# Naikkan konstanta cache SW (fsm-mobile-vN -> vN+1) supaya PWA membuang shell lama.
$MobileController = Join-Path $Root 'app\Http\Controllers\Web\MobileController.php'
$enc = New-Object System.Text.UTF8Encoding($false)
$mc = [System.IO.File]::ReadAllText($MobileController)
$swMatch = [regex]::Match($mc, 'fsm-mobile-v(\d+)')
if ($swMatch.Success) {
    $newCache = 'fsm-mobile-v' + ([int]$swMatch.Groups[1].Value + 1)
    $mc = $mc -replace 'fsm-mobile-v\d+', $newCache
    [System.IO.File]::WriteAllText($MobileController, $mc, $enc)
    Write-Host "Cache service worker di-bump: $($swMatch.Value) -> $newCache" -ForegroundColor Green
} else {
    Write-Host 'Peringatan: konstanta fsm-mobile-vN tidak ditemukan di MobileController — leapkan bump cache.' -ForegroundColor Yellow
}

# ---------- 3. Zip (wajib forward-slash, jangan Compress-Archive) ----------
New-Item -ItemType Directory -Force -Path $OutDir | Out-Null
$ZipPath = Join-Path $OutDir "$Version.zip"
$ZipPath = [System.IO.Path]::GetFullPath($ZipPath)
$DistAbs = [System.IO.Path]::GetFullPath($Dist)

Write-Host "Membuat $ZipPath ..." -ForegroundColor Cyan
tar -a -c -f $ZipPath -C $DistAbs index.html assets
if ($LASTEXITCODE -ne 0) { throw "tar gagal membuat zip (exit $LASTEXITCODE)" }

# Verifikasi: tidak boleh ada entry dengan backslash
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
try {
    $bad = $zip.Entries | Where-Object { $_.FullName -like '*\*' }
    if ($bad) {
        throw 'Zip mengandung entry backslash — bundle akan gagal di Android. Gunakan tar seperti di script ini.'
    }
    Write-Host ('Isi zip ({0} entry):' -f $zip.Entries.Count) -ForegroundColor Cyan
    $zip.Entries | Select-Object -First 6 -ExpandProperty FullName | ForEach-Object { Write-Host "  $_" }
} finally {
    $zip.Dispose()
}

# ---------- 4. Update .env lokal (opsional) ----------
if (-not $NoEnvUpdate) {
    $envFile = Join-Path $Root '.env'
    if (Test-Path $envFile) {
        $content = [System.IO.File]::ReadAllLines($envFile)
        $found = $false
        for ($i = 0; $i -lt $content.Length; $i++) {
            if ($content[$i] -match '^MOBILE_BUNDLE_VERSION=') {
                $content[$i] = "MOBILE_BUNDLE_VERSION=$Version"
                $found = $true
                break
            }
        }
        if (-not $found) { $content += "MOBILE_BUNDLE_VERSION=$Version" }
        [System.IO.File]::WriteAllLines($envFile, $content, (New-Object System.Text.UTF8Encoding($false)))
        Write-Host ".env lokal: MOBILE_BUNDLE_VERSION=$Version" -ForegroundColor Green
    }
}

# ---------- 4b. Commit + push perubahan /mobile (public/mobile + controller) ----------
if ($NoGitPush) {
    Write-Host 'Lewati auto push (-NoGitPush). Ingat push manual agar /mobile ikut ter-update di VPS.' -ForegroundColor Yellow
} else {
    Push-Location $Root
    try {
        $branch = (git rev-parse --abbrev-ref HEAD).Trim()
        $paths = @('public/mobile', 'app/Http/Controllers/Web/MobileController.php')
        git add -- $paths
        # Bandingkan hanya path milik /mobile, agar perubahan staged lain tidak ikut.
        $changed = (git diff --cached --name-only -- $paths)
        if ($changed) {
            Write-Host "Commit perubahan /mobile (branch $branch)..." -ForegroundColor Cyan
            git commit -m "release-ota: build /mobile bundle v$Version + bump cache SW" -- $paths
            if ($LASTEXITCODE -ne 0) { throw 'git commit gagal.' }

            if ($branch -eq 'main') {
                Write-Host 'Push ke origin/main...' -ForegroundColor Cyan
                git push origin main
                if ($LASTEXITCODE -ne 0) { throw 'git push gagal - cek koneksi/kredensial, lalu coba git push origin main manual.' }
                Write-Host 'Push OK. Di VPS jalankan ./deploy.sh (git pull) untuk update /mobile via browser.' -ForegroundColor Green
            } else {
                Write-Host "Branch aktif bukan main ($branch) - commit dibuat tapi TIDAK di-push. Merge/push manual ya." -ForegroundColor Yellow
            }
        } else {
            Write-Host 'Tidak ada perubahan /mobile yang perlu di-commit (build sama dengan sebelumnya).' -ForegroundColor Yellow
        }
    } finally {
        Pop-Location
    }
}

# ---------- 5. Ringkasan ----------
Write-Host ''
Write-Host '================ RILIS OTA SIAP ================' -ForegroundColor Green
Write-Host "Zip      : $ZipPath"
Write-Host ''
Write-Host 'Langkah di VPS:' -ForegroundColor Yellow
Write-Host '  0. /mobile (browser) : ./deploy.sh  (perubahan sudah di-push script ini)'
Write-Host "  1. Upload ke: /www/wwwroot/fsm.indomotorlestari.com/storage/app/private/bundles/$Version.zip"
Write-Host "  2. .env VPS : MOBILE_BUNDLE_VERSION=$Version"
Write-Host '  3. Jalankan : php artisan config:clear'
Write-Host '  4. Cek      : curl -s https://fsm.indomotorlestari.com/api/v1/app/version'
Write-Host '               (bundle_url kini SIGNED — curl langsung ke /app/bundle/N dibalas 403, itu memang benar)'
Write-Host '================================================' -ForegroundColor Green
