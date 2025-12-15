<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ApproveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:approve {email : The email of the user to approve}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve a user account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Usuario con email '{$email}' no encontrado.");

            return Command::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info("El usuario '{$email}' es un administrador y siempre está aprobado.");

            return Command::SUCCESS;
        }

        if ($user->isApproved()) {
            $this->info("El usuario '{$email}' ya está aprobado.");

            return Command::SUCCESS;
        }

        $user->approve();

        $this->info("Usuario '{$email}' aprobado correctamente.");

        return Command::SUCCESS;
    }
}
