<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$entries = App\Models\PoolEntry::latest()->take(5)->get();

echo "Últimas 5 entradas de pileta:\n";
echo str_repeat("-", 80) . "\n";

foreach($entries as $e) {
    echo sprintf(
        "ID: %d | Pool: %d | Unit: %d | Resident: %s | Entered: %s | Exited: %s\n",
        $e->id,
        $e->pool_id,
        $e->unit_id,
        $e->resident_id ?? 'null',
        $e->entered_at,
        $e->exited_at ?? 'null'
    );
}

echo "\nEntradas abiertas hoy para residente ID 3:\n";
$openEntries = App\Models\PoolEntry::query()
    ->where('resident_id', 3)
    ->whereDate('entered_at', now()->toDateString())
    ->whereNull('exited_at')
    ->get();

if ($openEntries->count() > 0) {
    foreach ($openEntries as $e) {
        echo sprintf("ID: %d | Entered: %s\n", $e->id, $e->entered_at);
    }
} else {
    echo "No hay entradas abiertas\n";
}
