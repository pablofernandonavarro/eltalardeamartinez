<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$viviana = App\Models\User::where('email', 'like', '%viviana%')->first();
$today = now()->toDateString();

echo "=== Entradas de Viviana hoy ({$today}) ===\n\n";

$entries = App\Models\PoolEntry::query()
    ->where('unit_id', 30) // Unit de Viviana
    ->where('user_id', $viviana->id)
    ->whereDate('entered_at', $today)
    ->orderBy('entered_at', 'desc')
    ->get();

echo "Total entradas hoy: {$entries->count()}\n\n";

foreach($entries as $entry) {
    echo "ID: {$entry->id}\n";
    echo "  Entered at: {$entry->entered_at}\n";
    echo "  Exited at: " . ($entry->exited_at ?? 'ABIERTA - Aún está adentro') . "\n";
    echo "  Pool ID: {$entry->pool_id}\n";
    echo "  Guests count: {$entry->guests_count}\n";
    echo "  Resident ID: " . ($entry->resident_id ?? 'null (es usuario)') . "\n";
    echo "\n";
}

echo "=== Pass de hoy ===\n";
$pass = App\Models\PoolDayPass::where('user_id', $viviana->id)
    ->whereDate('date', $today)
    ->first();

if ($pass) {
    echo "Token: {$pass->token}\n";
    echo "Usado: " . ($pass->used_at ?? 'NO') . "\n";
    echo "Pool entry ID: " . ($pass->pool_entry_id ?? 'null') . "\n";
}
