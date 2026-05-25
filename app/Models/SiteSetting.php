<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    // Secciones del portal residente con sus labels e íconos
    public const SECTIONS = [
        'section_household'    => ['label' => 'Mi hogar',        'icon' => 'home-modern'],
        'section_pool_qr'      => ['label' => 'Mi QR de Pileta', 'icon' => 'qr-code'],
        'section_pool_guests'  => ['label' => 'Mis Invitados',   'icon' => 'users'],
        'section_sum'          => ['label' => 'Reservar SUM',    'icon' => 'calendar-days'],
        'section_expenses'     => ['label' => 'Mis Expensas',    'icon' => 'currency-dollar'],
    ];

    private const CACHE_KEY = 'site_settings_all';

    public static function get(string $key, mixed $default = true): mixed
    {
        $all = Cache::rememberForever(self::CACHE_KEY, function () {
            return self::pluck('value', 'key')->toArray();
        });

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $val = $all[$key];

        // Cast booleans stored as '1'/'0'
        if ($val === '1') return true;
        if ($val === '0') return false;

        return $val;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
        );

        Cache::forget(self::CACHE_KEY);
    }

    public static function allSections(): array
    {
        $result = [];
        foreach (self::SECTIONS as $key => $meta) {
            $result[$key] = array_merge($meta, ['enabled' => (bool) self::get($key, true)]);
        }

        return $result;
    }
}
