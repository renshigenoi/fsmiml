<#
.SYNOPSIS
    Build & rilis APK native FSM Teknisi dalam satu perintah.

.DESCRIPTION
    Script ini otomatis:
      1. Membaca versi saat ini dari .env.liveserver.example
      2. Menaikkan versi (patch: 1.0.0 -> 1.0.1, atau minor/major jika ditentukan)
      3. Update versionName & versionCode di android/app/build.gradle
      4. Update "version" di mobile/package.json
      5. Sync Capacitor (npm run build + cap sync android)
      6. Build APK Release via Gradle
      7. Rename APK -> fsm-teknisi-{versi}.apk dan copy ke apk-output/
      8. Update MOBILE_APP_VERSION & MOBILE_APP_DOWNLOAD_URL di .env.liveserver.example

.PARAMETER Bump
    Bagian versi yang dinaikkan: patch (default), minor, atau major.

.PARAMETER Version
    Paksa versi tertentu, misal: 2.0.0. Mengabaikan -Bump.

.PARAMETER SkipBuild
    Lewati npm build + cap sync (pakai dist/ yang sudah ada). Untuk test cepat.

.PARAMETER SkipGradle
    Lewati Gradle assembleRelease (pakai APK yang sudah ada di output Gradle).

.PARAMETER AppUrl
    Base URL server live. Default: https://fsm.indomotorlestari.com

.EXAMPLE
    .\release-native.ps1
    .\release-native.ps1 -Bump minor
    .\release-native.ps1 -Version 2.0.0
    .\release-native.ps1 -SkipBuild -SkipGradle
#>
[CmdletBinding()]
param(
    [ValidateSet('patch','minor','major')]
    [string]$Bump = 'patch',
    [string]$Version = '',
    [switch]$SkipBuild,
    [switch]$SkipGradle,
    [string]$AppUrl = 'https://fsm.indomotorlestari.com'
)

$ErrorActionPreference = 'Stop'
$Root        = if ($PSScriptRoot) { $PSScriptRoot } else { $PWD.Path }
$EnvFile     = Join-Path $Root '.env.liveserver.example'
$BuildGradle = Join-Path $Root 'mobile\android\app\build.gradle'
$PackageJson = Join-Path $Root 'mobile\package.json'
$MobileDir   = Join-Path $Root 'mobile'
$AndroidDir  = Join-Path $MobileDir 'android'
$ApkOutput   = Join-Path $Root 'apk-output'
$GradleApk   = Join-Path $AndroidDir 'app\build\outputs\apk\release\app-release.apk'

# ─────────────────────────────────────────────────────────────
# 1. Baca versi saat ini dari .env.liveserver.example
# ─────────────────────────────────────────────────────────────
Write-Host "`n[1/7] Membaca versi saat ini..." -ForegroundColor Cyan

if (-not (Test-Path $EnvFile)) { throw "File tidak ditemukan: $EnvFile" }

$envLines   = [System.IO.File]::ReadAllLines($EnvFile)
$currentVer = ''
foreach ($line in $envLines) {
    if ($line -match '^MOBILE_APP_VERSION=(.+)$') {
        $currentVer = $Matches[1].Trim()
        break
    }
}
if (-not $currentVer) { $currentVer = '1.0.0' }
Write-Host "   Versi saat ini : $currentVer" -ForegroundColor Gray

# ─────────────────────────────────────────────────────────────
# 2. Tentukan versi baru
# ─────────────────────────────────────────────────────────────
Write-Host "[2/7] Menentukan versi baru..." -ForegroundColor Cyan

function Bump-Version([string]$ver, [string]$part) {
    $parts = $ver -split '\.'
    while ($parts.Count -lt 3) { $parts += '0' }
    $major = [int]$parts[0]; $minor = [int]$parts[1]; $patch = [int]$parts[2]
    switch ($part) {
        'major' { $major++; $minor = 0; $patch = 0 }
        'minor' { $minor++; $patch = 0 }
        default { $patch++ }
    }
    return "$major.$minor.$patch"
}

if ($Version -ne '') {
    $newVer = $Version
    Write-Host "   Versi baru (paksa) : $newVer" -ForegroundColor Yellow
} else {
    $newVer = Bump-Version $currentVer $Bump
    Write-Host "   Versi baru ($Bump)  : $newVer" -ForegroundColor Green
}

