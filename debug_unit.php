<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$unit = App\Models\Unit::where('number', '1102')
    ->whereHas('building', function($q) {
        $q->where('name', 'Torre 11');
    })->first();

if (!$unit) {
    echo "Unit not found\n";
    exit;
}

echo "Unit ID: {$unit->id}\n\n";

echo "=== USERS ===\n";
$users = $unit->currentUsers()->get();
foreach ($users as $u) {
    $role = $u->pivot->role ?? 'N/A';
    echo "ID: {$u->id} | Name: [{$u->name}] | Role: {$role}\n";
}

echo "\n=== RESIDENTS ===\n";
$residents = App\Models\Resident::where('unit_id', $unit->id)->active()->get();
foreach ($residents as $r) {
    $userId = $r->user_id ?? 'null';
    $rel = $r->relationship ?? 'N/A';
    echo "ID: {$r->id} | Name: [{$r->name}] | UserID: {$userId} | Relationship: {$rel}\n";
}
