<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ModelSeeder; // Asegúrate de importar el seeder

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Llama al ModelSeeder para ejecutar su método run
        $this->call(ModelSeeder::class);
    }
}