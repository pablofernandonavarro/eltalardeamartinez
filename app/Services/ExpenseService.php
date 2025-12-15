<?php

namespace App\Services;

use App\Events\ExpenseDetailsGenerated;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Unit;

class ExpenseService
{
    /**
     * Generate expense details for all units in the building based on coefficients.
     */
    public function generateExpenseDetails(Expense $expense): int
    {
        $building = $expense->building;
        $units = $building->units()->withTrashed()->get();
        $totalCoefficient = $units->sum('coefficient');

        if ($totalCoefficient == 0) {
            return 0;
        }

        $details = [];

        foreach ($units as $unit) {
            $amount = ($expense->total_amount * $unit->coefficient) / $totalCoefficient;

            $details[] = [
                'expense_id' => $expense->id,
                'unit_id' => $unit->id,
                'amount' => round($amount, 2),
                'paid_amount' => 0,
                'status' => \App\ExpenseStatus::Pendiente->value,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ExpenseDetail::insert($details);

        $count = count($details);
        ExpenseDetailsGenerated::dispatch($expense, $count);

        return $count;
    }

    /**
     * Calculate the amount for a unit based on its coefficient.
     */
    public function calculateUnitAmount(Expense $expense, Unit $unit): float
    {
        $building = $expense->building;
        $totalCoefficient = $building->units()->withTrashed()->sum('coefficient');

        if ($totalCoefficient == 0) {
            return 0;
        }

        return round(($expense->total_amount * $unit->coefficient) / $totalCoefficient, 2);
    }
}
