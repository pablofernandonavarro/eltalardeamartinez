<?php

namespace App\Livewire\Admin\Amenities;

use App\Models\Amenity;
use Livewire\Component;

class Edit extends Component
{
    public Amenity $amenity;

    public $name;
    public $slug;
    public $description;
    public $icon_color;
    public $schedule_type;
    public $weekday_schedule;
    public $weekend_schedule;
    public $availability;
    public $additional_info;
    public $is_active;
    public $display_order;

    public function mount(Amenity $amenity)
    {
        $this->amenity = $amenity;
        $this->name = $amenity->name;
        $this->slug = $amenity->slug;
        $this->description = $amenity->description;
        $this->icon_color = $amenity->icon_color;
        $this->schedule_type = $amenity->schedule_type;
        $this->weekday_schedule = $amenity->weekday_schedule;
        $this->weekend_schedule = $amenity->weekend_schedule;
        $this->availability = $amenity->availability;
        $this->additional_info = $amenity->additional_info;
        $this->is_active = $amenity->is_active;
        $this->display_order = $amenity->display_order;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:amenities,slug,' . $this->amenity->id,
            'description' => 'nullable|string',
            'icon_color' => 'required|string',
            'schedule_type' => 'nullable|string',
            'weekday_schedule' => 'nullable|string',
            'weekend_schedule' => 'nullable|string',
            'availability' => 'nullable|string',
            'additional_info' => 'nullable|string',
            'is_active' => 'boolean',
            'display_order' => 'required|integer|min:0',
        ]);

        $this->amenity->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon_color' => $this->icon_color,
            'schedule_type' => $this->schedule_type,
            'weekday_schedule' => $this->weekday_schedule,
            'weekend_schedule' => $this->weekend_schedule,
            'availability' => $this->availability,
            'additional_info' => $this->additional_info,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
        ]);

        session()->flash('message', 'Amenidad actualizada correctamente.');

        return $this->redirect(route('admin.amenities.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.amenities.edit');
    }
}
