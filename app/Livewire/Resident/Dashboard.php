<?php

namespace App\Livewire\Resident;

use App\Models\ExpenseDetail;
use App\Models\PoolEntry;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $units = $user->currentUnitUsers()->with(['unit.building.complex'])->get();

        // Get expense details for user's units
        $expenseDetails = ExpenseDetail::query()
            ->whereIn('unit_id', $units->pluck('unit_id'))
            ->with(['expense.concept', 'unit.building'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        // Get recent pool entries
        $poolEntries = PoolEntry::query()
            ->whereIn('unit_id', $units->pluck('unit_id'))
            ->where('user_id', $user->id)
            ->with(['pool', 'unit.building'])
            ->latest('entered_at')
            ->limit(10)
            ->get();

        // Calculate pending expenses
        $pendingExpenses = ExpenseDetail::query()
            ->whereIn('unit_id', $units->pluck('unit_id'))
            ->where('status', \App\ExpenseStatus::Pendiente)
            ->sum('amount');

        return view('livewire.resident.dashboard', [
            'units' => $units,
            'expenseDetails' => $expenseDetails,
            'poolEntries' => $poolEntries,
            'pendingExpenses' => $pendingExpenses,
        ])->layout('components.layouts.resident', ['title' => 'Mi Portal']);
    }
}
