<?php
namespace App\Jobs;

use App\Models\Assistant;
use App\Models\DocumentTraining;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FineTuneAssistantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $assistantId;
    public $documentTrainingId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($assistantId, $documentTrainingId)
    {
        $this->assistantId = $assistantId;
        $this->documentTrainingId = $documentTrainingId;
        Log::info("En construct::::sipi");
        Log::info("AssistandId::::sipi". $assistantId);
        Log::info("DocumentTrainingId" . $documentTrainingId);

    }

    /**
     * Execute the job.
     *
     * @return void
     */

// public function handle()
// {
//     Log::info("El job ha llegado al handle.");
// }

//     public function handle()
//     {
//         Log::info("::::::Log En metodo handle::::");
//         $assistant = Assistant::findOrFail($this->assistantId);
//         $documentTraining = DocumentTraining::findOrFail($this->documentTrainingId);
    
//         Log::info("Empezando el handle con assistant_id: {$assistant->id}, document_training_id: {$documentTraining->id}");
    


//         $apiKey = config('services.openai.api_key'); // Obtén la clave desde la configuración
//         $fileId = $documentTraining->file_id; // El ID del archivo de entrenamiento cargado


//         Log::info("FileId es ::: {$fileId}");

    

//         Log::info("En el log del jog... empezando el job");
//         try {
//             // Enviar solicitud a OpenAI para iniciar el fine-tuning
//             // $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/fine-tuning/jobs', [
//             //     'training_file' => $fileId,
//             //     'model' => 'gpt-4-turbo', // Modelo actualizado
//             // ]);



//             $response = Http::withHeaders([
//                 'Authorization' => 'Bearer ' . $apiKey,
//                 'Content-Type' => 'application/json',
//             ])->post('https://api.openai.com/v1/fine_tuning/jobs', [
//                 'training_file' => $fileId,
//                 'model' => 'gpt-4-turbo',
//             ]);

//             if ($response->successful()) {
//                 $responseData = $response->json();

//                 // Actualizar el modelo DocumentTraining con el ID del fine-tuning y su estado
//                 $this->documentTraining->fine_tune_id = $responseData['id']; // ID del trabajo de fine-tuning
//                 $this->documentTraining->status = 'fine-tuning';
//                 $this->documentTraining->save();

//                 Log::info("Fine-tuning iniciado con éxito para el archivo: $fileId");
//             } else {
//                 // Manejar errores en la respuesta
//                 throw new \Exception('Error iniciando el fine-tuning: ' . $response->body());
//             }
//         } catch (\Exception $e) {
//             // Registrar errores en los logs
//             Log::error('Error iniciando el fine-tuning: ' . $e->getMessage());
//             throw $e;
//         }
    
// }


public function handle()
{

        $apiKey = config('services.openai.api_key'); // Obtén la clave desde la configuración

    // Obtener el documento de entrenamiento
    $documentTraining = DocumentTraining::find($this->documentTrainingId);

    // Construir la ruta completa del archivo
    $filePath = storage_path("app/public/{$documentTraining->path}");

    // Subir el archivo a OpenAI
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,  // Asegúrate de que el API key esté configurado correctamente
    ])->attach(
        'file', fopen($filePath, 'r'), 'training_data.jsonl'  // El archivo a subir
        )->post('https://api.openai.com/v1/files', [
            'purpose' => 'fine-tune',  // Aquí agregamos el propósito del archivo
        ]);

    // Verificar la respuesta
    if ($response->successful()) {
        $fileId = $response->json()['id'];  // Obtener el file_id
        Log::info("Archivo subido con éxito. File ID: {$fileId}");

        // Aquí debes actualizar tu modelo de DocumentTraining con el file_id
        $documentTraining->file_id = $fileId;
        $documentTraining->save();



        // Ahora hacemos la solicitud para el fine-tuning
        $fineTuneResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/fine_tuning/jobs', [
            'training_file' => $fileId,  // Este es el file_id del archivo JSONL subido
            'model' => 'gpt-3.5-turbo',         // Modelo base para el fine-tuning
        ]);
        
        // Verificar la respuesta
        if ($fineTuneResponse->successful()) {
            $fineTuneJobId = $fineTuneResponse->json()['id'];  // Obtener el ID del trabajo
            Log::info("Fine-tuning iniciado con éxitoooooo. Job ID: {$fineTuneJobId}");
            Log::info("Otro Logg mas prueba linea 149");

            // Actualizar el campo `fine_tuning_job_id` en la tabla `assistants`
            

            $assistantId=$documentTraining->assistant_id;
            Log::info("assistantId es ". $assistantId);
            $assistant = Assistant::find($assistantId); // $assistantId es el ID del asistente
            if ($assistant) {
                $assistant->fine_tuning_job_id = $fineTuneJobId;
                $assistant->save(); // Guardar el ID del trabajo de fine-tuning en la base de datos
                Log::info("Fine-tuning Job ID almacenado correctamente para el asistente.");
            } else {
                Log::error("Asistente no encontrado. No se pudo almacenar el Job ID.");
            }

        } else {
            Log::error('Error al iniciar el fine-tuning:', $fineTuneResponse->json());
        }
        

        

        // Verificar la respuesta de la solicitud de fine-tuning
        if ($fineTuneResponse->successful()) {
            $fineTuneJobId = $fineTuneResponse->json()['id'];
            Log::info("Fine-tuning iniciado con éxito. Job ID: {$fineTuneJobId}");
        } else {
            Log::error('Error al iniciar el fine-tuning:', $fineTuneResponse->json());
        }

    } else {
        Log::error('Error al subir el archivo:', $response->json());
        // Puedes manejar el error aquí de la forma que necesites
    }
}

}