<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$unitId = 30; // Torre 11-1102
$pool = App\Models\Pool::first();
$monthStart = now()->startOfMonth();
$monthEnd = now()->endOfMonth();

echo "Unit ID: {$unitId}\n";
echo "Pool ID: {$pool->id}\n";
echo "Mes: " . now()->format('Y-m') . "\n\n";

// Método 1: COUNT(DISTINCT ...)
$count1 = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->where('pool_entries.unit_id', $unitId)
    ->where('pool_entries.pool_id', $pool->id)
    ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->selectRaw('COUNT(DISTINCT pool_entry_guests.pool_guest_id) as total')
    ->value('total');

echo "Método COUNT(DISTINCT): {$count1}\n\n";

// Método 2: Listar invitados únicos
$guests = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->join('pool_guests', 'pool_guests.id', '=', 'pool_entry_guests.pool_guest_id')
    ->where('pool_entries.unit_id', $unitId)
    ->where('pool_entries.pool_id', $pool->id)
    ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->select('pool_guests.id', 'pool_guests.name', DB::raw('COUNT(*) as entries'))
    ->groupBy('pool_guests.id', 'pool_guests.name')
    ->get();

echo "Invitados únicos (días de semana):\n";
foreach($guests as $guest) {
    echo "  - {$guest->name} (ID: {$guest->id}, Entradas: {$guest->entries})\n";
}
echo "\nTotal: " . $guests->count() . " invitados únicos\n";
