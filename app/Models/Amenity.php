<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amenity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_color',
        'schedule_type',
        'weekday_schedule',
        'weekend_schedule',
        'availability',
        'additional_info',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope para amenidades activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Obtener el SVG del icono según el slug
     */
    public function getIconSvg(): string
    {
        $icons = [
            'pileta' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
            'gimnasio' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>',
            'sum' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            'espacios-comunes' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
            'seguridad' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            'administracion' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        ];

        $path = $icons[$this->slug] ?? $icons['pileta'];
        
        return '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $path . '</svg>';
    }

    /**
     * Obtener clases CSS de color
     */
    public function getColorClasses(): array
    {
        $colors = [
            'blue' => ['bg' => 'bg-blue-500', 'border' => 'border-blue-200 dark:border-blue-800', 'gradient' => 'from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20', 'text' => 'text-blue-600 dark:text-blue-400'],
            'orange' => ['bg' => 'bg-orange-500', 'border' => 'border-orange-200 dark:border-orange-800', 'gradient' => 'from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20', 'text' => 'text-orange-600 dark:text-orange-400'],
            'green' => ['bg' => 'bg-green-500', 'border' => 'border-green-200 dark:border-green-800', 'gradient' => 'from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20', 'text' => 'text-green-600 dark:text-green-400'],
            'purple' => ['bg' => 'bg-purple-500', 'border' => 'border-purple-200 dark:border-purple-800', 'gradient' => 'from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20', 'text' => 'text-purple-600 dark:text-purple-400'],
            'red' => ['bg' => 'bg-red-500', 'border' => 'border-red-200 dark:border-red-800', 'gradient' => 'from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20', 'text' => 'text-red-600 dark:text-red-400'],
            'amber' => ['bg' => 'bg-amber-500', 'border' => 'border-amber-200 dark:border-amber-800', 'gradient' => 'from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20', 'text' => 'text-amber-600 dark:text-amber-400'],
        ];

        return $colors[$this->icon_color] ?? $colors['blue'];
    }
}
