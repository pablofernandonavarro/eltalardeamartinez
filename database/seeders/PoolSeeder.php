<?php

namespace Database\Seeders;

use App\Models\Pool;
use App\Models\PoolRule;
use App\PoolStatus;
use Illuminate\Database\Seeder;

class PoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pool1 = Pool::create([
            'name' => 'Pileta Principal',
            'status' => PoolStatus::Habilitada,
            'description' => 'Pileta principal del complejo',
        ]);

        $pool2 = Pool::create([
            'name' => 'Pileta Infantil',
            'status' => PoolStatus::Habilitada,
            'description' => 'Pileta para niños',
        ]);

        PoolRule::create([
            'pool_id' => $pool1->id,
            'max_guests_per_unit' => 2,
            'max_entries_per_day' => 3,
            'allow_guests' => true,
            'only_owners' => false,
            'valid_from' => now()->startOfYear(),
            'valid_to' => null,
        ]);

        PoolRule::create([
            'pool_id' => $pool2->id,
            'max_guests_per_unit' => 3,
            'max_entries_per_day' => 5,
            'allow_guests' => true,
            'only_owners' => false,
            'valid_from' => now()->startOfYear(),
            'valid_to' => null,
        ]);
    }
}
