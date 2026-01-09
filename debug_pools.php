<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$unitId = 30;
$monthStart = now()->startOfMonth();
$monthEnd = now()->endOfMonth();

echo "Todos los invitados que ingresaron en enero 2026 con unit_id={$unitId}:\n\n";

$entries = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->join('pool_guests', 'pool_guests.id', '=', 'pool_entry_guests.pool_guest_id')
    ->join('pools', 'pools.id', '=', 'pool_entries.pool_id')
    ->where('pool_entries.unit_id', $unitId)
    ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->select([
        'pool_guests.id as guest_id',
        'pool_guests.name as guest_name',
        'pools.id as pool_id',
        'pools.name as pool_name',
        'pool_entries.entered_at',
        DB::raw('DAYOFWEEK(pool_entries.entered_at) as day_of_week')
    ])
    ->orderBy('pool_entries.entered_at', 'desc')
    ->get();

foreach($entries as $entry) {
    echo "- {$entry->guest_name} (Guest ID: {$entry->guest_id})\n";
    echo "  Pool: {$entry->pool_name} (Pool ID: {$entry->pool_id})\n";
    echo "  Fecha: {$entry->entered_at} (Día semana: {$entry->day_of_week})\n\n";
}

echo "Total entradas: " . $entries->count() . "\n\n";

// Contar por pool
echo "Conteo por pool:\n";
$byPool = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->join('pools', 'pools.id', '=', 'pool_entries.pool_id')
    ->where('pool_entries.unit_id', $unitId)
    ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->select('pools.id', 'pools.name', DB::raw('COUNT(DISTINCT pool_entry_guests.pool_guest_id) as unique_guests'))
    ->groupBy('pools.id', 'pools.name')
    ->get();

foreach($byPool as $pool) {
    echo "  Pool {$pool->id} ({$pool->name}): {$pool->unique_guests} invitados únicos\n";
}
