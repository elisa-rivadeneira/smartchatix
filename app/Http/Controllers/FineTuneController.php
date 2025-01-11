<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FineTuneController extends Controller
{
    public function uploadDataset(Request $request)
    {
        // Validar que se ha enviado una URL de archivo
        $request->validate([
            'file_url' => 'required|url',
        ]);

        // Obtener la URL del archivo
        $fileUrl = $request->input('file_url');

        // Descargar el archivo desde la URL
        $fileContent = file_get_contents($fileUrl);

        // Guardar el archivo en el almacenamiento local
        $filePath = storage_path('app/training_data.jsonl');
        file_put_contents($filePath, $fileContent);

        // Ahora que el archivo está guardado localmente, podemos subirlo a OpenAI
        $file = fopen($filePath, 'r');

        // Enviar el archivo a OpenAI para el fine-tuning
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
        ])->attach(
            'file', $file, 'training_data.jsonl'
        )->post('https://api.openai.com/v1/files');

        // Cerrar el archivo después de enviarlo
        fclose($file);

        // Manejar la respuesta de OpenAI
        if ($response->successful()) {
            return response()->json(['message' => 'Dataset uploaded successfully']);
        } else {
            return response()->json(['message' => 'Failed to upload dataset', 'error' => $response->body()], 400);
        }
    }
}
