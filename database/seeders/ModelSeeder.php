<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIModel; // Importar el modelo AIModel

class ModelSeeder extends Seeder
{
    public function run()
    {
        // Crear los modelos de ejemplo
        AIModel::create([
            'name' => 'GPT-3.5 Turbo',
            'identifier' => 'gpt-3.5-turbo',
            'description' => 'Modelo genérico GPT-3.5 Turbo'
        ]);

        AIModel::create([
            'name' => 'GPT-4',
            'identifier' => 'gpt-4',
            'description' => 'Modelo genérico GPT-4'
        ]);
    }
}
