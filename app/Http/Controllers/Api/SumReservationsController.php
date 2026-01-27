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

        $reservations = SumReservation::query()
            ->with(['unit.building', 'user'])
            ->when($start, fn ($q) => $q->where('date', '>=', $start))
            ->when($end, fn ($q) => $q->where('date', '<=', $end))
            ->get();

        $events = $reservations->map(function ($reservation) {
            // Combinar fecha con hora de inicio y fin
            $startDateTime = $reservation->date->format('Y-m-d').' '.
                (is_string($reservation->start_time) ? $reservation->start_time : $reservation->start_time->format('H:i:s'));
            $endDateTime = $reservation->date->format('Y-m-d').' '.
                (is_string($reservation->end_time) ? $reservation->end_time : $reservation->end_time->format('H:i:s'));

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
