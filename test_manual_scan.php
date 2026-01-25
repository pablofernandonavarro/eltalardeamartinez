<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "═══════════════════════════════════════════════\n";
echo "   TEST MANUAL: Simular escaneo de Fernando\n";
echo "═══════════════════════════════════════════════\n\n";

// Simular autenticación como bañero (buscar usuario con turno activo)
echo "1️⃣ Buscando usuario con turno activo HOY...\n";
$activeShiftToday = \App\Models\PoolShift::query()
    ->whereDate('started_at', now()->toDateString())
    ->whereNull('ended_at')
    ->with('user')
    ->first();

if ($activeShiftToday) {
    $banero = $activeShiftToday->user;
    echo "✅ Usuario con turno activo: {$banero->name} (ID: {$banero->id})\n\n";
} else {
    echo "⚠️ No hay turnos activos HOY, usando primer usuario disponible...\n";
    $banero = \App\Models\User::whereNotNull('approved_at')->first();

    if (!$banero) {
        echo "❌ No hay usuarios disponibles\n";
        exit(1);
    }

    echo "✅ Usuario: {$banero->name} (ID: {$banero->id})\n\n";
}

// Verificar turno activo
echo "2️⃣ Verificando turno activo...\n";
$activeShift = \App\Models\PoolShift::query()
    ->where('user_id', $banero->id)
    ->whereDate('started_at', now()->toDateString())
    ->whereNull('ended_at')
    ->first();

if (!$activeShift) {
    echo "⚠️ No hay turno activo para este bañero HOY\n";
    echo "Creando turno activo de prueba...\n";

    $pool = \App\Models\Pool::first();
    $activeShift = \App\Models\PoolShift::create([
        'user_id' => $banero->id,
        'pool_id' => $pool->id,
        'started_at' => now(),
    ]);
    echo "✅ Turno creado: {$pool->name}\n\n";
} else {
    echo "✅ Turno activo: {$activeShift->pool->name}\n\n";
}

// Simular el proceso de loadPass()
echo "3️⃣ Simulando loadPass() con token de Fernando...\n";
$token = '2b1f458e-356d-47a4-a244-599fe0cf5bc6';
$token = strtolower(trim($token));
$token = preg_replace('/\s+/', '', $token);
$token = preg_replace('/[\x00-\x1F\x7F]/u', '', $token);

echo "Token limpio: {$token}\n\n";

// Buscar como usuario
$user = \App\Models\User::query()
    ->whereRaw('LOWER(qr_token) = ?', [$token])
    ->whereNotNull('approved_at')
    ->first();

if (!$user) {
    echo "❌ Usuario no encontrado con QR token\n";
    exit(1);
}

echo "✅ Usuario encontrado: {$user->name}\n";

$unitUser = $user->currentUnitUsers()->first();
if (!$unitUser) {
    echo "❌ Usuario no tiene unidad activa\n";
    exit(1);
}

echo "✅ Unidad activa: {$unitUser->unit->full_identifier}\n\n";

// Verificar entrada abierta
echo "4️⃣ Verificando entrada abierta...\n";
$openEntry = \App\Models\PoolEntry::query()
    ->where('unit_id', $unitUser->unit_id)
    ->where('user_id', $user->id)
    ->whereNull('resident_id')
    ->whereDate('entered_at', now()->toDateString())
    ->whereNull('exited_at')
    ->latest('entered_at')
    ->first();

if ($openEntry) {
    echo "⚠️ Ya tiene entrada abierta (ID: {$openEntry->id})\n";
    echo "   Acción: SALIDA\n\n";
} else {
    echo "✅ No tiene entrada abierta\n";
    echo "   Acción: ENTRADA\n\n";

    // Simular registro de entrada
    echo "5️⃣ Registrando entrada...\n";

    $poolAccessService = new \App\Services\PoolAccessService();

    try {
        $entry = $poolAccessService->registerEntry(
            $activeShift->pool,
            $unitUser->unit,
            $user,
            0, // sin invitados
            now()->toDateTimeString()
        );

        echo "✅ ENTRADA REGISTRADA EXITOSAMENTE\n";
        echo "   Entry ID: {$entry->id}\n";
        echo "   Pool: {$entry->pool->name}\n";
        echo "   User: {$entry->user->name}\n";
        echo "   Unit: {$entry->unit->full_identifier}\n";
        echo "   Entered at: {$entry->entered_at}\n\n";

        echo "═══════════════════════════════════════════════\n";
        echo "✅ ÉXITO: El sistema FUNCIONA CORRECTAMENTE\n";
        echo "El problema está en el frontend (JavaScript/Scanner)\n";
        echo "═══════════════════════════════════════════════\n";

    } catch (\Exception $e) {
        echo "❌ ERROR al registrar entrada:\n";
        echo "   {$e->getMessage()}\n\n";
        echo "   Stack trace:\n";
        echo $e->getTraceAsString();
    }
}
