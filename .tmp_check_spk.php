<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new App\Modules\Legacy\Services\LegacyDataSourceService;

$needle = 'SO/IML/2608/M0004952';

// 1. Persis seperti endpoint (pakai filter sales_type/status bawaan)
$rows = $service->sales($needle, 10);
echo 'dengan_filter_rows='.count($rows).PHP_EOL;

// 2. Tanpa filter sales_type/status
$db = Illuminate\Support\Facades\DB::connection('sales');
$pattern = '%'.$needle.'%';
$raw = $db->select(
    "SELECT serial, spk_no, sales_type, status, sales_invoice_no_car, sales_order_no_car
     FROM sales
     WHERE spk_no ILIKE ? OR sales_invoice_no_car ILIKE ? OR sales_order_no_car ILIKE ?
     LIMIT 10",
    [$pattern, $pattern, $pattern],
);
echo 'tanpa_filter_rows='.count($raw).PHP_EOL;
foreach ($raw as $row) {
    echo sprintf("serial=%s spk=%s type=%s status=%s inv_car=%s so_car=%s", $row->serial, $row->spk_no, $row->sales_type, $row->status, $row->sales_invoice_no_car, $row->sales_order_no_car).PHP_EOL;
}

// 3. Cari potongan nomor lain, kalau SPK persis tidak ketemu
$loose = $db->select(
    "SELECT serial, spk_no, sales_type, status FROM sales WHERE spk_no ILIKE ? LIMIT 10",
    ['%M0004952%'],
);
echo 'loose_M0004952_rows='.count($loose).PHP_EOL;
foreach ($loose as $row) {
    echo sprintf("serial=%s spk=%s type=%s status=%s", $row->serial, $row->spk_no, $row->sales_type, $row->status).PHP_EOL;
}

// 4. Cari berdasarkan bulan 2608 sebagai pembanding
$month = $db->select(
    "SELECT serial, spk_no, sales_type, status FROM sales WHERE spk_no ILIKE '%2608%' LIMIT 10",
);
echo 'spk_2608_rows='.count($month).PHP_EOL;
foreach ($month as $row) {
    echo sprintf("serial=%s spk=%s type=%s status=%s", $row->serial, $row->spk_no, $row->sales_type, $row->status).PHP_EOL;
}
