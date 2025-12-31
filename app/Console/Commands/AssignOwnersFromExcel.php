<?php

namespace App\Console\Commands;

use App\Models\Unit;
use App\Models\UnitUser;
use App\Models\User;
use App\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AssignOwnersFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'units:assign-owners {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna propietarios desde el campo owner del Excel a las unidades funcionales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Modo DRY RUN - No se realizarán cambios en la base de datos');
        }

        $this->info('Buscando unidades con propietarios del Excel...');

        // Obtener todas las unidades que tienen owner (del Excel) pero no tienen propietario asignado
        $units = Unit::whereNotNull('owner')
            ->where('owner', '!=', '')
            ->whereDoesntHave('currentUsers', function ($query) {
                $query->where('is_owner', true);
            })
            ->get();

        if ($units->isEmpty()) {
            $this->info('✅ No hay unidades pendientes de asignar propietarios.');

            return 0;
        }

        $this->info("📋 Se encontraron {$units->count()} unidades sin propietario asignado.");

        // Agrupar unidades por propietario
        $ownerGroups = $units->groupBy('owner');

        $this->info("👥 Total de propietarios únicos: {$ownerGroups->count()}");
        $this->newLine();

        $usersCreated = 0;
        $assignmentsCreated = 0;

        DB::beginTransaction();

        try {
            foreach ($ownerGroups as $ownerName => $ownerUnits) {
                $this->line("Procesando: {$ownerName} ({$ownerUnits->count()} unidad(es))");

                // Generar email basado en el nombre
                $email = $this->generateEmail($ownerName);

                // Buscar o crear el usuario
                $user = User::where('email', $email)->first();

                if (! $user) {
                    if ($dryRun) {
                        $this->warn("  [DRY RUN] Se crearía el usuario: {$email}");
                    } else {
                        $user = User::create([
                            'name' => $ownerName,
                            'email' => $email,
                            'password' => Hash::make('password123'), // Contraseña temporal
                            'role' => Role::Propietario,
                            'approved_at' => now(),
                        ]);
                        $this->info("  ✓ Usuario creado: {$email}");
                        $usersCreated++;
                    }
                } else {
                    $this->comment("  ℹ Usuario ya existe: {$email}");
                }

                // Asignar el usuario a cada unidad
                foreach ($ownerUnits as $unit) {
                    if ($dryRun) {
                        $this->warn("    [DRY RUN] Se asignaría a unidad: {$unit->full_identifier}");
                    } else {
                        // Verificar que no exista ya la asignación
                        $exists = UnitUser::where('unit_id', $unit->id)
                            ->where('user_id', $user->id)
                            ->where('is_owner', true)
                            ->whereNull('ended_at')
                            ->exists();

                        if (! $exists) {
                            UnitUser::create([
                                'unit_id' => $unit->id,
                                'user_id' => $user->id,
                                'is_owner' => true,
                                'is_responsible' => true, // El propietario es responsable del pago por defecto
                                'started_at' => now(),
                            ]);
                            $this->info("    ✓ Asignado a: {$unit->full_identifier}");
                            $assignmentsCreated++;
                        } else {
                            $this->comment("    ℹ Ya estaba asignado a: {$unit->full_identifier}");
                        }
                    }
                }

                $this->newLine();
            }

            if (! $dryRun) {
                DB::commit();
                $this->newLine();
                $this->info('✅ Proceso completado exitosamente!');
                $this->info("   • Usuarios creados: {$usersCreated}");
                $this->info("   • Asignaciones creadas: {$assignmentsCreated}");
                $this->newLine();
                $this->warn('⚠️  Nota: Todos los usuarios creados tienen la contraseña temporal: password123');
                $this->warn('   Los propietarios deben cambiar su contraseña al iniciar sesión.');
            } else {
                DB::rollBack();
                $this->newLine();
                $this->info('✅ DRY RUN completado. No se realizaron cambios.');
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Genera un email basado en el nombre del propietario
     */
    private function generateEmail(string $name): string
    {
        // Normalizar el nombre: remover acentos, espacios, etc.
        $slug = Str::slug($name, '.');

        // Si el slug está vacío, usar un identificador genérico
        if (empty($slug)) {
            $slug = 'propietario.'.uniqid();
        }

        $baseEmail = strtolower($slug).'@eltalardemartinez.com';

        // Si el email ya existe, agregar un número
        $email = $baseEmail;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = strtolower($slug).$counter.'@eltalardemartinez.com';
            $counter++;
        }

        return $email;
    }
}
