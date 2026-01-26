<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SumSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
    ];

    /**
     * Obtener el valor de una configuracion (con cache).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("sum_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Establecer el valor de una configuracion.
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => (string) $value]);
        } else {
            self::create([
                'key' => $key,
                'value' => (string) $value,
                'type' => self::detectType($value),
            ]);
        }

        Cache::forget("sum_setting_{$key}");
    }

    /**
     * Castear el valor segun el tipo.
     */
    protected static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => $value === 'true' || $value === '1',
            'float' => (float) $value,
            'time' => $value,
            default => $value,
        };
    }

    /**
     * Detectar el tipo de un valor.
     */
    protected static function detectType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'integer',
            is_bool($value) => 'boolean',
            is_float($value) => 'float',
            default => 'string',
        };
    }

    /**
     * Limpiar toda la cache de configuraciones.
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("sum_setting_{$key}");
        }
    }
}
