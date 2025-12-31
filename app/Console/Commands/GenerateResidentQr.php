<?php

namespace App\Console\Commands;

use App\Models\Resident;
use Illuminate\Console\Command;

class GenerateResidentQr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resident:generate-qr {resident_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate or regenerate QR token for a resident';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $residentId = $this->argument('resident_id');

        $resident = Resident::find($residentId);

        if (! $resident) {
            $this->error('Residente no encontrado.');

            return 1;
        }

        if ($resident->qr_token) {
            $this->info("Residente ya tiene QR: {$resident->qr_token}");
            $regenerate = $this->confirm('¿Regenerar QR?', false);

            if (! $regenerate) {
                return 0;
            }
        }

        if (! $resident->canHavePersonalQr()) {
            $this->error('Este residente no puede tener QR personal (debe ser mayor de 18 años y tener cuenta de usuario).');

            return 1;
        }

        $resident->generateQrToken();

        $this->info('✓ QR generado exitosamente');
        $this->info("Token: {$resident->qr_token}");

        return 0;
    }
}
