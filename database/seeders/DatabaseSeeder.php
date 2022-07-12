<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // call artisan command to install passport
        Artisan::call('passport:install');
        Artisan::call('passport:keys');

        // run seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            WorldSeeder::class,
        ]);
    }
}
