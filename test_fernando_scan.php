<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Resident;

$token = '2b1f458e-356d-47a4-a244-599fe0cf5bc6';

echo "🔍 Probando token: {$token}\n\n";

// Simular el proceso de loadPass()
$token = strtolower(trim($token));
$token = preg_replace('/\s+/', '', $token);
$token = preg_replace('/[\x00-\x1F\x7F]/u', '', $token);

echo "Token limpio: {$token}\n\n";

// 1. Buscar como residente
echo "1️⃣ Buscando como QR de residente...\n";
$resident = Resident::query()
    ->whereRaw('LOWER(qr_token) = ?', [$token])
    ->active()
    ->first();

if ($resident) {
    echo "✅ Encontrado como residente:\n";
    echo "   ID: {$resident->id}\n";
    echo "   Name: {$resident->name}\n";
    echo "   Can have personal QR: " . ($resident->canHavePersonalQr() ? 'SI' : 'NO') . "\n";
} else {
    echo "❌ No encontrado como residente\n";
}

echo "\n";

// 2. Buscar como usuario
echo "2️⃣ Buscando como QR de usuario...\n";
$user = User::query()
    ->whereRaw('LOWER(qr_token) = ?', [$token])
    ->whereNotNull('approved_at')
    ->first();

if ($user) {
    echo "✅ Encontrado como usuario:\n";
    echo "   ID: {$user->id}\n";
    echo "   Name: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Approved: " . ($user->approved_at ? 'SI' : 'NO') . "\n";

    $unitUser = $user->currentUnitUsers()->first();
    if ($unitUser) {
        echo "   Unit: {$unitUser->unit->full_identifier}\n";
        echo "   ✅ DEBERÍA FUNCIONAR\n";
    } else {
        echo "   ❌ No tiene unidad activa\n";
    }
} else {
    echo "❌ No encontrado como usuario\n";

    // Debug: buscar sin case sensitive
    echo "\n🔍 Debug - Buscando sin lowercase...\n";
    $userDebug = User::where('qr_token', '2b1f458e-356d-47a4-a244-599fe0cf5bc6')
        ->whereNotNull('approved_at')
        ->first();
    if ($userDebug) {
        echo "✅ Encontrado (sin lowercase): {$userDebug->name}\n";
        echo "   QR Token en BD: {$userDebug->qr_token}\n";
    }
}

echo "\n";

// 3. Buscar como day-pass
echo "3️⃣ Buscando como day-pass...\n";
$pass = \App\Models\PoolDayPass::query()
    ->whereRaw('LOWER(token) = ?', [$token])
    ->first();

if ($pass) {
    echo "✅ Encontrado como day-pass\n";
} else {
    echo "❌ No encontrado como day-pass\n";
}
