<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Assistant; // 
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;
use App\Models\Document;
use Illuminate\Support\Str;
use App\Models\Conversation; // 
use App\Models\Message; // 



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
        //dd($assistants);
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
            'whatsapp_number' => 'required|string|max:255',
            'prompt' => 'required|string',
        ]);

        // Crear el asistente en la base de datos
        Assistant::create([
            'name' => $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'],
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
        'whatsapp_number' => 'required|string|max:255',
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

     $whatsappLink = "https://wa.me/{$assistant->whatsapp_number}?text=Hola,%20quiero%20hablar%20con%20un%20asesor.";
     $prompt .= "\n\n" . "Si deseas hablarr con un humano, puedes contactarnos a través de WhatsApp: " . $whatsappLink;
 


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
    $sessionId = $request->input('session_id') ?? Str::uuid()->toString();

    // Verificar si el asistente tiene un documento asociado
    $document = Document::where('assistant_id', $assistant->id)->first();
    if ($document) {
        $prompt .= "\n\n" . "Contenido del documento: " . $document->content;
    }

    Log::info('Prompt: ' . $prompt);


    // Interpretar la entrada del usuario
    $responseFromNLP = $this->interpretUserInput($user_input);
    Log::info('$responseFromNLP: ' . $responseFromNLP);

    if ($responseFromNLP === 'contactar_humano') {
        // Caso: Usuario quiere hablar con humano
        $whatsappLink = "https://wa.me/{$assistant->whatsapp_number}?text=Hola,%20quiero%20hablar%20con%20un%20asesor.";
        $htmlResponse = "
        <p>Si deseas conversar con uno de nuestros <strong>asesores</strong>, puedes contactarnos a través de WhatsApp:</p>
        <a href='{$whatsappLink}' target='_blank'>
            <button style='padding: 10px 20px; background-color: #25d366; color: white; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; justify-content: center;'>
                <!-- Ícono de WhatsApp con Font Awesome -->
                <i class='fab fa-whatsapp' style='margin-right: 10px; font-size: 24px;'></i>
                Hablar con un asesor
            </button>

        </a>
    ";

    // Guardar en el historial para mantener consistencia
    $chatHistory = ChatHistory::create([
        'assistant_id' => $assistant->id,
        'user_message' => $user_input,
        'assistant_response' => strip_tags($htmlResponse), // Guardamos la respuesta en texto plano
    ]);

    return response()->json([
        'user_message' => $chatHistory->user_message,
        'assistant_response' => $htmlResponse, // Aquí puedes retornar el HTML
        'session_id' => $sessionId,
    ])->cookie('chat_session_id', $sessionId, 120);
        

        Log::info('Necesitando hablar con humano: ' . json_encode($response));

        // Devolver respuesta para el humano
        return response()->json($response);
    } else {
        // Caso: Flujo normal de conversación con OpenAI
        // Obtener el historial de chat previo
        $chatHistories = ChatHistory::where('assistant_id', $assistant->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Construir el contexto basado en el historial
        $messages = [
            ['role' => 'system', 'content' => $prompt] // Incluir el prompt inicial
        ];
        foreach ($chatHistories as $chat) {
            $messages[] = ['role' => 'user', 'content' => $chat->user_message];
            $messages[] = ['role' => 'assistant', 'content' => $chat->assistant_response];
        }
        $messages[] = ['role' => 'user', 'content' => $user_input];

        // Llamada a la API de OpenAI
        $openAIResponse = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

        if ($openAIResponse->successful()) {
            $responseData = $openAIResponse->json();
            $generatedText = $responseData['choices'][0]['message']['content'];
            Log::info('Texto generado IA: ' . $generatedText);


            if (strpos($generatedText, 'hablar') !== false || strpos($generatedText, 'WhatsApp') !== false ) {
                // Reemplazar el enlace de texto plano con el botón HTML
                $whatsappLink = "https://wa.me/{$assistant->whatsapp_number}?text=Hola,%20quiero%20hablar%20con%20un%20asesor.";

                $generatedText= "<p>Si deseas hablar con un asesor, puedes contactarnos a través de WhatsApp:</p>
                                 <a href='{$whatsappLink}'?text=Holass,%20quiero%20hablar%20con%20un%20asesor' target='_blank'>
                                     <button style='padding: 10px 20px; background-color: #25d366; color: white; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; justify-content: center;'>
                                         <i class='fab fa-whatsapp' style='margin-right: 10px; font-size: 24px;'></i>
                                         Hablar con un asesor
                                     </button>
                                 </a>";
            }else if ( strpos($generatedText, 'prueba') !== false){

                $whatsappLink = "https://wa.me/{$assistant->whatsapp_number}?text=Hola,%20quiero%20realizar%20la%20prueba%20gratuita%20de%20SmartChatix%20de%2015%20días.";

                $generatedText= "<p>Si deseas hacer la prueba gratuita de 15 días, puedes solicitarlo a través de WhatsApp:</p>
                                 <a href='{$whatsappLink}'?text=Hola,%20quiero%20realizar%20la%20prueba%20gratuita%20de%201520%dias' target='_blank'>
                                     <button style='padding: 10px 20px; background-color: #25d366; color: white; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; justify-content: center;'>
                                         <i class='fab fa-whatsapp' style='margin-right: 10px; font-size: 24px;'></i>
                                         Solicitar Prueba Gratuita
                                     </button>
                                 </a>";

            }


            // Guardar en el historial
            $chatHistory = ChatHistory::create([
                'assistant_id' => $assistant->id,
                'user_message' => $user_input,
                'assistant_response' => $generatedText,
            ]);

            // Guardar conversación y mensajes
            $conversation = Conversation::firstOrCreate(
                ['session_id' => $sessionId, 'assistant_id' => $assistant->id],
                ['assistant_id' => $assistant->id]
            );
            Message::create(['conversation_id' => $conversation->id, 'sender' => 'user', 'message' => $user_input]);
            Message::create(['conversation_id' => $conversation->id, 'sender' => 'assistant', 'message' => $generatedText]);

            // Devolver respuesta
            return response()->json([
                'user_message' => $chatHistory->user_message,
                'assistant_response' => $chatHistory->assistant_response,
                'session_id' => $sessionId,
            ])->cookie('chat_session_id', $sessionId, 120);
        } else {
            Log::error('Error en la llamada a OpenAI: ' . $openAIResponse->body());
            return response()->json(['error' => 'Error al generar la respuesta.'], 500);
        }
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


//////////////////probando

public function interpretUserInput($user_input)
{

    Log::info('En funcion interpreteruserinput');
    // Aquí defines la estructura del mensaje que le envías a OpenAI
    $openAIResponse = $this->askOpenAI_pidehumano($user_input);


    // Supongamos que OpenAI te devuelve un resultado indicando si el usuario quiere hablar con un humano
    return $openAIResponse;
}

public function askOpenAI_pidehumano($user_input)
{

    // Configura tu clave de API de OpenAI
    $apiKey = config('services.openai.api_key');
    

    $client = new \GuzzleHttp\Client();
    


    // Incluir el mensaje actual del usuario
$messages[] = ['role' => 'user', 'content' => $user_input];
$messageString = json_encode($messages);  // Encode the array as JSON

Log::info('Mensaje enviado:::'. $messageString);

    $response = Http::withToken(config('services.openai.api_key'))
    ->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 1000,
        'temperature' => 0.7,
    ]);


    $body = $response->getBody();


    $data = json_decode($body, true);
    // Check if $data is an array before logging
        if (is_array($data)) {
            Log::info('data:', $data);
        } else {
            // Handle the case where $data is not an array (e.g., log an error message)
            Log::error('Unexpected data type for $body. Expected array, got: ' . gettype($data));
        }


    // Aquí interpreto si la respuesta contiene un interés por hablar con un humano
    $intent = strtolower($user_input); // Extraer el intent del mensaje original

    Log::info('Intent (original): ' . $intent);

    // Utilizar expresiones regulares o técnicas de NLP más avanzadas para detectar la intención
    if (preg_match('/(hablar con un humano| necesito hablar con un asesor |  quiero hablar con un humano | quiero hablar con un humano|quiero hablar con una persona|necesito un agente humano|quiero hablar con alguien)/i', $intent)) {
        return 'contactar_humano';
    }

    return 'sin_intencion_especifica';
}



}