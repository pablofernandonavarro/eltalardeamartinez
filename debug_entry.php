<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Buscar la entrada del 06/01/2026 09:44
$entry = App\Models\PoolEntry::whereDate('entered_at', '2026-01-06')
    ->whereTime('entered_at', '>=', '09:44:00')
    ->whereTime('entered_at', '<=', '09:45:00')
    ->with(['user', 'resident', 'guests'])
    ->first();

if (!$entry) {
    echo "Entry not found\n";
    exit;
}

echo "=== POOL ENTRY DEBUG ===\n\n";
echo "ID: {$entry->id}\n";
echo "Entered at: {$entry->entered_at}\n";
echo "User ID: " . ($entry->user_id ?? 'null') . "\n";
echo "Resident ID: " . ($entry->resident_id ?? 'null') . "\n";

if ($entry->user) {
    echo "User name: {$entry->user->name}\n";
}

if ($entry->resident) {
    echo "Resident name: {$entry->resident->name}\n";
}

echo "\nGuests:\n";
foreach ($entry->guests as $guest) {
    echo "  - {$guest->name} (ID: {$guest->id})\n";
}
