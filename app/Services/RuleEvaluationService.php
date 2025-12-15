<?php

namespace App\Services;

use App\Models\Pool;
use App\Models\SystemRule;
use App\Models\Unit;
use Illuminate\Support\Carbon;

class RuleEvaluationService
{
    /**
     * Evaluar reglas de ocupación de unidades basadas en habitantes.
     *
     * @return array{max_allowed: int|null, current_count: int, violations: array, is_valid: bool}
     */
    public function evaluateUnitOccupancyRules(Unit $unit, int $currentOccupantsCount): array
    {
        $rules = SystemRule::active()
            ->ofType('unit_occupancy')
            ->orderBy('priority', 'desc')
            ->get();

        $violations = [];
        $maxAllowed = null;

        foreach ($rules as $rule) {
            if ($this->matchesOccupancyConditions($rule, $unit, $currentOccupantsCount)) {
                $maxAllowed = $rule->limits['max_residents'] ?? null;

                if ($maxAllowed && $currentOccupantsCount > $maxAllowed) {
                    $violations[] = [
                        'rule' => $rule,
                        'message' => $rule->limits['message'] ??
                            "La unidad excede el máximo de {$maxAllowed} habitantes permitidos.",
                    ];
                }
                break; // Usar la primera regla que coincida (mayor prioridad)
            }
        }

        return [
            'max_allowed' => $maxAllowed,
            'current_count' => $currentOccupantsCount,
            'violations' => $violations,
            'is_valid' => empty($violations),
        ];
    }

    /**
     * Evaluar reglas de invitados por día de semana en piletas.
     *
     * @return array{max_allowed: int|null, current_count: int, violations: array, is_valid: bool}
     */
    public function evaluatePoolWeeklyGuestRules(
        Pool $pool,
        Unit $unit,
        Carbon $date,
        int $guestsCount
    ): array {
        $dayOfWeek = $date->dayOfWeek; // 0 = Domingo, 6 = Sábado

        $rules = SystemRule::active()
            ->ofType('pool_weekly_guests')
            ->orderBy('priority', 'desc')
            ->get();

        $violations = [];
        $maxAllowed = null;

        foreach ($rules as $rule) {
            $conditions = $rule->conditions ?? [];

            // Verificar si la regla aplica para este día de semana
            if (isset($conditions['days_of_week']) &&
                in_array($dayOfWeek, $conditions['days_of_week'], true)) {
                $maxAllowed = $rule->limits['max_guests'] ?? null;

                if ($maxAllowed !== null && $guestsCount > $maxAllowed) {
                    $dayName = $date->locale('es')->dayName;
                    $violations[] = [
                        'rule' => $rule,
                        'message' => $rule->limits['message'] ??
                            "Los {$dayName}s se permite un máximo de {$maxAllowed} invitados.",
                    ];
                }
                break;
            }
        }

        return [
            'max_allowed' => $maxAllowed,
            'current_count' => $guestsCount,
            'violations' => $violations,
            'is_valid' => empty($violations),
        ];
    }

    /**
     * Evaluar reglas de invitados mensuales en piletas.
     *
     * @return array{max_allowed: int|null, current_count: int, violations: array, is_valid: bool}
     */
    public function evaluatePoolMonthlyGuestRules(
        Pool $pool,
        Unit $unit,
        Carbon $month,
        int $currentMonthGuestsCount
    ): array {
        $rules = SystemRule::active()
            ->ofType('pool_monthly_guests')
            ->orderBy('priority', 'desc')
            ->get();

        $violations = [];
        $maxAllowed = null;

        foreach ($rules as $rule) {
            if ($this->matchesPoolConditions($rule, $unit)) {
                $maxAllowed = $rule->limits['max_guests_per_month'] ?? null;

                if ($maxAllowed !== null && $currentMonthGuestsCount >= $maxAllowed) {
                    $violations[] = [
                        'rule' => $rule,
                        'message' => $rule->limits['message'] ??
                            "Se ha alcanzado el límite mensual de {$maxAllowed} invitados.",
                    ];
                }
                break;
            }
        }

        return [
            'max_allowed' => $maxAllowed,
            'current_count' => $currentMonthGuestsCount,
            'violations' => $violations,
            'is_valid' => empty($violations),
        ];
    }

    /**
     * Verificar si una regla de ocupación coincide con las condiciones de la unidad.
     * Basado en la cantidad de habitantes actuales.
     */
    protected function matchesOccupancyConditions(
        SystemRule $rule,
        Unit $unit,
        int $currentOccupantsCount
    ): bool {
        $conditions = $rule->conditions ?? [];

        // Si no hay condiciones específicas, la regla aplica a todas las unidades
        if (empty($conditions)) {
            return true;
        }

        // Verificar rango de habitantes actuales
        if (isset($conditions['min_occupants']) &&
            $currentOccupantsCount < $conditions['min_occupants']) {
            return false;
        }
        if (isset($conditions['max_occupants']) &&
            $currentOccupantsCount > $conditions['max_occupants']) {
            return false;
        }

        // Verificar edificio específico
        if (isset($conditions['building_ids']) &&
            ! in_array($unit->building_id, $conditions['building_ids'], true)) {
            return false;
        }

        // Verificar complejo específico
        if (isset($conditions['complex_ids']) &&
            $unit->building &&
            ! in_array($unit->building->complex_id, $conditions['complex_ids'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si una regla de pileta coincide con las condiciones de la unidad.
     */
    protected function matchesPoolConditions(SystemRule $rule, Unit $unit): bool
    {
        $conditions = $rule->conditions ?? [];

        // Si no hay condiciones específicas, la regla aplica a todas las unidades
        if (empty($conditions)) {
            return true;
        }

        // Verificar edificio específico
        if (isset($conditions['building_ids']) &&
            ! in_array($unit->building_id, $conditions['building_ids'], true)) {
            return false;
        }

        // Verificar complejo específico
        if (isset($conditions['complex_ids']) &&
            $unit->building &&
            ! in_array($unit->building->complex_id, $conditions['complex_ids'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Contar habitantes actuales de una unidad (usuarios activos + residentes activos).
     */
    public function countCurrentOccupants(Unit $unit): int
    {
        // Contar usuarios activos asociados a la unidad
        $activeUsersCount = $unit->currentUsers()->count();

        // Contar residentes activos
        $activeResidentsCount = $unit->activeResidents()->count();

        return $activeUsersCount + $activeResidentsCount;
    }
}
