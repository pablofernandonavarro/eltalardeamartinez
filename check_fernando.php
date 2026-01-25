<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::where('email', 'escalas.fernando@example.com')->first();

if ($user) {
    echo "✅ Usuario encontrado:\n";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "QR Token: " . ($user->qr_token ?? 'NULL') . "\n";
    echo "Approved: " . ($user->approved_at ? 'SI - ' . $user->approved_at : 'NO') . "\n";

    $unitUsers = $user->currentUnitUsers()->get();
    echo "Unit Users count: " . $unitUsers->count() . "\n";

    if ($unitUsers->count() > 0) {
        foreach ($unitUsers as $uu) {
            echo "  - Unit ID: {$uu->unit_id} - {$uu->unit->full_identifier}\n";
            echo "    Role: {$uu->role}\n";
            echo "    Active: " . ($uu->left_at ? 'NO' : 'SI') . "\n";
        }
    }
} else {
    echo "❌ Usuario NO encontrado con email: fernando@gmail.com\n";
}

// Buscar si existe con variaciones
echo "\n🔍 Buscando variaciones del email...\n";
$variations = User::where('email', 'like', '%fernando%')->get();
foreach ($variations as $v) {
    echo "  - {$v->email} (ID: {$v->id}, Name: {$v->name})\n";
}
