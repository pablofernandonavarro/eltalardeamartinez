<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateExitQr extends Command
{
    protected $signature = 'pool:generate-exit-qr';

    protected $description = 'Muestra el token del QR único de salida para las piletas';

    public function handle(): int
    {
        $exitToken = \App\Livewire\Banero\Pools\Scanner::EXIT_QR_TOKEN;

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  QR ÚNICO DE SALIDA PARA PILETAS');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');
        $this->info('Token: '.$exitToken);
        $this->info('');
        $this->info('Este QR debe ser escaneado por todos los usuarios para');
        $this->info('registrar su salida de la pileta.');
        $this->info('');
        $this->info('Puedes generar el QR usando cualquier generador online:');
        $this->info('https://www.qr-code-generator.com/');
        $this->info('https://qr-code-generator.com/');
        $this->info('');
        $this->info('O acceder a la página de administración para ver el QR:');
        $this->info(route('banero.pools.scanner').'?show_exit_qr=1');
        $this->info('');

        return Command::SUCCESS;
    }
}
