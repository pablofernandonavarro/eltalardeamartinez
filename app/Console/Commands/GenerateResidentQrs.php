<?php

namespace App\Console\Commands;

use App\Models\Resident;
use Illuminate\Console\Command;

class GenerateResidentQrs extends Command
{
    protected $signature = 'residents:generate-qrs';
    protected $description = 'Generar QR tokens para residentes mayores de 15 años con cuenta de usuario';

    public function handle()
    {
        $residents = Resident::query()
            ->whereNotNull('auth_user_id')
            ->whereNull('qr_token')
            ->active()
            ->get();

        $count = 0;
        foreach ($residents as $resident) {
            if ($resident->canHavePersonalQr()) {
                $resident->generateQrToken();
                $this->info("✅ QR generado para: {$resident->name} (ID: {$resident->id})");
                $count++;
            }
        }

        $this->info("Total QRs generados: {$count}");
    }
}
