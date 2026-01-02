<?php

namespace App\Livewire\Admin\Amenities;

use App\Models\Amenity;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function delete($id)
    {
        $amenity = Amenity::findOrFail($id);
        $amenity->delete();

        session()->flash('message', 'Amenidad eliminada correctamente.');
    }

    public function toggleActive($id)
    {
        $amenity = Amenity::findOrFail($id);
        $amenity->is_active = !$amenity->is_active;
        $amenity->save();

        session()->flash('message', 'Estado actualizado correctamente.');
    }

    public function render()
    {
        $amenities = Amenity::orderBy('display_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.amenities.index', [
            'amenities' => $amenities,
        ]);
    }
}
