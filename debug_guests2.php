<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'viviana.patalano@gmail.com')->first();
$unitId = $user->currentUnitUsers()->first()->unit_id ?? null;

echo "Buscando invitados que ingresaron en enero 2026 en unit_id={$unitId}\n\n";

$entries = DB::table('pool_entry_guests')
    ->join('pool_entries', 'pool_entries.id', '=', 'pool_entry_guests.pool_entry_id')
    ->join('pool_guests', 'pool_guests.id', '=', 'pool_entry_guests.pool_guest_id')
    ->join('users', 'users.id', '=', 'pool_guests.created_by_user_id')
    ->where('pool_entries.unit_id', $unitId)
    ->whereMonth('pool_entries.entered_at', 1)
    ->whereYear('pool_entries.entered_at', 2026)
    ->whereRaw('DAYOFWEEK(pool_entries.entered_at) NOT IN (1, 7)')
    ->select([
        'pool_guests.id as guest_id',
        'pool_guests.name as guest_name',
        'pool_guests.unit_id as guest_unit_id',
        'users.name as created_by',
        'users.email as created_by_email',
        'pool_entries.entered_at',
        DB::raw('DAYOFWEEK(pool_entries.entered_at) as day_of_week')
    ])
    ->orderBy('pool_entries.entered_at', 'desc')
    ->get();

echo "Total de entradas en días de semana: " . $entries->count() . "\n\n";

foreach($entries as $entry) {
    echo "- {$entry->guest_name} (ID: {$entry->guest_id})\n";
    echo "  Creado por: {$entry->created_by} ({$entry->created_by_email})\n";
    echo "  Unit ID del invitado: {$entry->guest_unit_id}\n";
    echo "  Fecha: {$entry->entered_at} (Día: {$entry->day_of_week})\n\n";
}
