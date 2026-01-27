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

            // Colores con buen contraste en modo claro y oscuro
            $colors = match ($reservation->status) {
                'pending' => [
                    'bg' => '#f59e0b', // amber-500 - funciona en ambos modos
                    'border' => '#d97706', // amber-600 - borde más oscuro
                    'text' => '#ffffff', // texto blanco
                ],
                'approved' => [
                    'bg' => '#10b981', // emerald-500 - funciona en ambos modos
                    'border' => '#059669', // emerald-600 - borde más oscuro
                    'text' => '#ffffff', // texto blanco
                ],
                'rejected' => [
                    'bg' => '#ef4444', // red-500 - funciona en ambos modos
                    'border' => '#dc2626', // red-600 - borde más oscuro
                    'text' => '#ffffff', // texto blanco
                ],
                'cancelled' => [
                    'bg' => '#71717a', // zinc-500
                    'border' => '#52525b', // zinc-600 - borde más oscuro
                    'text' => '#ffffff', // texto blanco
                ],
                'completed' => [
                    'bg' => '#3b82f6', // blue-500 - funciona en ambos modos
                    'border' => '#2563eb', // blue-600 - borde más oscuro
                    'text' => '#ffffff', // texto blanco
                ],
                default => [
                    'bg' => '#71717a',
                    'border' => '#52525b',
                    'text' => '#ffffff',
                ],
            };

            return [
                'id' => $reservation->id,
                'title' => $reservation->user->name.' - '.$reservation->unit->full_identifier,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'backgroundColor' => $colors['bg'],
                'borderColor' => $colors['border'],
                'textColor' => $colors['text'],
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
