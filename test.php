<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fechaVal = '2025-05-31';
$parsedFecha = \Carbon\Carbon::parse($fechaVal)->format('Y-m-d');
$mes = null;
$año = null;
if ($parsedFecha) {
    $carbonDate = \Carbon\Carbon::parse($parsedFecha);
    if (!$mes) $mes = $carbonDate->month;
    if (!$año) $año = $carbonDate->year;
}

echo "MES: " . var_export($mes, true) . "\n";
echo "ANO: " . var_export($año, true) . "\n";
