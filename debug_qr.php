<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$qrToken = '5e74e306-00f9-4fe6-95c8-6f8087f69532';

echo "Buscando QR: {$qrToken}\n\n";

// Buscar en residents (QR personal)
$resident = App\Models\Resident::where('qr_token', $qrToken)->first();
if ($resident) {
    echo "✓ Encontrado como QR PERSONAL de RESIDENTE\n";
    echo "  Nombre: {$resident->name}\n";
    echo "  ID: {$resident->id}\n";
    echo "  Unidad: {$resident->unit_id}\n";
    
    $unit = App\Models\Unit::find($resident->unit_id);
    if ($unit) {
        echo "  Unidad completa: {$unit->full_identifier}\n";
    }
    
    // Verificar si tiene entrada abierta
    $openEntry = App\Models\PoolEntry::where('resident_id', $resident->id)
        ->whereNull('exited_at')
        ->first();
    
    if ($openEntry) {
        echo "  ⚠️ TIENE ENTRADA ABIERTA (ya está adentro)\n";
        echo "     Entrada ID: {$openEntry->id}\n";
        echo "     Ingreso: {$openEntry->entered_at}\n";
        echo "     Pool ID: {$openEntry->pool_id}\n";
    } else {
        echo "  ✓ NO tiene entrada abierta (puede ingresar)\n";
    }
    
    exit(0);
}

// Buscar en users (QR personal)
$user = App\Models\User::where('qr_token', $qrToken)->first();
if ($user) {
    echo "✓ Encontrado como QR PERSONAL de USUARIO\n";
    echo "  Nombre: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  ID: {$user->id}\n";
    
    // Verificar si tiene entrada abierta
    $openEntry = App\Models\PoolEntry::where('user_id', $user->id)
        ->whereNull('resident_id')
        ->whereNull('exited_at')
        ->first();
    
    if ($openEntry) {
        echo "  ⚠️ TIENE ENTRADA ABIERTA (ya está adentro)\n";
        echo "     Entrada ID: {$openEntry->id}\n";
        echo "     Ingreso: {$openEntry->entered_at}\n";
        echo "     Pool ID: {$openEntry->pool_id}\n";
    } else {
        echo "  ✓ NO tiene entrada abierta (puede ingresar)\n";
    }
    
    exit(0);
}

// Buscar en day passes (QR diario con invitados)
$dayPass = App\Models\PoolDayPass::where('token', $qrToken)->first();
if ($dayPass) {
    echo "✓ Encontrado como DAY PASS (QR diario con invitados)\n";
    echo "  ID: {$dayPass->id}\n";
    echo "  Unidad: {$dayPass->unit_id}\n";
    echo "  Usuario ID: {$dayPass->user_id}\n";
    echo "  Invitados permitidos: {$dayPass->guests_allowed}\n";
    echo "  Fecha: {$dayPass->date}\n";
    echo "  Usado: " . ($dayPass->used_at ? "Sí ({$dayPass->used_at})" : "No") . "\n";
    
    $user = App\Models\User::find($dayPass->user_id);
    if ($user) {
        echo "  Usuario: {$user->name}\n";
    }
    
    $unit = App\Models\Unit::find($dayPass->unit_id);
    if ($unit) {
        echo "  Unidad completa: {$unit->full_identifier}\n";
    }
    
    // Verificar si el residente/usuario tiene entrada abierta
    $openEntry = App\Models\PoolEntry::where('user_id', $dayPass->user_id)
        ->whereNull('resident_id')
        ->whereNull('exited_at')
        ->first();
    
    if ($openEntry) {
        echo "  ⚠️ USUARIO TIENE ENTRADA ABIERTA (ya está adentro)\n";
        echo "     Entrada ID: {$openEntry->id}\n";
        echo "     Ingreso: {$openEntry->entered_at}\n";
        echo "     Pool ID: {$openEntry->pool_id}\n";
    } else {
        echo "  ✓ Usuario NO tiene entrada abierta (puede usar el day pass)\n";
    }
    
    exit(0);
}

echo "❌ QR NO ENCONTRADO en ninguna tabla\n";
