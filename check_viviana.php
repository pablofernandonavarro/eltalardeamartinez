<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Buscando Viviana ===\n";
$viviana = App\Models\User::where('email', 'like', '%viviana%')
    ->orWhere('name', 'like', '%viviana%')
    ->first();

if (!$viviana) {
    echo "No se encontró usuario Viviana\n";
    exit;
}

echo "Usuario: {$viviana->name} (ID: {$viviana->id})\n";
echo "Email: {$viviana->email}\n";
echo "Rol: {$viviana->role->value}\n\n";

echo "=== Unidades asignadas ===\n";
$units = $viviana->currentUnitUsers()->with('unit')->get();
echo "Total: {$units->count()}\n";
foreach($units as $uu) {
    echo "  - Unit ID: {$uu->unit_id} ({$uu->unit->full_identifier})\n";
    echo "    Propietario: " . ($uu->is_owner ? 'Sí' : 'No') . "\n";
}

echo "\n=== Pass de hoy ===\n";
$today = now()->toDateString();
$pass = App\Models\PoolDayPass::where('user_id', $viviana->id)
    ->whereDate('date', $today)
    ->first();

if ($pass) {
    echo "Token: {$pass->token}\n";
    echo "Unit ID: {$pass->unit_id}\n";
    echo "Invitados permitidos: {$pass->guests_allowed}\n";
    echo "Usado: " . ($pass->used_at ? $pass->used_at : 'No') . "\n";
} else {
    echo "No tiene pass para hoy\n";
}

echo "\n=== Es residente? ===\n";
$resident = App\Models\Resident::where('auth_user_id', $viviana->id)
    ->active()
    ->first();

if ($resident) {
    echo "SÍ - Residente ID: {$resident->id}\n";
    echo "Nombre: {$resident->name}\n";
    echo "Unit ID: {$resident->unit_id}\n";
    echo "QR Token: {$resident->qr_token}\n";
} else {
    echo "NO - No es residente (es usuario/inquilino)\n";
}
