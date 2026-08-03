<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = Illuminate\Support\Facades\DB::connection('pgsql');

echo '--- work_orders terbaru ---'.PHP_EOL;
foreach ($db->table('work_orders')->orderByDesc('id')->limit(5)->get(['id', 'number', 'status', 'created_at']) as $row) {
    echo sprintf('id=%s number=%s status=%s created=%s', $row->id, $row->number, $row->status, $row->created_at).PHP_EOL;
}

echo '--- failed_jobs terbaru ---'.PHP_EOL;
$failedCount = $db->table('failed_jobs')->count();
echo 'failed_count='.$failedCount.PHP_EOL;
foreach ($db->table('failed_jobs')->orderByDesc('id')->limit(5)->get(['uuid', 'failed_at', 'exception']) as $row) {
    $firstLine = preg_split('/\R/', $row->exception)[0] ?? '';
    echo sprintf('uuid=%s failed_at=%s err=%s', mb_substr($row->uuid, 0, 8), $row->failed_at, mb_substr($firstLine, 0, 160)).PHP_EOL;
}

echo '--- notifications ---'.PHP_EOL;
echo 'notifications_count='.$db->table('notifications')->count().PHP_EOL;
