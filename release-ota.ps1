<#
.SYNOPSIS
    Rilis bundle OTA (Capgo self-hosted) dalam satu perintah.

.DESCRIPTION
    Script ini otomatis:
      1. Build web bundle (mobile/ -> dist/)
      2. Zip isi dist/ dengan pemisah path yang benar (forward-slash, index.html di root)
      3. Menentukan versi bundle berikutnya (tertinggi + 1, minimal 2)
      4. (Opsional) memperbarui MOBILE_BUNDLE_VERSION di .env lokal
      5. Menampilkan langkah upload ke VPS

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
    [switch]$SkipBuild
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

# ---------- 5. Ringkasan ----------
Write-Host ''
Write-Host '================ RILIS OTA SIAP ================' -ForegroundColor Green
Write-Host "Zip      : $ZipPath"
Write-Host ''
Write-Host 'Langkah di VPS:' -ForegroundColor Yellow
Write-Host "  1. Upload ke: /www/wwwroot/fsm.indomotorlestari.com/storage/app/private/bundles/$Version.zip"
Write-Host "  2. .env VPS : MOBILE_BUNDLE_VERSION=$Version"
Write-Host '  3. Jalankan : php artisan config:clear'
Write-Host "  4. Cek      : curl -I https://fsm.indomotorlestari.com/api/v1/app/bundle/$Version"
Write-Host '================================================' -ForegroundColor Green
