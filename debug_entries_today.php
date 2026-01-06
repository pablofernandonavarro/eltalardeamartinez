<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Torre 11-1102 es unit_id 30
$unitId = 30;
$today = '2026-01-06';

$entries = App\Models\PoolEntry::where('unit_id', $unitId)
    ->whereDate('entered_at', $today)
    ->orderBy('entered_at')
    ->get();

echo "=== INGRESOS HOY ({$today}) - Unit ID: {$unitId} ===\n\n";
echo "Total ingresos: " . $entries->count() . "\n\n";

foreach ($entries as $entry) {
    $userName = $entry->user ? $entry->user->name : 'N/A';
    $residentName = $entry->resident ? $entry->resident->name : 'N/A';
    $exitStatus = $entry->exited_at ? 'SALIÓ' : 'ADENTRO';
    
    echo "ID: {$entry->id} | {$entry->entered_at} | Pool: {$entry->pool->name}\n";
    echo "  User: {$userName} | Resident: {$residentName}\n";
    echo "  Guests: {$entry->guests_count} | Status: {$exitStatus}\n\n";
}
