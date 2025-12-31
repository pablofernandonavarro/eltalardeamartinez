<?php

namespace App\Livewire\Admin\News;

use App\Models\News;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showDeleted = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $news = News::find($id);

        if ($news) {
            $news->delete();
            session()->flash('success', 'Novedad eliminada correctamente.');
        }
    }

    public function restore($id)
    {
        $news = News::withTrashed()->find($id);

        if ($news) {
            $news->restore();
            session()->flash('success', 'Novedad restaurada correctamente.');
        }
    }

    public function forceDelete($id)
    {
        $news = News::withTrashed()->find($id);

        if ($news) {
            $news->forceDelete();
            session()->flash('success', 'Novedad eliminada permanentemente.');
        }
    }

    public function togglePublish($id)
    {
        $news = News::find($id);

        if ($news) {
            if ($news->published_at) {
                $news->published_at = null;
                $message = 'Novedad despublicada.';
            } else {
                $news->published_at = now();
                $message = 'Novedad publicada.';
            }
            $news->save();
            session()->flash('success', $message);
        }
    }

    public function render()
    {
        $query = News::query();

        if ($this->showDeleted) {
            $query->onlyTrashed();
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        $news = $query->orderBy('order')
            ->orderBy('event_date', 'desc')
            ->paginate(10);

        return view('livewire.admin.news.index', [
            'news' => $news,
        ]);
    }
}