# versionCode = gabungan angka semua segmen, misal 1.1.0 -> 110, 1.10.2 -> 1102
$parts = $newVer -split '\.'
while ($parts.Count -lt 3) { $parts += '0' }
$versionCode = [int]$parts[0] * 10000 + [int]$parts[1] * 100 + [int]$parts[2]

# ─────────────────────────────────────────────────────────────
# 3. Update build.gradle (versionCode & versionName)
# ─────────────────────────────────────────────────────────────
Write-Host "[3/7] Update android/app/build.gradle..." -ForegroundColor Cyan

if (-not (Test-Path $BuildGradle)) { throw "File tidak ditemukan: $BuildGradle" }
$gradle = [System.IO.File]::ReadAllText($BuildGradle)
$gradle = $gradle -replace 'versionCode\s+\d+', "versionCode $versionCode"
$gradle = $gradle -replace 'versionName\s+"[^"]+"', "versionName `"$newVer`""
[System.IO.File]::WriteAllText($BuildGradle, $gradle, (New-Object System.Text.UTF8Encoding($false)))
Write-Host "   versionCode=$versionCode  versionName=$newVer" -ForegroundColor Gray

# ─────────────────────────────────────────────────────────────
# 4. Update package.json
# ─────────────────────────────────────────────────────────────
Write-Host "[4/7] Update mobile/package.json..." -ForegroundColor Cyan

if (-not (Test-Path $PackageJson)) { throw "File tidak ditemukan: $PackageJson" }
$pkg = [System.IO.File]::ReadAllText($PackageJson)
$pkg = $pkg -replace '"version"\s*:\s*"[^"]+"', "`"version`": `"$newVer`""
[System.IO.File]::WriteAllText($PackageJson, $pkg, (New-Object System.Text.UTF8Encoding($false)))
Write-Host "   package.json version=$newVer" -ForegroundColor Gray

# ─────────────────────────────────────────────────────────────
# 5. npm build + cap sync
# ─────────────────────────────────────────────────────────────
if (-not $SkipBuild) {
    Write-Host "[5/7] npm run build + cap sync android..." -ForegroundColor Cyan
    Push-Location $MobileDir
    try {
        npm run build
        if ($LASTEXITCODE -ne 0) { throw "npm run build gagal (exit $LASTEXITCODE)" }
        npx cap sync android
        if ($LASTEXITCODE -ne 0) { throw "cap sync android gagal (exit $LASTEXITCODE)" }
    } finally { Pop-Location }
} else {
    Write-Host "[5/7] SKIP npm build + cap sync." -ForegroundColor Yellow
}

# ─────────────────────────────────────────────────────────────
# 6. Gradle assembleRelease
# ─────────────────────────────────────────────────────────────
if (-not $SkipGradle) {
    Write-Host "[6/7] Gradle assembleRelease..." -ForegroundColor Cyan
    Push-Location $AndroidDir
    try {
        .\gradlew assembleRelease
        if ($LASTEXITCODE -ne 0) { throw "Gradle assembleRelease gagal (exit $LASTEXITCODE)" }
    } finally { Pop-Location }
} else {
    Write-Host "[6/7] SKIP Gradle build." -ForegroundColor Yellow
}

$GradleApkDir = Join-Path $AndroidDir 'app\build\outputs\apk\release'
# Path APK: bisa "app-release.apk" (signed) atau "app-release-unsigned.apk" (belum ada keystore)
$GradleApk = Join-Path $GradleApkDir 'app-release.apk'
if (-not (Test-Path $GradleApk)) {
    $GradleApk = Join-Path $GradleApkDir 'app-release-unsigned.apk'
}

# ─────────────────────────────────────────────────────────────
# 7. Copy & rename APK ke apk-output/
# ─────────────────────────────────────────────────────────────
Write-Host "[7/8] Copy APK ke apk-output/..." -ForegroundColor Cyan

if (-not (Test-Path $GradleApk)) {
    throw "APK tidak ditemukan di: $GradleApkDir`nPastikan Gradle berhasil build atau gunakan -SkipGradle jika sudah ada."
}

$isUnsigned = $GradleApk -like '*unsigned*'
if ($isUnsigned) {
    Write-Host "   PERHATIAN: APK belum di-sign (app-release-unsigned.apk)." -ForegroundColor Yellow
    Write-Host "   APK ini bisa diinstall namun TIDAK bisa publish ke Play Store." -ForegroundColor Yellow
    Write-Host "   Untuk sign, tambahkan signingConfig di build.gradle." -ForegroundColor Yellow
}

