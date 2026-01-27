<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SumReservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SumReservationsController extends Controller
{
    /**
     * Get reservations as FullCalendar events.
     */
    public function events(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');

        // Si no se proporcionan fechas, usar un rango por defecto
        if (! $start) {
            $start = now()->subDays(7)->toDateString();
        }
        if (! $end) {
            $end = now()->addDays(60)->toDateString();
        }

        $reservations = SumReservation::query()
            ->with(['unit.building', 'user'])
            ->where('date', '>=', $start)
            ->where('date', '<=', $end)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->get();

        $events = $reservations->map(function ($reservation) {
            $startTime = is_string($reservation->start_time)
                ? $reservation->start_time
                : $reservation->start_time->format('H:i:s');
            $endTime = is_string($reservation->end_time)
                ? $reservation->end_time
                : $reservation->end_time->format('H:i:s');

            // Calcular fechas de inicio y fin
            $startDate = $reservation->date->format('Y-m-d');
            $endDate = $reservation->date->format('Y-m-d');

            // Si la hora de fin es menor que la de inicio, cruza medianoche
            $startHour = (int) substr($startTime, 0, 2);
            $endHour = (int) substr($endTime, 0, 2);

            if ($endHour < $startHour || ($endHour === $startHour && substr($endTime, 3, 2) < substr($startTime, 3, 2))) {
                // Agregar un día a la fecha de fin
                $endDate = $reservation->date->copy()->addDay()->format('Y-m-d');
            }

            // Combinar fecha con hora de inicio y fin
            $startDateTime = $startDate.'T'.$startTime;
            $endDateTime = $endDate.'T'.$endTime;

            // Color según estado
            $color = match ($reservation->status) {
                'pending' => '#f59e0b', // amber
                'approved' => '#10b981', // green
                'rejected' => '#ef4444', // red
                'cancelled' => '#71717a', // zinc
                'completed' => '#3b82f6', // blue
                default => '#71717a',
            };

            return [
                'id' => $reservation->id,
                'title' => $reservation->user->name.' - '.$reservation->unit->full_identifier,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'reservation_id' => $reservation->id,
                    'user_name' => $reservation->user->name,
                    'unit' => $reservation->unit->full_identifier,
                    'status' => $reservation->status,
                    'status_label' => $reservation->status_label,
                    'total_amount' => $reservation->total_amount,
                    'time_range' => $reservation->time_range,
                ],
            ];
        });

        return response()->json($events);
    }
}
