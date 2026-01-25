<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Pool;
use App\Models\PoolEntry;

echo "═══════════════════════════════════════════════\n";
echo "   DIAGNÓSTICO: Fernando no puede ingresar\n";
echo "═══════════════════════════════════════════════\n\n";

// 1. Verificar usuario Fernando
echo "1️⃣ USUARIO FERNANDO:\n";
$fernando = User::where('email', 'LIKE', '%fernando%')->first();
if ($fernando) {
    echo "✅ Encontrado: {$fernando->name} ({$fernando->email})\n";
    echo "   ID: {$fernando->id}\n";
    echo "   QR Token: {$fernando->qr_token}\n";
    echo "   Approved: " . ($fernando->approved_at ? 'SI' : 'NO') . "\n";

    $unitUser = $fernando->currentUnitUsers()->first();
    if ($unitUser) {
        echo "   Unit: {$unitUser->unit->full_identifier} (ID: {$unitUser->unit_id})\n";
    } else {
        echo "   ❌ NO tiene unidad activa\n";
    }
} else {
    echo "❌ No encontrado\n";
    exit(1);
}

echo "\n";

// 2. Verificar si tiene entrada abierta HOY
echo "2️⃣ ENTRADAS ABIERTAS HOY:\n";
$openEntry = PoolEntry::query()
    ->where('user_id', $fernando->id)
    ->whereNull('resident_id')
    ->whereDate('entered_at', now()->toDateString())
    ->whereNull('exited_at')
    ->first();

if ($openEntry) {
    echo "⚠️ YA TIENE ENTRADA ABIERTA:\n";
    echo "   Entry ID: {$openEntry->id}\n";
    echo "   Pool: {$openEntry->pool->name}\n";
    echo "   Entered at: {$openEntry->entered_at}\n";
    echo "   ❌ Debe salir primero antes de volver a ingresar\n";
} else {
    echo "✅ No tiene entradas abiertas\n";
}

echo "\n";

// 3. Verificar piletas disponibles
echo "3️⃣ PILETAS DISPONIBLES:\n";
$pools = Pool::all();
foreach ($pools as $pool) {
    echo "   - {$pool->name} (ID: {$pool->id})\n";
}

echo "\n";

// 4. Verificar bañeros con turno activo
echo "4️⃣ TURNOS ACTIVOS DE BAÑEROS HOY:\n";
$activeShifts = \App\Models\PoolShift::query()
    ->whereDate('started_at', now()->toDateString())
    ->whereNull('ended_at')
    ->with(['user', 'pool'])
    ->get();

if ($activeShifts->count() > 0) {
    foreach ($activeShifts as $shift) {
        echo "   ✅ {$shift->user->name} - {$shift->pool->name}\n";
        echo "      Started: {$shift->started_at}\n";
    }
} else {
    echo "   ⚠️ NO HAY TURNOS ACTIVOS\n";
    echo "   ❌ Si estás usando el scanner como bañero, debes iniciar tu turno\n";
}

echo "\n";

// 5. Últimas entradas de Fernando
echo "5️⃣ ÚLTIMAS 5 ENTRADAS DE FERNANDO:\n";
$lastEntries = PoolEntry::query()
    ->where('user_id', $fernando->id)
    ->whereNull('resident_id')
    ->orderBy('entered_at', 'desc')
    ->limit(5)
    ->with('pool')
    ->get();

if ($lastEntries->count() > 0) {
    foreach ($lastEntries as $entry) {
        $status = $entry->exited_at ? '✅ Cerrada' : '⚠️ ABIERTA';
        echo "   {$status} - {$entry->pool->name}\n";
        echo "      Entrada: {$entry->entered_at}\n";
        if ($entry->exited_at) {
            echo "      Salida: {$entry->exited_at}\n";
        }
        echo "\n";
    }
} else {
    echo "   📭 No hay entradas previas\n";
}

echo "\n═══════════════════════════════════════════════\n";
echo "RESUMEN:\n";

if (!$fernando->approved_at) {
    echo "❌ PROBLEMA: Usuario no está aprobado\n";
} elseif (!$unitUser) {
    echo "❌ PROBLEMA: Usuario no tiene unidad activa\n";
} elseif ($openEntry) {
    echo "❌ PROBLEMA: Usuario ya tiene entrada abierta - debe salir primero\n";
} elseif ($activeShifts->count() === 0) {
    echo "⚠️ POSIBLE PROBLEMA: No hay turnos activos (si estás como bañero)\n";
    echo "   SOLUCIÓN: Inicia tu turno desde 'Mi Turno'\n";
} else {
    echo "✅ TODO OK - Debería poder ingresar\n";
    echo "   Revisar logs de Laravel para ver qué error específico está ocurriendo\n";
}

echo "═══════════════════════════════════════════════\n";
