<?php

namespace App\Livewire\Admin\Expenses;

use App\ExpenseStatus;
use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Expense $expense;

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'unit_number';

    public string $sortDir = 'asc';

    public function mount(Expense $expense): void
    {
        $this->expense = $expense->load(['building.complex', 'concept']);
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $details = $this->expense->details()
            ->with(['unit'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, function ($q) {
                $q->whereHas('unit', function ($uq) {
                    $uq->where('number', 'like', "%{$this->search}%")
                        ->orWhere('uf_code', 'like', "%{$this->search}%")
                        ->orWhere('owner', 'like', "%{$this->search}%");
                });
            })
            ->join('units', 'units.id', '=', 'expense_details.unit_id')
            ->select('expense_details.*')
            ->orderBy(match ($this->sortBy) {
                'unit_number' => 'units.number',
                'uf_code'     => 'units.uf_code',
                'owner'       => 'units.owner',
                'amount'      => 'expense_details.amount',
                'paid_amount' => 'expense_details.paid_amount',
                'status'      => 'expense_details.status',
                default       => 'units.number',
            }, $this->sortDir)
            ->paginate(30);

        $stats = [
            'total'    => $this->expense->details()->count(),
            'pendiente' => $this->expense->details()->where('status', ExpenseStatus::Pendiente)->count(),
            'parcial'   => $this->expense->details()->where('status', ExpenseStatus::Parcial)->count(),
            'pagada'    => $this->expense->details()->where('status', ExpenseStatus::Pagada)->count(),
            'vencida'   => $this->expense->details()->where('status', ExpenseStatus::Vencida)->count(),
            'total_amount'  => (float) $this->expense->details()->sum('amount'),
            'total_paid'    => (float) $this->expense->details()->sum('paid_amount'),
        ];

        $stats['total_pending'] = $stats['total_amount'] - $stats['total_paid'];

        return view('livewire.admin.expenses.show', [
            'details' => $details,
            'stats'   => $stats,
        ])->layout('components.layouts.app', ['title' => 'Detalle de Expensa']);
    }
}
