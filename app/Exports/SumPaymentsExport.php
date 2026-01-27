<?php

namespace App\Exports;

use App\Models\SumPayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SumPaymentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = SumPayment::query()
            ->with(['reservation.unit.building', 'reservation.user', 'paidByUser']);

        // Apply filters
        if (!empty($this->filters['paymentStatus'])) {
            $query->where('status', $this->filters['paymentStatus']);
        }

        if (!empty($this->filters['month'])) {
            $query->whereMonth('created_at', $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->whereYear('created_at', $this->filters['year']);
        }

        if (!empty($this->filters['paymentMethod'])) {
            $query->where('payment_method', $this->filters['paymentMethod']);
        }

        if (!empty($this->filters['search'])) {
            $query->whereHas('reservation', function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', '%'.$this->filters['search'].'%')
                        ->orWhere('email', 'like', '%'.$this->filters['search'].'%');
                })
                    ->orWhereHas('unit', function ($unitQuery) {
                        $unitQuery->where('number', 'like', '%'.$this->filters['search'].'%');
                    });
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID Pago',
            'Fecha Pago',
            'Residente',
            'Email',
            'Unidad',
            'Fecha Reserva',
            'Horario',
            'Horas',
            'Precio/Hora',
            'Monto Total',
            'Estado',
            'Método Pago',
            'Referencia',
            'Fecha Confirmación',
            'Confirmado Por',
            'Notas',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->created_at->format('d/m/Y'),
            $payment->reservation->user->name,
            $payment->reservation->user->email,
            ($payment->reservation->unit->building->name ?? '').' - '.$payment->reservation->unit->number,
            $payment->reservation->date->format('d/m/Y'),
            $payment->reservation->start_time.' - '.$payment->reservation->end_time,
            number_format($payment->reservation->total_hours, 1),
            '$'.number_format($payment->reservation->price_per_hour, 2),
            '$'.number_format($payment->amount, 2),
            $payment->status_label,
            $payment->payment_method_label,
            $payment->transaction_reference ?? '-',
            $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-',
            $payment->paidByUser?->name ?? '-',
            $payment->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Pagos SUM';
    }
}
