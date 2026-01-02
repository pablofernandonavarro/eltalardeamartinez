<?php

namespace App\Livewire\Admin;

use App\Models\Building;
use App\Models\News;
use App\Models\PoolEntry;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Estadísticas generales
        $stats = [
            'total_units' => Unit::count(),
            'occupied_units' => Unit::whereHas('residents', function ($q) {
                $q->where('is_active', true);
            })->count(),
            'total_residents' => Resident::where('is_active', true)->count(),
            'total_users' => User::count(),
            'total_buildings' => Building::count(),
        ];

        // Actividad de pileta hoy
        $poolActivity = [
            'entries_today' => PoolEntry::whereDate('entered_at', now())->count(),
            'currently_inside' => PoolEntry::whereDate('entered_at', now())
                ->whereNull('exited_at')
                ->count(),
            'total_people_today' => PoolEntry::whereDate('entered_at', now())
                ->sum('guests_count') + PoolEntry::whereDate('entered_at', now())->count(),
        ];

        // Actividad de pileta últimos 7 días
        $poolEntriesLastWeek = PoolEntry::where('entered_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(entered_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Noticias recientes
        $recentNews = News::latest('published_at')
            ->take(3)
            ->get();

        // Unidades con pagos pendientes (si existe el modelo Payment)
        $pendingPayments = \App\Models\Payment::where('status', 'pending')
            ->count();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'poolActivity' => $poolActivity,
            'poolEntriesLastWeek' => $poolEntriesLastWeek,
            'recentNews' => $recentNews,
            'pendingPayments' => $pendingPayments,
        ]);
    }
}
