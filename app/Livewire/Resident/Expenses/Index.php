<?php

namespace App\Livewire\Resident\Expenses;

use App\ExpenseStatus;
use App\Models\ExpenseDetail;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $selectedUnitId = null;

    public ?string $filterStatus = null;

    public ?string $filterPeriod = null;

    // ID del detalle cuyo desglose de rubros se muestra en el modal
    public ?int $expandedDetailId = null;

    public function mount(): void
    {
        $user = auth()->user();
        $unitUser = $user->currentUnitUsers()->first();

        if ($unitUser) {
            $this->selectedUnitId = $unitUser->unit_id;
        }
    }

    public function updatedSelectedUnitId(): void
    {
        $this->resetPage();
        $this->expandedDetailId = null;
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPeriod(): void
    {
        $this->resetPage();
    }

    public function toggleRubros(int $detailId): void
    {
        $this->expandedDetailId = ($this->expandedDetailId === $detailId) ? null : $detailId;
    }

    public function render()
    {
        $user = auth()->user();

        // Todas las unidades del usuario para el selector
        $unitUsers = $user->currentUnitUsers()->with(['unit.building'])->get();

        // IDs de unidades disponibles para el usuario
        $availableUnitIds = $unitUsers->pluck('unit_id')->toArray();

        // Si no hay unidad seleccionada o no le pertenece, usar la primera
        if (! $this->selectedUnitId || ! in_array($this->selectedUnitId, $availableUnitIds)) {
            $this->selectedUnitId = $availableUnitIds[0] ?? null;
        }

        // Períodos disponibles para el filtro (sin paginar)
        $periods = [];
        if ($this->selectedUnitId) {
            $periods = ExpenseDetail::query()
                ->where('unit_id', $this->selectedUnitId)
                ->join('expenses', 'expense_details.expense_id', '=', 'expenses.id')
                ->whereNull('expense_details.deleted_at')
                ->whereNull('expenses.deleted_at')
                ->whereNotNull('expenses.period')
                ->orderBy('expenses.period', 'desc')
                ->distinct()
                ->pluck('expenses.period')
                ->toArray();
        }

        // Query principal de detalles de expensas
        $expenseDetails = $this->selectedUnitId
            ? ExpenseDetail::query()
                ->where('unit_id', $this->selectedUnitId)
                ->with(['expense.concept', 'expense.building', 'payments'])
                ->whereHas('expense', function ($q) {
                    $q->whereNull('deleted_at')
                        ->when($this->filterPeriod, fn ($q) => $q->where('period', $this->filterPeriod));
                })
                ->when(
                    $this->filterStatus,
                    fn ($q) => $q->where('status', $this->filterStatus)
                )
                ->whereNull('expense_details.deleted_at')
                ->latest('expense_details.created_at')
                ->paginate(10)
            : collect()->paginate(10);

        // Totales de resumen para la unidad seleccionada
        $summary = $this->buildSummary($this->selectedUnitId);

        // Detalle expandido para el modal de rubros
        $expandedDetail = $this->expandedDetailId
            ? ExpenseDetail::with('expense')->find($this->expandedDetailId)
            : null;

        return view('livewire.resident.expenses.index', [
            'unitUsers' => $unitUsers,
            'periods' => $periods,
            'expenseDetails' => $expenseDetails,
            'summary' => $summary,
            'expandedDetail' => $expandedDetail,
        ])->layout('components.layouts.resident', ['title' => 'Mis Expensas']);
    }

    /**
     * Calcula el resumen de cuenta para una unidad.
     * Devuelve un array con los totales del estado de cuenta.
     */
    private function buildSummary(?int $unitId): array
    {
        if (! $unitId) {
            return $this->emptySummary();
        }

        $details = ExpenseDetail::query()
            ->where('unit_id', $unitId)
            ->whereNull('deleted_at')
            ->whereHas('expense', fn ($q) => $q->whereNull('deleted_at'))
            ->get(['amount', 'paid_amount', 'status', 'metadata']);

        if ($details->isEmpty()) {
            return $this->emptySummary();
        }

        $totalCuotas = $details->sum('amount');
        $totalPagado = $details->sum('paid_amount');
        $totalPendiente = $details->where('status', '!=', ExpenseStatus::Pagada)->sum(fn ($d) => $d->amount - $d->paid_amount);

        // Acumular intereses y deudas históricas desde metadata
        $totalIntereses = 0;
        $totalDeuda = 0;
        foreach ($details as $d) {
            $meta = $d->metadata ?? [];
            $totalIntereses += $meta['interests'] ?? 0;
            $totalDeuda += $meta['accumulated_debt'] ?? 0;
        }

        // Última cuota (período más reciente)
        $ultimaExpensa = ExpenseDetail::query()
            ->where('unit_id', $unitId)
            ->whereNull('deleted_at')
            ->whereHas('expense', fn ($q) => $q->whereNull('deleted_at'))
            ->with('expense')
            ->latest('created_at')
            ->first();

        $ultimaCuota = $ultimaExpensa?->amount ?? 0;
        $ultimoPeriodo = $ultimaExpensa?->expense?->period;
        $ultimoTotal = $ultimaExpensa?->metadata['total_to_pay'] ?? $ultimaCuota;

        // Deuda vigente: el TOTAL del último período según PDF
        $deudaVigente = $ultimaExpensa
            ? ($ultimaExpensa->metadata['previous_balance'] ?? 0)
                + ($ultimaExpensa->metadata['accumulated_debt'] ?? 0)
                + ($ultimaExpensa->metadata['interests'] ?? 0)
            : 0;

        $estaAlDia = $totalPendiente <= 0;

        return [
            'total_cuotas' => $totalCuotas,
            'total_pagado' => $totalPagado,
            'total_pendiente' => $totalPendiente,
            'total_intereses' => $totalIntereses,
            'total_deuda' => $totalDeuda,
            'deuda_vigente' => $deudaVigente,
            'ultima_cuota' => $ultimaCuota,
            'ultimo_total' => $ultimoTotal,
            'ultimo_periodo' => $ultimoPeriodo,
            'esta_al_dia' => $estaAlDia,
            'cantidad_pendientes' => $details->where('status', ExpenseStatus::Pendiente->value)->count()
                                   + $details->where('status', ExpenseStatus::Vencida->value)->count(),
        ];
    }

    private function emptySummary(): array
    {
        return [
            'total_cuotas' => 0,
            'total_pagado' => 0,
            'total_pendiente' => 0,
            'total_intereses' => 0,
            'total_deuda' => 0,
            'deuda_vigente' => 0,
            'ultima_cuota' => 0,
            'ultimo_total' => 0,
            'ultimo_periodo' => null,
            'esta_al_dia' => true,
            'cantidad_pendientes' => 0,
        ];
    }
}
