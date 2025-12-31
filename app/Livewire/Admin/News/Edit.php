<?php

namespace App\Livewire\Admin\News;

use App\Models\News;
use Livewire\Component;

class Edit extends Component
{
    public News $news;

    public $title;

    public $description;

    public $event_date;

    public $icon_type;

    public $color_scheme;

    public $is_featured;

    public $order;

    public $is_published;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'event_date' => 'required|date',
        'icon_type' => 'required|in:clock,document,check',
        'color_scheme' => 'required|in:orange,blue,green',
        'is_featured' => 'boolean',
        'order' => 'integer|min:0',
    ];

    public function mount(News $news)
    {
        $this->news = $news;
        $this->title = $news->title;
        $this->description = $news->description;
        $this->event_date = $news->event_date->format('Y-m-d');
        $this->icon_type = $news->icon_type;
        $this->color_scheme = $news->color_scheme;
        $this->is_featured = $news->is_featured;
        $this->order = $news->order;
        $this->is_published = $news->published_at !== null;
    }

    public function save()
    {
        $this->validate();

        $this->news->update([
            'title' => $this->title,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'icon_type' => $this->icon_type,
            'color_scheme' => $this->color_scheme,
            'is_featured' => $this->is_featured,
            'order' => $this->order,
            'published_at' => $this->is_published
                ? ($this->news->published_at ?? now())
                : null,
        ]);

        session()->flash('success', 'Novedad actualizada correctamente.');

        return redirect()->route('admin.news.index');
    }

    public function render()
    {
        return view('livewire.admin.news.edit');
    }
}
