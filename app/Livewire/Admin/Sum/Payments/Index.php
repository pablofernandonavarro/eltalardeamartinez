<?php

namespace App\Livewire\Admin\Sum\Payments;

use App\Enums\SumPaymentStatus;
use App\Enums\SumReservationStatus;
use App\Exports\SumPaymentsExport;
use App\Models\SumPayment;
use App\Services\MercadoPagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;

    // Filtros
    public string $paymentStatus = '';
    public string $month = '';
    public string $year = '';
    public string $paymentMethod = '';
    public string $search = '';

    // Modal de pago manual
    public bool $showPaymentModal = false;
    public ?int $selectedPaymentId = null;
    public string $modalPaymentMethod = '';
    public string $transactionReference = '';
    public string $notes = '';

    protected $queryString = [
        'paymentStatus' => ['except' => ''],
        'month' => ['except' => ''],
        'year' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->year = (string) now()->year;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updatingMonth(): void
    {
        $this->resetPage();
    }

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['paymentStatus', 'month', 'year', 'paymentMethod', 'search']);
        $this->year = (string) now()->year;
        $this->resetPage();
    }

    public function openPaymentModal(int $paymentId): void
    {
        $this->selectedPaymentId = $paymentId;
        $this->modalPaymentMethod = '';
        $this->transactionReference = '';
        $this->notes = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPaymentId = null;
        $this->resetValidation();
    }

    public function confirmPayment(): void
    {
        $this->validate([
            'modalPaymentMethod' => 'required|in:cash,transfer,card,online',
            'transactionReference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ], [
            'modalPaymentMethod.required' => 'Debe seleccionar un método de pago.',
        ]);

        $payment = SumPayment::findOrFail($this->selectedPaymentId);
        $payment->markAsPaid($this->modalPaymentMethod, $this->transactionReference, $this->notes);

        $this->closePaymentModal();
        session()->flash('message', 'Pago confirmado exitosamente.');
    }

    public function syncWithMercadoPago(int $paymentId): void
    {
        $payment = SumPayment::with('reservation')->find($paymentId);

        if (! $payment || $payment->status === SumPaymentStatus::Paid) {
            session()->flash('error', 'El pago ya está confirmado o no existe.');

            return;
        }

        try {
            $mpPayment = app(MercadoPagoService::class)->findApprovedPayment($payment->id);

            if (! $mpPayment) {
                session()->flash('error', 'No se encontró un pago aprobado en Mercado Pago para esta reserva.');

                return;
            }

            $payment->update([
                'status'                => SumPaymentStatus::Paid,
                'mp_payment_id'         => (string) $mpPayment->id,
                'mp_status'             => $mpPayment->status,
                'payment_method'        => 'online',
                'transaction_reference' => (string) $mpPayment->id,
                'paid_at'               => now(),
            ]);

            if ($payment->reservation && $payment->reservation->status === \App\Enums\SumReservationStatus::Pending) {
                $payment->reservation->update([
                    'status'      => SumReservationStatus::Approved,
                    'approved_at' => now(),
                ]);
            }

            session()->flash('message', 'Pago sincronizado con MP correctamente. ID: ' . $mpPayment->id);
        } catch (\Throwable $e) {
            \Log::error('Error al sincronizar pago con MP', ['payment_id' => $paymentId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Error al consultar Mercado Pago: ' . $e->getMessage());
        }
    }

    public function downloadInvoice(int $paymentId)
    {
        $payment = SumPayment::with(['reservation.unit.building', 'reservation.user', 'paidByUser'])
            ->findOrFail($paymentId);

        $pdf = Pdf::loadView('pdf.sum-invoice', ['payment' => $payment]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'factura-sum-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function exportToExcel()
    {
        $filters = [
            'paymentStatus' => $this->paymentStatus,
            'month' => $this->month,
            'year' => $this->year,
            'paymentMethod' => $this->paymentMethod,
            'search' => $this->search,
        ];

        $fileName = 'pagos-sum-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new SumPaymentsExport($filters), $fileName);
    }

    public function getPaymentsProperty()
    {
        return SumPayment::query()
            ->with(['reservation.unit.building', 'reservation.user', 'paidByUser'])
            ->when($this->paymentStatus, fn ($q) => $q->where('status', $this->paymentStatus))
            ->when($this->month, fn ($q) => $q->whereMonth('created_at', $this->month))
            ->when($this->year, fn ($q) => $q->whereYear('created_at', $this->year))
            ->when($this->paymentMethod, fn ($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->search, function ($q) {
                $q->whereHas('reservation', function ($query) {
                    $query->whereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    })
                        ->orWhereHas('unit', function ($unitQuery) {
                            $unitQuery->where('number', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $month = $this->month ?: now()->month;
        $year  = $this->year  ?: now()->year;

        $base = fn () => SumPayment::query()
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->when($year,  fn ($q) => $q->whereYear('created_at',  $year));

        return [
            'totalMonth'        => $base()->sum('amount'),
            'totalPaid'         => $base()->paid()->sum('amount'),
            'totalPending'      => $base()->pending()->sum('amount'),
            'totalReservations' => $base()->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.sum.payments.index', [
            'payments' => $this->payments,
            'stats' => $this->stats,
        ])->layout('components.layouts.app', ['title' => 'Pagos y Facturas del SUM']);
    }
}
