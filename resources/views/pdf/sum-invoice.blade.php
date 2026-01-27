<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $payment->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info-right {
            text-align: right;
        }
        .info-block {
            margin-bottom: 20px;
        }
        .info-block h3 {
            color: #2563eb;
            font-size: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .info-block p {
            margin: 3px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        thead {
            background-color: #f3f4f6;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .total-label {
            display: table-cell;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            width: 150px;
        }
        .grand-total {
            border-top: 2px solid #2563eb;
            padding-top: 10px;
            margin-top: 10px;
        }
        .grand-total .total-label,
        .grand-total .total-value {
            font-size: 18px;
            color: #2563eb;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #2563eb;
        }
        .notes h4 {
            color: #2563eb;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>FACTURA</h1>
            <p>Reserva de Salón de Usos Múltiples</p>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="invoice-info-left">
                <div class="info-block">
                    <h3>Factura Para:</h3>
                    <p><strong>{{ $payment->reservation->user->name }}</strong></p>
                    <p>{{ $payment->reservation->user->email }}</p>
                    <p>Unidad: {{ $payment->reservation->unit->building->name ?? '' }} - {{ $payment->reservation->unit->number }}</p>
                </div>
            </div>
            <div class="invoice-info-right">
                <div class="info-block">
                    <h3>Detalles de Factura:</h3>
                    <p><strong>Factura #:</strong> {{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p><strong>Fecha Emisión:</strong> {{ $payment->created_at->format('d/m/Y') }}</p>
                    @if($payment->paid_at)
                        <p><strong>Fecha Pago:</strong> {{ $payment->paid_at->format('d/m/Y') }}</p>
                    @endif
                    <p><strong>Estado:</strong> <span class="status-badge status-{{ $payment->status }}">{{ $payment->status_label }}</span></p>
                </div>
            </div>
        </div>

        <!-- Reservation Details Table -->
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th class="text-right">Horas</th>
                    <th class="text-right">Precio/Hora</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Reserva de SUM</td>
                    <td>{{ $payment->reservation->date->format('d/m/Y') }}</td>
                    <td>{{ $payment->reservation->start_time }} - {{ $payment->reservation->end_time }}</td>
                    <td class="text-right">{{ number_format($payment->reservation->total_hours, 1) }}</td>
                    <td class="text-right">${{ number_format($payment->reservation->price_per_hour, 2) }}</td>
                    <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">${{ number_format($payment->amount, 2) }}</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">TOTAL:</div>
                <div class="total-value">${{ number_format($payment->amount, 2) }}</div>
            </div>
        </div>

        <!-- Payment Info -->
        @if($payment->status === 'paid')
            <div class="notes">
                <h4>Información de Pago</h4>
                <p><strong>Método de Pago:</strong> {{ $payment->payment_method_label }}</p>
                @if($payment->transaction_reference)
                    <p><strong>Referencia:</strong> {{ $payment->transaction_reference }}</p>
                @endif
                @if($payment->paidByUser)
                    <p><strong>Registrado por:</strong> {{ $payment->paidByUser->name }}</p>
                @endif
                @if($payment->notes)
                    <p><strong>Notas:</strong> {{ $payment->notes }}</p>
                @endif
            </div>
        @endif

        @if($payment->reservation->notes)
            <div class="notes">
                <h4>Notas de la Reserva</h4>
                <p>{{ $payment->reservation->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Gracias por utilizar nuestras instalaciones</p>
            <p>Este documento fue generado electrónicamente el {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
