<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'icon_type',
        'color_scheme',
        'is_featured',
        'order',
        'published_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope para obtener solo noticias publicadas
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope para obtener noticias destacadas
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Obtener el SVG del ícono según el tipo
     */
    public function getIconSvg(): string
    {
        return match ($this->icon_type) {
            'clock' => '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            'document' => '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            'check' => '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            default => '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        };
    }

    /**
     * Obtener las clases de color según el esquema
     */
    public function getColorClasses(): array
    {
        return match ($this->color_scheme) {
            'orange' => [
                'gradient' => 'from-amber-500 to-orange-500',
                'bg' => 'bg-gradient-to-br from-amber-500 to-orange-500',
            ],
            'blue' => [
                'gradient' => 'from-blue-500 to-indigo-600',
                'bg' => 'bg-gradient-to-br from-blue-500 to-indigo-600',
            ],
            'green' => [
                'gradient' => 'from-emerald-500 to-green-600',
                'bg' => 'bg-gradient-to-br from-emerald-500 to-green-600',
            ],
            default => [
                'gradient' => 'from-zinc-500 to-zinc-600',
                'bg' => 'bg-gradient-to-br from-zinc-500 to-zinc-600',
            ],
        };
    }

    /**
     * Verificar si la noticia está publicada
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }
}
