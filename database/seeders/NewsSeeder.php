<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        News::create([
            'title' => 'Horario de Verano - Pileta',
            'description' => 'Desde diciembre hasta marzo, la pileta está disponible de lunes a viernes de 9:00 a 22:00 hs y fines de semana de 10:00 a 20:00 hs.',
            'event_date' => '2025-12-10',
            'icon_type' => 'clock',
            'color_scheme' => 'orange',
            'is_featured' => true,
            'order' => 1,
            'published_at' => now(),
        ]);

        News::create([
            'title' => 'Reunión de Consorcio',
            'description' => 'Próxima reunión de consorcio el día 20/12 a las 19:00 hs en el SUM. Asistencia importante.',
            'event_date' => '2025-12-05',
            'icon_type' => 'document',
            'color_scheme' => 'blue',
            'is_featured' => false,
            'order' => 2,
            'published_at' => now(),
        ]);

        News::create([
            'title' => 'Mantenimiento Completado',
            'description' => 'Finalizó el mantenimiento de las áreas comunes. Todas las instalaciones están operativas.',
            'event_date' => '2025-12-01',
            'icon_type' => 'check',
            'color_scheme' => 'green',
            'is_featured' => false,
            'order' => 3,
            'published_at' => now(),
        ]);
    }
}
