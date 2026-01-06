<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'viviana.patalano@gmail.com')->first();
if (!$user) {
    echo "Usuario no encontrado\n";
    exit;
}

$unitId = $user->currentUnitUsers()->first()->unit_id ?? null;
echo "Unit ID: {$unitId}\n\n";

$pool = App\Models\Pool::first();
$monthStart = now()->startOfMonth();
$monthEnd = now()->endOfMonth();

// Contar con DISTINCT
$countDistinct = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->where('pool_entries.unit_id', $unitId)
    ->where('pool_entries.pool_id', $pool->id)
    ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->distinct('pool_entry_guests.pool_guest_id')
    ->count('pool_entry_guests.pool_guest_id');

echo "COUNT DISTINCT: {$countDistinct}\n\n";

// Listar invitados únicos con detalles
$weekdays = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->where('pool_entries.unit_id', $unitId)
    ->where('pool_entries.pool_id', $pool->id)
    ->whereBetween('pool_entries.entered_at', [$monthStart, $monthEnd])
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->select('pool_entry_guests.pool_guest_id', DB::raw('COUNT(*) as count'))
    ->groupBy('pool_entry_guests.pool_guest_id')
    ->get();

echo "Invitados únicos días de semana: " . $weekdays->count() . "\n";
foreach($weekdays as $w) {
    $guest = App\Models\PoolGuest::find($w->pool_guest_id);
    echo "  - " . ($guest->name ?? 'N/A') . " (ID: {$w->pool_guest_id}, Entradas: {$w->count})\n";
}
