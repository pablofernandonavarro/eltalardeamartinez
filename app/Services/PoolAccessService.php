<?php

namespace App\Services;

use App\Events\PoolEntryRegistered;
use App\Models\Pool;
use App\Models\PoolEntry;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;

class PoolAccessService
{
    /**
     * Validate and register pool entry for a user.
     *
     * @throws \Exception
     */
    public function registerEntry(
        Pool $pool,
        Unit $unit,
        User $user,
        int $guestsCount = 0,
        ?string $enteredAt = null
    ): PoolEntry {
        $this->validateAccess($pool, $unit, $user, null, $guestsCount, $enteredAt);

        $entry = PoolEntry::create([
            'pool_id' => $pool->id,
            'unit_id' => $unit->id,
            'user_id' => $user->id,
            'resident_id' => null,
            'guests_count' => $guestsCount,
            'entered_at' => $enteredAt ?? now(),
            'notes' => null,
        ]);

        PoolEntryRegistered::dispatch($entry);

        return $entry;
    }

    /**
     * Validate and register pool entry for a resident.
     *
     * @throws \Exception
     */
    public function registerResidentEntry(
        Pool $pool,
        Unit $unit,
        Resident $resident,
        int $guestsCount = 0,
        ?string $enteredAt = null
    ): PoolEntry {
        $this->validateAccess($pool, $unit, null, $resident, $guestsCount, $enteredAt);

        $entry = PoolEntry::create([
            'pool_id' => $pool->id,
            'unit_id' => $unit->id,
            'user_id' => $resident->user_id, // Usuario responsable (padre/tutor)
            'resident_id' => $resident->id,
            'guests_count' => $guestsCount,
            'entered_at' => $enteredAt ?? now(),
            'notes' => null,
        ]);

        PoolEntryRegistered::dispatch($entry);

        return $entry;
    }

    /**
     * Validate pool access rules.
     *
     * @throws \Exception
     */
    protected function validateAccess(
        Pool $pool,
        Unit $unit,
        ?User $user = null,
        ?Resident $resident = null,
        int $guestsCount = 0,
        ?string $enteredAt = null
    ): void {
        if (! $pool->isEnabled()) {
            throw new \Exception('La pileta está inhabilitada.');
        }

        $rule = $pool->currentRule;
        if (! $rule) {
            throw new \Exception('No hay reglas activas para esta pileta.');
        }

        $enteredAtDate = $enteredAt ? Carbon::parse($enteredAt) : now();

        // Validar que se proporcione usuario o residente
        if (! $user && ! $resident) {
            throw new \Exception('Debe proporcionar un usuario o un residente.');
        }

        // Validar acceso según tipo
        if ($user) {
            // Validación para usuarios
            if ($rule->only_owners && ! $user->isPropietario()) {
                throw new \Exception('Solo los propietarios pueden ingresar a esta pileta.');
            }

            $unitUser = $unit->currentUsers()
                ->where('user_id', $user->id)
                ->first();

            if (! $unitUser) {
                throw new \Exception('El usuario no está asociado a esta unidad.');
            }
        } elseif ($resident) {
            // Validación para residentes
            if ($resident->unit_id !== $unit->id) {
                throw new \Exception('El residente no pertenece a esta unidad.');
            }

            if ($resident->ended_at && $resident->ended_at->isPast()) {
                throw new \Exception('El residente ya no está activo en esta unidad.');
            }

            // Si la regla solo permite propietarios, verificar que el residente pertenezca a un propietario
            if ($rule->only_owners) {
                $owner = $unit->currentOwner;
                if (! $owner || ($resident->user_id && $resident->user_id !== $owner->user_id)) {
                    throw new \Exception('Solo los residentes de propietarios pueden ingresar a esta pileta.');
                }
            }
        }

        if ($guestsCount > 0 && ! $rule->allow_guests) {
            throw new \Exception('No se permiten invitados en esta pileta.');
        }

        if ($rule->max_guests_per_unit > 0 && $guestsCount > $rule->max_guests_per_unit) {
            throw new \Exception("El máximo de invitados permitidos es {$rule->max_guests_per_unit}.");
        }

        // NOTA: No validamos max_entries_per_day porque los residentes pueden
        // salir y entrar múltiples veces con los mismos invitados.
        // Solo se validan invitados únicos (en validateAccess y en el Scanner).
        // Un invitado que ingresa múltiples veces el mismo día cuenta como 1 solo.

        // Validar reglas dinámicas del sistema SOLO si hay invitados
        // Los propietarios/inquilinos pueden ingresar solos sin límites
        if ($guestsCount > 0) {
            $ruleService = app(RuleEvaluationService::class);

            // Validar invitados por día de semana
            $weeklyCheck = $ruleService->evaluatePoolWeeklyGuestRules(
                $pool,
                $unit,
                $enteredAtDate,
                $guestsCount
            );

            if (! $weeklyCheck['is_valid']) {
                throw new \Exception($weeklyCheck['violations'][0]['message']);
            }

            // Validar invitados mensuales
            $monthStart = $enteredAtDate->copy()->startOfMonth();
            $monthEnd = $enteredAtDate->copy()->endOfMonth();
            $monthGuestsCount = PoolEntry::forUnit($unit->id)
                ->where('pool_id', $pool->id)
                ->whereBetween('entered_at', [$monthStart, $monthEnd])
                ->sum('guests_count');

            $monthlyCheck = $ruleService->evaluatePoolMonthlyGuestRules(
                $pool,
                $unit,
                $enteredAtDate,
                $monthGuestsCount + $guestsCount // Incluir los invitados actuales
            );

            if (! $monthlyCheck['is_valid']) {
                throw new \Exception($monthlyCheck['violations'][0]['message']);
            }
        }
    }

    /**
     * Get entries count for a unit on a specific date.
     */
    public function getEntriesCountForDate(Unit $unit, Pool $pool, string $date): int
    {
        return PoolEntry::forUnit($unit->id)
            ->where('pool_id', $pool->id)
            ->forDate($date)
            ->count();
    }

    /**
     * Get total guests count for a unit on a specific date.
     */
    public function getGuestsCountForDate(Unit $unit, Pool $pool, string $date): int
    {
        return PoolEntry::forUnit($unit->id)
            ->where('pool_id', $pool->id)
            ->forDate($date)
            ->sum('guests_count');
    }
}
