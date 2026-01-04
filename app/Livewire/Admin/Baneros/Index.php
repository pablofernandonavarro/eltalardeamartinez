<?php

namespace App\Livewire\Admin\Baneros;

use App\Models\PoolShift;
use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, active, history

    public function endShift($shiftId)
    {
        $shift = PoolShift::findOrFail($shiftId);
        $shift->end();

        session()->flash('message', 'Turno finalizado correctamente.');
    }

    public function render()
    {
        // Obtener bañeros
        $baneros = User::where('role', UserRole::Banero)
            ->orderBy('name')
            ->get();

        // Obtener turnos según el filtro
        $shiftsQuery = PoolShift::with(['user', 'pool'])
            ->latest('started_at');

        if ($this->filter === 'active') {
            $shiftsQuery->active();
        } elseif ($this->filter === 'history') {
            $shiftsQuery->whereNotNull('ended_at');
        }

        $shifts = $shiftsQuery->paginate(20);

        // Estadísticas
        $stats = [
            'total_baneros' => $baneros->count(),
            'active_shifts' => PoolShift::active()->count(),
            'shifts_today' => PoolShift::whereDate('started_at', today())->count(),
            'avg_shift_duration' => PoolShift::whereNotNull('ended_at')
                ->whereDate('started_at', '>=', now()->subDays(30))
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_minutes')
                ->value('avg_minutes'),
        ];

        return view('livewire.admin.baneros.index', [
            'baneros' => $baneros,
            'shifts' => $shifts,
            'stats' => $stats,
        ]);
    }
}
