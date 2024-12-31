<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Assistant; // Asegúrate de tener un modelo Assistant
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;
use App\Models\Document;



class AssistantController extends Controller
{
    // Muestra el formulario para crear un asistente
    public function create()
    {
        return view('assistants.create'); // Devuelve la vista de creación
    }

    public function index()
    {
        $assistants = Assistant::where('user_id', auth()->id())->get();
        //$assistants = Assistant::all(); // Obtenemos todos los asistentes
        return view('assistants.index', compact('assistants'));
    }

    public function show($id)
    {
        $assistant = Assistant::findOrFail($id);

        $chatHistories = ChatHistory::where('assistant_id', $id)->get(); // Obtener historial de chat para este asistente
        
        return view('assistants.show', compact('assistant', 'chatHistories'));
    }

    // Almacena un nuevo asistente
    public function store(Request $request)
    {

        $user_id= auth()->id();
        //dd($user_id);

        // Validación de los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string',
        ]);

        // Crear el asistente en la base de datos
        Assistant::create([
            'name' => $validated['name'],
            'prompt' => $validated['prompt'],
            'user_id' => $user_id,
        ]);

        // Redirigir a la lista de asistentes o a una página de éxito
        return redirect()->route('assistants.index')->with('success', 'Asistente creado con éxito.');
    }

    public function edit($id)
{
    // Buscar el asistente por ID
    $assistant = Assistant::findOrFail($id);
    $document = Document::where('assistant_id', $id)->first(); // Buscar documento asociado
    ($document);

    // Retornar la vista de edición con los datos del asistente
    return view('assistants.edit', compact('assistant', 'document'));

}

    public function update(Request $request, $id)
{
    // Validar los datos
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'prompt' => 'required|string',
    ]);

    // Buscar y actualizar el asistente
    $assistant = Assistant::findOrFail($id);
    $assistant->update($validated);

    // Redirigir a la vista index con mensaje de éxito
    return redirect()->route('assistants.index')->with('success', 'Asistente actualizado con éxito.');
}

public function destroy($id)
{
    // Buscar y eliminar el asistente
    $assistant = Assistant::findOrFail($id);
    $assistant->delete();

    // Redirigir a la vista index con un mensaje de éxito
    return redirect()->route('assistants.index')->with('success', 'Asistente eliminado con éxito.');
}


public function generateResponse(Request $request, $id)
{


    $assistant = Assistant::findOrFail($id);

    $prompt = $assistant->prompt;
    $user_input = $request->input('user_input');

     // Verificar si el asistente tiene un documento asociado
      $document = Document::where('assistant_id', $assistant->id)->first();
     
    //dd($document);
     // Si el documento existe, concatenar su contenido al prompt
     if ($document) {
         $prompt .= "\n\n" . "Contenido del documento: " . $document->content;
     }

    // Obtener el historial de chat previo para este asistente
    $chatHistories = ChatHistory::where('assistant_id', $assistant->id)
    ->orderBy('created_at', 'asc') // Asegurar que el historial esté en orden cronológico
    ->get();

    // Construir el contexto basado en el historial
    $messages = [
        ['role' => 'system', 'content' => $prompt] // Incluir el prompt inicial
    ];

    foreach ($chatHistories as $chat) {
        $messages[] = ['role' => 'user', 'content' => $chat->user_message];
        $messages[] = ['role' => 'assistant', 'content' => $chat->assistant_response];
    }

    // Incluir el mensaje actual del usuario
    $messages[] = ['role' => 'user', 'content' => $user_input];


    $response = Http::withToken(config('services.openai.api_key'))
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ]);

    if ($response->successful()) {
        $responseData = $response->json();
        $generatedText = $responseData['choices'][0]['message']['content'];

        // Guardar en el historial
        $chatHistory = ChatHistory::create([
            'assistant_id' => $assistant->id,
            'user_message' => $user_input,
            'assistant_response' => $generatedText,
        ]);

        // Devolver la respuesta como JSON
        // return response()->json([
        //     'user_message' => $chatHistory->user_message,
        //     'assistant_response' => $chatHistory->assistant_response
        // ]);

        // Verificar si ya existe una conversación activa
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'assistant_id' => $assistant->id,
            ],
            [
                'title' => "Conversación con {$assistant->name}", // O cualquier otro título dinámico
            ]
        );

        // Guardar los mensajes en la tabla Messages
        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'user', // Indica que el mensaje es del usuario
            'content' => $user_input,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'assistant', // Indica que el mensaje es del asistente
            'content' => $generatedText,
        ]);

        // Devolver la respuesta como JSON
        return response()->json([
            'conversation_id' => $conversation->id,
            'user_message' => $user_input,
            'assistant_response' => $generatedText
        ]);

    } else {
        return response()->json(['error' => 'Error al generar la respuesta.'], 500);
    }
}


public function publicGenerateResponse(Request $request, $id)
{
    Log::info('Entrando al método publicGenerateResponse con ID: ' . $id);


$assistant = Assistant::findOrFail($id);

$prompt = $assistant->prompt;
$user_input = $request->input('user_input');

Log::info('Request::::' . $request);
// Verificar si el asistente tiene un documento asociado
$document = Document::where('assistant_id', $assistant->id)->first();

// Si el documento existe, concatenar su contenido al prompt
if ($document) {
    $prompt .= "\n\n" . "Contenido del documento: " . $document->content;
}
  Log::info('$prompt:' . $prompt);

// Obtener el historial de chat previo para este asistente
$chatHistories = ChatHistory::where('assistant_id', $assistant->id)
    ->orderBy('created_at', 'asc') // Asegurar que el historial esté en orden cronológico
    ->get();


// Construir el contexto basado en el historial
$messages = [
    ['role' => 'system', 'content' => $prompt] // Incluir el prompt inicial
];

foreach ($chatHistories as $chat) {
    $messages[] = ['role' => 'user', 'content' => $chat->user_message];
    $messages[] = ['role' => 'assistant', 'content' => $chat->assistant_response];
}

// Incluir el mensaje actual del usuario
$messages[] = ['role' => 'user', 'content' => $user_input];

// Llamada a la API de OpenAI
$response = Http::withToken(config('services.openai.api_key'))
    ->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 1000,
        'temperature' => 0.7,
    ]);

if ($response->successful()) {
    $responseData = $response->json();
    $generatedText = $responseData['choices'][0]['message']['content'];

    // Guardar en el historial
    $chatHistory = ChatHistory::create([
        'assistant_id' => $assistant->id,
        'user_message' => $user_input,
        'assistant_response' => $generatedText,
    ]);

    // Devolver la respuesta como JSON
    return response()->json([
        'user_message' => $chatHistory->user_message,
        'assistant_response' => $chatHistory->assistant_response
    ]);
} else {
    return response()->json(['error' => 'Error al generar la respuesta.'], 500);
}
}


public function uploadDocument(Request $request, $assistant)
{
    // Validar el archivo
    $validated = $request->validate([
        'file' => 'required|file|mimes:pdf,docx',
    ]);

    // Guardar el archivo en storage
    $path = $request->file('file')->store('documents');

    // Crear el registro del documento
    $document = Document::create([
        'asistente_id' => $assistant,
        'filename' => $request->file('file')->getClientOriginalName(),
        'path' => $path,
    ]);

    // Procesar el archivo para extraer el contenido
    $this->processFile($document);

    return back()->with('success', 'Documento subido y procesado correctamente.');
}


}