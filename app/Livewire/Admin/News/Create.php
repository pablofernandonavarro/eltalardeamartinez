<?php

namespace App\Livewire\Admin\News;

use App\Models\News;
use Livewire\Component;

class Create extends Component
{
    public $title = '';

    public $description = '';

    public $event_date;

    public $icon_type = 'clock';

    public $color_scheme = 'orange';

    public $is_featured = false;

    public $order = 0;

    public $publish_now = true;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'event_date' => 'required|date',
        'icon_type' => 'required|in:clock,document,check',
        'color_scheme' => 'required|in:orange,blue,green',
        'is_featured' => 'boolean',
        'order' => 'integer|min:0',
    ];

    public function save()
    {
        $this->validate();

        $news = News::create([
            'title' => $this->title,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'icon_type' => $this->icon_type,
            'color_scheme' => $this->color_scheme,
            'is_featured' => $this->is_featured,
            'order' => $this->order,
            'published_at' => $this->publish_now ? now() : null,
        ]);

        session()->flash('success', 'Novedad creada correctamente.');

        return redirect()->route('admin.news.index');
    }

    public function render()
    {
        return view('livewire.admin.news.create');
    }
}
