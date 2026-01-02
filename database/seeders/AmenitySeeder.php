<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            [
                'name' => 'Pileta',
                'slug' => 'pileta',
                'description' => 'Pileta climatizada para uso de residentes',
                'icon_color' => 'blue',
                'schedule_type' => 'weekdays_weekends',
                'weekday_schedule' => '9:00-13:00,15:00-22:00',
                'weekend_schedule' => '10:00-20:00',
                'availability' => 'Temporada de Verano',
                'additional_info' => '<strong>Importante:</strong> Menores de 12 años deben estar acompañados por un adulto',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Gimnasio',
                'slug' => 'gimnasio',
                'description' => 'Gimnasio equipado con máquinas modernas',
                'icon_color' => 'orange',
                'schedule_type' => 'all_days',
                'weekday_schedule' => '7:00-23:00',
                'weekend_schedule' => '7:00-23:00',
                'availability' => 'Todo el Año',
                'additional_info' => '<strong>Recordatorio:</strong> Uso exclusivo para mayores de 16 años. Traer toalla.',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'SUM',
                'slug' => 'sum',
                'description' => 'Salón de Usos Múltiples',
                'icon_color' => 'green',
                'schedule_type' => 'by_reservation',
                'weekday_schedule' => null,
                'weekend_schedule' => 'Horario flexible según reserva',
                'availability' => 'Con Reserva Previa',
                'additional_info' => '<strong>Reservas:</strong> Con 15 días de anticipación en administración',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Espacios Comunes',
                'slug' => 'espacios-comunes',
                'description' => 'Quincho, parrillas, área de juegos infantiles y senderos',
                'icon_color' => 'purple',
                'schedule_type' => 'open_access',
                'weekday_schedule' => null,
                'weekend_schedule' => null,
                'availability' => 'Acceso Libre',
                'additional_info' => null,
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Seguridad',
                'slug' => 'seguridad',
                'description' => 'Control de acceso y vigilancia permanente',
                'icon_color' => 'red',
                'schedule_type' => 'all_days',
                'weekday_schedule' => '24 horas',
                'weekend_schedule' => '24 horas',
                'availability' => '24 horas',
                'additional_info' => '<strong>Control de acceso:</strong> Solicitar autorización previa para visitas',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Administración',
                'slug' => 'administracion',
                'description' => 'Atención al propietario e inquilino',
                'icon_color' => 'amber',
                'schedule_type' => 'weekdays',
                'weekday_schedule' => '9:00-17:00',
                'weekend_schedule' => null,
                'availability' => 'Lunes a Viernes',
                'additional_info' => '<strong>Contacto:</strong> admin@eltalardemartinez.com',
                'is_active' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
