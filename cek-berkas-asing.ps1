# cek-berkas-asing.ps1
# Jalankan dari ROOT proyek (folder yang berisi file "artisan").
#   Cek saja (tidak mengubah apa pun):   powershell -ExecutionPolicy Bypass -File .\cek-berkas-asing.ps1
#   Pindahkan berkas asing ke karantina: powershell -ExecutionPolicy Bypass -File .\cek-berkas-asing.ps1 -Pindah
param([switch]$Pindah)

$root = (Get-Location).Path
if (-not (Test-Path (Join-Path $root 'artisan'))) { Write-Host 'Jalankan dari folder root proyek (yang berisi file artisan).' -ForegroundColor Red; exit 1 }

$manifestFile = Join-Path $PSScriptRoot 'sodentna-manifest.txt'
if (-not (Test-Path $manifestFile)) { Write-Host 'sodentna-manifest.txt tidak ditemukan di folder skrip.' -ForegroundColor Red; exit 1 }

$ok = New-Object 'System.Collections.Generic.HashSet[string]' ([StringComparer]::OrdinalIgnoreCase)
Get-Content $manifestFile | Where-Object { $_ -and -not $_.StartsWith('#') } | ForEach-Object { [void]$ok.Add($_.Trim().Replace('/', '\')) }

# Berkas bawaan Laravel yang sah
@('app\Http\Controllers\Controller.php', 'app\Providers\AppServiceProvider.php', 'database\factories\UserFactory.php', 'database\seeders\DatabaseSeeder.php',
  'database\migrations\0001_01_01_000000_create_users_table.php', 'database\migrations\0001_01_01_000001_create_cache_table.php', 'database\migrations\0001_01_01_000002_create_jobs_table.php',
  'resources\views\welcome.blade.php', 'resources\css\app.css', 'resources\js\app.js', 'resources\js\bootstrap.js',
  'tests\TestCase.php', 'tests\Unit\ExampleTest.php', 'routes\web.php', 'routes\console.php') | ForEach-Object { [void]$ok.Add($_) }

$scan = @('app', 'routes', 'resources\views', 'resources\css', 'resources\js', 'database\migrations', 'database\seeders', 'database\factories', 'tests', 'lang')
$foreign = @()
foreach ($dir in $scan) {
    $full = Join-Path $root $dir
    if (-not (Test-Path $full)) { continue }
    Get-ChildItem $full -Recurse -File | Where-Object { $_.Name -ne '.gitkeep' } | ForEach-Object {
        $rel = $_.FullName.Substring($root.Length + 1)
        if (-not $ok.Contains($rel)) { $foreign += $rel }
    }
}

Write-Host ''
if ($foreign.Count -eq 0) { Write-Host 'Tidak ada berkas asing ditemukan.' -ForegroundColor Green }
else {
    Write-Host ("Ditemukan {0} berkas yang BUKAN bagian proyek ini:" -f $foreign.Count) -ForegroundColor Yellow
    $foreign | Sort-Object | ForEach-Object { Write-Host "  $_" }
}

if ($Pindah -and $foreign.Count -gt 0) {
    $q = Join-Path $root '_karantina_ai_lain'
    foreach ($rel in $foreign) {
        $dest = Join-Path $q $rel
        New-Item -ItemType Directory -Force -Path (Split-Path $dest) | Out-Null
        Move-Item -LiteralPath (Join-Path $root $rel) -Destination $dest -Force
    }
    Write-Host ''
    Write-Host ("Selesai: {0} berkas dipindah ke _karantina_ai_lain (tidak dihapus)." -f $foreign.Count) -ForegroundColor Green
}
elseif ($foreign.Count -gt 0) {
    Write-Host ''
    Write-Host 'Belum ada yang diubah. Tambahkan -Pindah untuk memindahkan berkas di atas ke folder karantina.' -ForegroundColor Cyan
}

# Peringatan untuk berkas konfigurasi yang sering ikut tertimpa
Write-Host ''
Write-Host 'Pemeriksaan tambahan:' -ForegroundColor Cyan
$boot = Join-Path $root 'bootstrap\app.php'
if (Test-Path $boot) {
    $hit = Select-String -Path $boot -Pattern 'pin\.property|property\.context|password\.fresh|alias\(|append:|prepend:' -SimpleMatch:$false
    if ($hit) { Write-Host '  bootstrap\app.php berisi middleware/alias tambahan, periksa barisnya:' -ForegroundColor Yellow; $hit | ForEach-Object { Write-Host ('    ' + $_.Line.Trim()) } }
    else { Write-Host '  bootstrap\app.php: tidak ada alias middleware tambahan.' }
}
$comp = Join-Path $root 'composer.json'
if (Test-Path $comp) {
    $hit = Select-String -Path $comp -Pattern 'dompdf|maatwebsite|spatie|barryvdh|livewire'
    if ($hit) { Write-Host '  composer.json memuat paket tambahan (bukan bawaan, periksa apakah memang Anda pasang):' -ForegroundColor Yellow; $hit | ForEach-Object { Write-Host ('    ' + $_.Line.Trim()) } }
    else { Write-Host '  composer.json: tidak ada paket tambahan yang mencurigakan.' }
}
$web = Join-Path $root 'routes\web.php'
if (Test-Path $web) {
    Write-Host '  Isi routes\web.php (harus hanya require auth, public, owner, tenant):'
    Get-Content $web | Where-Object { $_.Trim() -ne '' } | ForEach-Object { Write-Host ('    ' + $_) }
}
Write-Host ''
