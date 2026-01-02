<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$res = App\Models\Resident::find(1);
echo "Residente: " . $res->name . "\n";
echo "QR Token: " . $res->qr_token . "\n";
echo "Unit ID: " . $res->unit_id . "\n";

echo "\nBuscando entrada abierta...\n";
$entry = App\Models\PoolEntry::query()
    ->where('resident_id', 1)
    ->whereDate('entered_at', now()->toDateString())
    ->whereNull('exited_at')
    ->latest('entered_at')
    ->first();

if ($entry) {
    echo "Entrada abierta encontrada:\n";
    echo "  ID: " . $entry->id . "\n";
    echo "  Pool ID: " . $entry->pool_id . "\n";
    echo "  Unit ID: " . $entry->unit_id . "\n";
    echo "  Entered at: " . $entry->entered_at . "\n";
} else {
    echo "No hay entrada abierta\n";
}