New-Item -ItemType Directory -Force -Path $ApkOutput | Out-Null
$ApkName = "fsm-teknisi-$newVer.apk"
$ApkDest = Join-Path $ApkOutput $ApkName
Copy-Item -Path $GradleApk -Destination $ApkDest -Force
Write-Host "   APK disimpan: $ApkDest" -ForegroundColor Gray

# Juga copy ke public/downloads/apk/ (untuk deploy langsung ke VPS jika mount)
$PublicApkDir  = Join-Path $Root 'public\downloads\apk'
$PublicApkDest = Join-Path $PublicApkDir $ApkName
if (Test-Path $PublicApkDir) {
    Copy-Item -Path $GradleApk -Destination $PublicApkDest -Force
    Write-Host "   APK disalin ke public/downloads/apk/$ApkName" -ForegroundColor Gray
}

# ─────────────────────────────────────────────────────────────
# 8. Update .env.liveserver.example
# ─────────────────────────────────────────────────────────────
Write-Host "[8/8] Update .env.liveserver.example..." -ForegroundColor Cyan

$downloadUrl = "$AppUrl/downloads/apk/$ApkName"
$updated = $false
for ($i = 0; $i -lt $envLines.Length; $i++) {
    if ($envLines[$i] -match '^MOBILE_APP_VERSION=') {
        $envLines[$i] = "MOBILE_APP_VERSION=$newVer"; $updated = $true
    }
    if ($envLines[$i] -match '^MOBILE_APP_DOWNLOAD_URL=') {
        $envLines[$i] = "MOBILE_APP_DOWNLOAD_URL=$downloadUrl"
    }
}
if (-not $updated) { $envLines += "MOBILE_APP_VERSION=$newVer" }
[System.IO.File]::WriteAllLines($EnvFile, $envLines, (New-Object System.Text.UTF8Encoding($false)))
Write-Host "   MOBILE_APP_VERSION=$newVer" -ForegroundColor Gray
Write-Host "   MOBILE_APP_DOWNLOAD_URL=$downloadUrl" -ForegroundColor Gray

# ─────────────────────────────────────────────────────────────
# 9. Git: add → commit → push
# ─────────────────────────────────────────────────────────────
Write-Host "[9/9] Git commit & push..." -ForegroundColor Cyan

Push-Location $Root
try {
    # Pastikan ini git repo
    $isGit = git rev-parse --is-inside-work-tree 2>$null
    if ($isGit -ne 'true') { throw "Folder ini bukan git repository." }

    git add .
    if ($LASTEXITCODE -ne 0) { throw "git add gagal (exit $LASTEXITCODE)" }

    $commitMsg = "release: APK native v$newVer - update native build"
    git commit -m $commitMsg
    if ($LASTEXITCODE -ne 0) { throw "git commit gagal (exit $LASTEXITCODE)" }

    git push origin main
    if ($LASTEXITCODE -ne 0) { throw "git push gagal (exit $LASTEXITCODE)" }

    Write-Host "   git push origin main berhasil!" -ForegroundColor Green
} catch {
    Write-Host "   GIT ERROR: $_" -ForegroundColor Red
    Write-Host "   Jalankan manual:" -ForegroundColor Yellow
    Write-Host "     git add ." -ForegroundColor Yellow
    Write-Host "     git commit -m 'release: APK native v$newVer'" -ForegroundColor Yellow
    Write-Host "     git push origin main" -ForegroundColor Yellow
} finally {
    Pop-Location
}

# ─────────────────────────────────────────────────────────────
# Ringkasan
# ─────────────────────────────────────────────────────────────
Write-Host "`n=============================================" -ForegroundColor Green
Write-Host " RELEASE NATIVE SELESAI" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green
Write-Host " Versi   : $currentVer  -->  $newVer"
Write-Host " APK     : $ApkDest"
Write-Host " URL     : $downloadUrl"
Write-Host ""
Write-Host "Langkah selanjutnya di VPS:" -ForegroundColor Yellow
Write-Host "  1. git pull origin main"
Write-Host "  2. Salin nilai dari .env.liveserver.example ke .env VPS"
Write-Host "  3. Jalankan: php artisan config:clear"
Write-Host "  4. Upload APK ke: public/downloads/apk/$ApkName"
Write-Host "=============================================" -ForegroundColor Green
