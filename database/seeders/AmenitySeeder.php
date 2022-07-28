<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // prepare data to be seeded
        $data = [
            ['name'=>'internet'],
            ['name'=>'electricity'],
            ['name'=>'lounge'],
        ];

        foreach ($data as $item) {
            Amenity::firstOrCreate($item);
        }
    }
}
