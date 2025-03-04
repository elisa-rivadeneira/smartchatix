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
use App\Models\User; // 
use App\Models\DocumentTraining; // 
use App\Jobs\FineTuneAssistantJob;
use App\Models\AIModel;
use Illuminate\Support\Facades\DB; // Asegúrate de importar DB aquí
use App\Models\Course; // 
use Parsedown;
use App\Mail\EnviarEmailCliente;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;




class AssistantController extends Controller
{
    // Muestra el formulario para crear un asistente
    public function create()
    {
        $models = AIModel::all(); // Obtener todos los modelos

        return view('assistants.create', compact('models'));
    
    }

    public function index()
    {
        $assistants = Assistant::where('user_id', auth()->id())
        ->join('a_i_models', 'assistants.model_id', '=', 'a_i_models.id')  // Hacer el join con la tabla de modelos
        ->select('assistants.*', 'a_i_models.name as model_name')  // Seleccionar todos los campos de assistants y el nombre del modelo
        ->get();
        $user = User::find(auth()->id());

        //$assistants = Assistant::all(); // Obtenemos todos los asistentes
     //  dd($user);
        return view('assistants.index', compact('assistants','user'));
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

       // dd($request);
        $user_id= auth()->id();

        // Validación de los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:255',
            'prompt' => 'required|string',
            'model_id' => 'required|exists:a_i_models,id', // Asegúrate de que el model_id sea válido
            'type' => 'required|string', // Validación para el campo 'type'
        ]);

      //  dd($validated);

        // Crear el asistente en la base de datos
        Assistant::create([
            'name' => $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'prompt' => $validated['prompt'],
            'user_id' => $user_id,
            'model_id' => $validated['model_id'],
            'type' => $request->input('type'), // Guardar el campo 'type'


        ]);

        // Redirigir a la lista de asistentes o a una página de éxito
        return redirect()->route('assistants.index')->with('success', 'Asistente creado con éxito.');
    }

    public function edit($id)
{


    // Buscar el asistente por ID
    $assistant = Assistant::with('model')  // Si tienes una relación definida en el modelo Assistant
    ->join('a_i_models', 'assistants.model_id', '=', 'a_i_models.id') // Hacer el join con la tabla de modelos
    ->select('assistants.*', 'a_i_models.name as model_name') // Seleccionar los campos de assistants y el nombre del modelo
    ->where('assistants.id', $id)  // Filtrar por ID
    ->first();  // Obtener el primer (y único) resultado

    $models = AIModel::all(); // Obtener todos los modelos

    


    $document = Document::where('assistant_id', $id)->first(); // Buscar documento asociado
   // ($document);

    // Retornar la vista de edición con los datos del asistente
    return view('assistants.edit', compact('assistant', 'document', 'models'));

}

    public function update(Request $request, $id)
{

   // Log::info('$request:::'.$request);

    Log::info('$id:::'.$id);
    // Validar los datos
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'whatsapp_number' => 'required|string|max:255',
        'prompt' => 'required|string',
        'model_id' => 'required|exists:a_i_models,id', // Asegúrate de que el model_id sea válido
        'type' => 'required|string', // Validación para el campo 'type'

    ]);
    Log::info('$estavalidadoooo el asistente');

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


public function publicGenerateResponse_old(Request $request, $id)
{
    Log::info('Entrando al método publicGenerateResponse con ID: ' . $id);

    $assistant = Assistant::findOrFail($id);

    $model = DB::table('assistants')
    ->join('a_i_models', 'assistants.model_id', '=', 'a_i_models.id')
    ->where('assistants.id', $assistant->id) // Filtrar por el ID del asistente
    ->select('a_i_models.identifier as model_identifier')
    ->first(); // Obtener solo el primer resultado

    // Obtener el nombre del modelo
    $model_identifier = $model->model_identifier;

    Log::info('El modelo que llamamos es ::::'.$model_identifier);

    $prompt = $assistant->prompt;

   // Log::info('Prompt inicial_______: ' . $prompt);

    $user_input = $request->input('user_input');
    $sessionId = $request->input('session_id') ?? Str::uuid()->toString();

   // Log::info('Request:::::::: ' . $request);
    Log::info('sessionId:::::::: ' . $sessionId);



    // Verificar si el asistente tiene un documento asociado
    $document = Document::where('assistant_id', $assistant->id)->first();
    if ($document) {
        $prompt .= "\n\n" . "Contenido del documento: " . $document->content;
    }



    $prompt .= "Responde de manera clara y estructurada. Usa Markdown para formatear tus respuestas:
        - Listas para enumeraciones.
        - Negrillas para destacar palabras importantes.
        - Títulos y subtítulos si es necesario.
        - Saltos de línea para separar ideas.
        
        Ejemplo:
         Lugares a visitar en  Lima:
        - Parque de las Aguas : Hermoso parque de agua con efectos muy llamativos 
        - Parque Wiracocha : Ideal para pasar un dia en familia
        - Costa Verde : Ver el sol, conversar con el mar. Pasar momentos inolvidables";



    // Interpretar la entrada del usuario
    $responseFromNLP = $this->interpretUserInput($user_input);

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
                //'model' => 'gpt-3.5-turbo',
              //  'model' => 'gpt-4o-mini',
              //'model' => 'ft:gpt-3.5-turbo-0125:personal::An7XBcbe',
                'model' => $model_identifier,
              
              
              'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.1,
            ]);

        if ($openAIResponse->successful()) {
            $responseData = $openAIResponse->json();
            $generatedText = $responseData['choices'][0]['message']['content'];
                // Captura los datos de uso
            $promptTokens = $responseData['usage']['prompt_tokens'] ?? 0;
            $completionTokens = $responseData['usage']['completion_tokens'] ?? 0;
            $totalTokens = $responseData['usage']['total_tokens'] ?? 0;


            // Formatear el texto generado
            $generatedText = nl2br($generatedText); // Convertir saltos de línea a <br>
            $generatedText = preg_replace('/^- (.+)/m', '<li>$1</li>', $generatedText);
            if (strpos($generatedText, '<li>') !== false) {
                $generatedText = "<ul>" . $generatedText . "</ul>";
            }




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

            Log::info('session_id: ' . $sessionId);
            Log::info('assistant_id: ' . $assistant->id);

            $conversation = Conversation::where('session_id', $sessionId)
            ->where('assistant_id', $assistant->id)
            ->first();



        $user = User::find($assistant->user_id); // El id_user es la relación con el usuario


        if (!$conversation) {
            Log::info('Primer mensaje con tokens: ' . $totalTokens);
        
            $conversation = Conversation::create([
                'session_id' => $sessionId,
                'assistant_id' => $assistant->id,
                'total_tokens' => $totalTokens,
            ]);

             // Actualizar el total de tokens usados por el asistente
             $assistant->total_tokens_used += $totalTokens;
             $assistant->save();
         

             $user->total_tokens_used += $totalTokens;
             $user->save();


        
            Log::info('Nueva conversación creada: ' . json_encode($conversation->toArray()));
        } else {
            Log::info('Mensaje secundario: Incrementando tokens');
        
            // Incrementar los tokens si ya existe la conversación
            $conversation->increment('total_tokens', $totalTokens);
            


            // Actualizar el total de tokens usados por el asistente
            $assistant->total_tokens_used += $totalTokens;
            $assistant->save();

            $user->total_tokens_used += $totalTokens;
            $user->save();

        
            Log::info('Tokens actualizados: ' . $conversation->total_tokens);
        }
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

public function publicGenerateResponse(Request $request, $id)
{

    // Obtener el asistente con el ID proporcionado
    $assistant = Assistant::findOrFail($id);

    // Obtener el tipo de asistente (curso, producto, servicio)
    $type = $assistant->type;

    $model = DB::table('assistants')
    ->join('a_i_models', 'assistants.model_id', '=', 'a_i_models.id')
    ->where('assistants.id', $assistant->id) // Filtrar por el ID del asistente
    ->select('a_i_models.identifier as model_identifier')
    ->first(); // Obtener solo el primer resultado

    // Obtener el nombre del modelo
    $model_identifier = $model->model_identifier;

    $prompt = $assistant->prompt;

     $user_input = $request->input('user_input');
     $sessionId = $request->input('session_id') ?? Str::uuid()->toString();
 

    // Procesar la solicitud de acuerdo al tipo de asistente
    $response = '';

    $generatedText='Texto generado por la IA';

    $totalTokens=0;

    if (strpos($user_input, 'hablar') !== false || strpos($user_input, 'WhatsApp') !== false ) {
        // Reemplazar el enlace de texto plano con el botón HTML
        $whatsappLink = "https://wa.me/{$assistant->whatsapp_number}?text=Hola,%20quiero%20hablar%20con%20un%20asesor.";

        $generatedText= "<p>Si deseas hablar con un asesor, puedes contactarnos a través de WhatsApp:</p>
                         <a href='{$whatsappLink}'?text=Holass,%20quiero%20hablar%20con%20un%20asesor' target='_blank'>
                             <button style='padding: 10px 20px; background-color: #25d366; color: white; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; justify-content: center;'>
                                 <i class='fab fa-whatsapp' style='margin-right: 10px; font-size: 24px;'></i>
                                 Hablar con un asesor
                             </button>
                         </a>";
    }else{

        switch ($type) {
            case 'curso':
                // Lógica personalizada para el tipo 'curso'
                $generatedText = $this->generateCourseResponse($assistant, $request);
                break;
            case 'producto':
                // Lógica personalizada para el tipo 'producto'
                $response = $this->generateProductResponse($assistant, $request);
                break;
            case 'developer':
                // Lógica personalizada para el tipo 'developer'
                $generatedText = $this->generateDeveloperResponse($assistant, $request);
                break;
            case 'servicio':
                // Lógica personalizada para el tipo 'servicio'
                $generatedText = $this->generateServiceResponse($assistant, $request);
                break;
            case 'base_de_datos':
                // Lógica personalizada para el tipo 'servicio'
                $generatedText = $this->generateDBResponse($assistant, $request);
                break;

    
            default:
                // Caso por defecto si el tipo no es reconocido
                $response = 'Tipo de asistente no válido.';
                break;
        }


    }
    

   



    $chatHistory = ChatHistory::create([
        'assistant_id' => $assistant->id,
        'user_message' => $user_input,
        //'assistant_response' => $generatedText,
        'assistant_response' => is_array($generatedText)
        ? $generatedText['assistant_response']
        : $generatedText,
        'chart_data' => is_array($generatedText)
        ? json_encode($generatedText['chart_data'])
        : null,
    ]);

    Log::info('Linea 582');

    $conversation = Conversation::where('session_id', $sessionId)
    ->where('assistant_id', $assistant->id)
    ->first();


    $user = User::find($assistant->user_id); // El id_user es la relación con el usuario

    Log::info('Linea 591');

    if (!$conversation) {

        $conversation = Conversation::create([
            'session_id' => $sessionId,
            'assistant_id' => $assistant->id,
            'total_tokens' => $totalTokens,
        ]);

        // Actualizar el total de tokens usados por el asistente
        $assistant->total_tokens_used += $totalTokens;
        $assistant->save();
    

        $user->total_tokens_used += $totalTokens;
        $user->save();



    } else {

        // Incrementar los tokens si ya existe la conversación
        $conversation->increment('total_tokens', $totalTokens);
        


        // Actualizar el total de tokens usados por el asistente
        $assistant->total_tokens_used += $totalTokens;
        $assistant->save();

        $user->total_tokens_used += $totalTokens;
        $user->save();


    }
        //Log::info('GenerateText en linea 627:'.$generatedText);
        if (is_array($generatedText)) {
                // Tiene 'assistant_response'
                $assistantText = $generatedText['assistant_response'];
            } else {
                // Es solo un string
                $assistantText = $generatedText;
            }
        Message::create(['conversation_id' => $conversation->id, 'sender' => 'user', 'message' => $user_input]);
        Message::create(['conversation_id' => $conversation->id, 'sender' => 'assistant', 'message' => $assistantText]);

    Log::info('Linea 630');

    $coursesession = session('course_name');
    Log::info('Linea 633');

    if($coursesession){


    }

        Log::info('Linea 648');



    return response()->json([
        'user_message' => $user_input,
        'assistant_response' => $assistantText,
        'session_id' => $sessionId,
        'chart_data' => (is_array($generatedText) && isset($generatedText['chart_data']))
            ? $generatedText['chart_data']
            : null
    ])->cookie('chat_session_id', $sessionId, 120);
    // Retornar la respuesta generada
    //return response()->json(['response' => $response]);
}

private function generateCourseResponse($assistant, $request)
{
    Log::info('assistant:->'.$assistant);

    $prompt=$assistant->prompt;

    return $this->generateOpenAIResponse($prompt,$request->user_input);

}

private function generateOpenAIResponse($prompt , $mensaje)
    {

        $prompt.='Tu tarea es responder acerca de los cursos que brindamos';



        $prompt .= "Responde de manera cordial, clara y estructurada. Usa Markdown para formatear tus respuestas:
        - Listas para enumeraciones.
        - Negrillas para destacar palabras importantes.
        - Títulos y subtítulos si es necesario.
        - Saltos de línea para separar ideas.
        Las respuestas no deben ser mas de 10 lineas
        ";


        // Obtener todos los cursos de la base de datos
        $courses = Course::all();  // Asegúrate de tener el modelo Course importado


        // Crear una lista de cursos para pasar al modelo
        $coursesList = $courses->map(function($course) {
            return $course->title;  // Solo los títulos de los cursos, o agrega más información según lo necesites
        });

        $coursesListText = implode(', ', $coursesList->toArray());  // Convertir la lista a un string


        // Mensaje enviado por el usuario

        $detectedCourse = $this->detectCourseQuery($mensaje); // Buscar si mencionan un curso

   
        if ($detectedCourse != optional(session('course_name'))->id && optional(session('course_name'))->id !== null) {
        $detectedCourse = session('course_name');
        }


    if ($detectedCourse)   {

                Log::info('Detecto el curso:'.$detectedCourse->title);
                session(['course_name' => $detectedCourse]);

                Log::info('Contenido de la sesión:', session()->all());

                $imageUrl = url('storage/' . $detectedCourse->imagen); // Asegúrate de que las imágenes estén en public/storage
                // Si se detectó un curso específico, construye la respuesta con los detalles
                $prompt .= "El curso del que tienes que dar informacion concisa y precisa pero no muy larga es '{$detectedCourse->title}'. Aquí tienes más información: " .
                "{$detectedCourse->description}. " .
                "Este curso tiene una duración de {$detectedCourse->duration}, se dicta en modalidad {$detectedCourse->modalidad} y cuesta {$detectedCourse->price}. " .
                "El profesor a cargo es {$detectedCourse->teacher}, quien tiene el siguiente perfil: {$detectedCourse->description_teacher}.".
                "Preguntar si desea mas informacion acerca del curso o si desea inscribirse";
                "Solo dar informacion precisa de todos los temas si el usuario te pide tema o temario";
         
                Log::info('imageUrl'.$imageUrl);

                if($detectedCourse->imagen){
                    Log::info('Si hay imageUrl');
                    $prompt .= "Además, puedes ver una imagen representativa del curso aquí, solo la muestras si existe. Y tambien mostrar solo la primera vez que hables del curso al final de todo el texto:![Imagen del curso]({$imageUrl}) ";
                }

            } else {
                    Log::info('No detecto el curso');
                    // Si no se menciona un curso específico, lista todos los cursos disponibles
                    $courses = Course::all(['title']); // Solo selecciona los títulos
                    $coursesList = $courses->pluck('title')->implode(', ');
                
                    $prompt .= "Los cursos que ofrecemos son: $coursesList. Preguntar si desea saber acerca de algun curso o servicio adicional";
                }
            



    
    $instruction = [
        [
            "role" => "system", 
            "content" => $prompt
            
            ]
    ];

    // Agregar el mensaje del usuario al prompt
    $messages = array_merge($instruction, [
        [
            'role' => 'user', 
            'content' => $mensaje
        ]
    ]);


    $openAIResponse = Http::withToken(config('services.openai.api_key'))
    ->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 1000,
        'temperature' => 0.1,
    ]);

       


    if ($openAIResponse->successful()) {
        Log::info('Pasando por aquiiiii.... en openairepsonse');
        $responseData = $openAIResponse->json();

        $generatedText = $responseData['choices'][0]['message']['content'];


        // Formatear el texto generado
        $generatedText = nl2br($generatedText); // Convertir saltos de línea a <br>
        $generatedText = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $generatedText);

        // Convertir los encabezados en <h6>
        $generatedText = preg_replace('/^### (.+)/m', '<h6>$1</h6>', $generatedText);

        // Convertir las listas con guion (-) en elementos <li>
        $generatedText = preg_replace('/^- (.+)/m', '<li>$1</li>', $generatedText);

        // Envolver las listas en <ul> solo si hay elementos <li>
        if (strpos($generatedText, '<li>') !== false) {
            // Envolver solo el contenido de la lista en un <ul>
            $generatedText = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$0</ul>', $generatedText);
        }

        // Procesar las imágenes
        $generatedText = preg_replace_callback(
            '/!\[(.*?)\]\((https?:\/\/[^\s]+\.(jpg|jpeg|png|gif|webp|svg))\)/i',
            function ($matches) {
                return '<img src="' . htmlspecialchars($matches[2]) . '" alt="' . htmlspecialchars($matches[1]) . '" style="max-width:100%; height:auto;" />';
            },
            $generatedText
        );



    }else{

        return response()->json(['error' => 'Error al generar la respuesta.'], 500);
    }


    return $generatedText;
}


private function detectCourseQuery($message) {
    // Buscar un curso cuyo título o categoría coincida con el mensaje del usuario
    $courses = Course::all(); // Traer todos los cursos de la base de datos
    $bestMatch = null;
    $highestScore = 0;

    foreach ($courses as $course) {
        // Calcular la similitud entre el mensaje del usuario y el título del curso
        similar_text(strtolower($message), strtolower($course->title), $percentage);

        // Si el porcentaje es mayor que el puntaje más alto encontrado hasta ahora, actualizamos
        if ($percentage > $highestScore) {
            $highestScore = $percentage;
            $bestMatch = $course;
        }
    }

    // Retornar el curso con mayor similitud si supera un umbral razonable
    return $highestScore > 40 ? $bestMatch : null; // 40% es un umbral inicial, ajústalo según tus necesidades

}

// Generar respuesta para producto
private function generateProductResponse($assistant, $request)
{
    // Aquí puedes implementar la lógica para generar una respuesta
    // Ejemplo: consultar detalles del producto y generar la respuesta
    $productInfo = "Detalles del producto: " . $assistant->info;
    // Generación de respuesta personalizada
    return "Generando respuesta para producto: " . $productInfo;
}
private function generateServiceResponse($assistant, $request)
{
    $prompt=$assistant->prompt;
    return $this->generateOpenAIResponse_Service($prompt,$request->user_input);
}


private function generateOpenAIResponse_Service($prompt , $mensaje)

{
        $generatedText='';
        Log::info('En funcion generateOpenAIResponse programming');

       $prompt.='Atiendes a los clientes de manera atenta y cordial, invitandolos a probar nuestros servicios. Asi mismo das respuestas cortas maximo de 50 palabras para que el cliente pueda leer las respuestas rapidamente. Siempre da una pregunta despues de explicar algo para invitar al cliente a tomar accion o seguir preguntando algo mas. Solo da una pregunta nada mas, no des mas de una pregunta';



        // Iniciar sesión para almacenar el historial (si aún no está iniciada)
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Crear o recuperar el historial desde la sesión
            if (!isset($_SESSION['chat_history'])) {
                $_SESSION['chat_history'] = []; // Inicializar historial si no existe
            }

            // Limitar el historial a los últimos 20 mensajes
            if (count($_SESSION['chat_history']) > 20) {
                $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
            }

            // Agregar el mensaje actual del usuario al historial
            $_SESSION['chat_history'][] = [
                'role' => 'user',
                'content' => $mensaje
            ];

        $instruction = [
            [
                "role" => "system", 
                "content" => $prompt
                
                ]
        ];

        // Combinar el historial con las instrucciones iniciales
        $messages = array_merge($instruction, $_SESSION['chat_history']);


        $openAIResponse = Http::withToken(config('services.openai.api_key'))
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 4096,
            'temperature' => 0.1,
        ]);



        if ($openAIResponse->successful()) {
            Log::info('Pasando por aquí.... en openAI response');
            $responseData = $openAIResponse->json();
            $generatedText = $responseData['choices'][0]['message']['content'];

            // Dividir el texto en partes: texto normal y bloques de código
            $codeBlocks = [];
            $generatedText = preg_replace_callback('/```(.*?)```/s', function ($matches) use (&$codeBlocks) {
                // Almacenar los bloques de código
                $codeBlocks[] = $matches[1]; // Guardamos solo el código
                return '{{CODE_' . (count($codeBlocks) - 1) . '}}'; // Reemplazamos por un marcador único
            }, $generatedText);

            // Procesar el texto normal (Markdown)
            $generatedText = nl2br($generatedText); // Convertir saltos de línea a <br>
            $generatedText = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $generatedText); // Negrita
            $generatedText = preg_replace('/^### (.+)/m', '<h6>$1</h6>', $generatedText); // Encabezados
            $generatedText = preg_replace('/^- (.+)/m', '<li>$1</li>', $generatedText); // Listas

            // Envolver listas en <ul>
            if (strpos($generatedText, '<li>') !== false) {
                $generatedText = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$0</ul>', $generatedText);
            }

            // Procesar imágenes
            $generatedText = preg_replace_callback(
                '/!\[(.*?)\]\((https?:\/\/[^\s]+\.(jpg|jpeg|png|gif|webp|svg))\)/i',
                function ($matches) {
                    return '<img src="' . htmlspecialchars($matches[2]) . '" alt="' . htmlspecialchars($matches[1]) . '" style="max-width:100%; height:auto;" />';
                },
                $generatedText
            );

            // Reemplazar los marcadores {{CODE_X}} por los bloques de código resaltados
            foreach ($codeBlocks as $index => $code) {
                $highlightedCode = '<pre><code class="php">' . htmlspecialchars($code) . '</code></pre>';
                // Reemplazar el marcador por el código resaltado
                $generatedText = preg_replace('/{{CODE_' . $index . '}}/', $highlightedCode, $generatedText);
            }





            // Finalmente, devolver el texto procesado

        } else {
            Log::info("openAIResponse No fue successful");
        }



                return $generatedText;
              //  return response()->json(['generatedText' => $generatedText]);

        }


private function generateDBResponse($assistant, $request)
{

    $prompt=$assistant->prompt;

    $prompt.="Eres un agente que ejecuta acciones como la capacidad de generar emails y enviarlos de acuerdo a las solicitudes que te hagan";
    return $this->generateOpenAIResponse_DB($prompt,$request->user_input);
}

private function generateOpenAIResponse_DB($prompt , $mensaje)
{


        Log::info("✅ Entrando a generateOpenAIResponse_DB()");

        $generatedText='';
        Log::info('En funcion generateOpenAIResponse programming');

        $prompt.='Eres un asistente que da informacion sobre las bases de datos y tambien grficas y estadisticas acerca de las consultas. Das las respuestas en formato markdown bien estilizadas. No inventas datos, todos los datos los obtienes del historial o contexto.';

        $prompt.='Indicar que si no encuentra cierta información, responda con una aclaración (“No encuentro esos datos en el historial”)';

        $prompt.='Cuando te pidan datos de ventas o graficos de las ventas, devuelve un JSON con la configuración de Chart.js (labels, datasets, etc.) bajo la clave chart_data. Devuélvelo en un bloque ```json. Además, explica el resultado en Markdown, pero no incluyas <script> ni <canvas>, tampoco lo pongas dentro de <pre><code class="language-json">';


        $instruction = [
            [
                "role" => "system", 
                "content" => $prompt
                ]
        ];
    //    Log::info('Cookie de sesión: ' . json_encode($_SESSION));
        // Iniciar sesión para almacenar el historial (si aún no está iniciada)
        if (session_status() == PHP_SESSION_NONE) {
        session_start();
        }
        // Crear o recuperar el historial desde la sesión
        if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = []; // Inicializar historial si no existe
        }

        // Limitar el historial a los últimos 20 mensajes
        if (count($_SESSION['chat_history']) > 20) {
            $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
        }

        // Agregar el mensaje actual del usuario al historial
        $_SESSION['chat_history'][] = [
            'role' => 'user',
            'content' => $mensaje
        ];

        // Verificar si el mensaje contiene palabras clave para enviar email
        // Limpiar y normalizar mensaje
        // Normalizar mensaje antes de analizarlo

        $resultado= $this->detectarIntencion($mensaje);

        $intencion = $resultado['intencion'];
        $destinatario = $resultado['destinatario'];


         switch ($intencion) {
            // case 'ventas':
            //     return $this->obtenerVentas();
            case 'ventas':
                return $this->obtenerVentasfaiss($mensaje);
            case 'grafico':
                return $this->obtenerGrafico($mensaje);
            case 'compras':
                return $this->obtenerCompras();
            case 'stock':
                return $this->obtenerStock();
            case 'clientes':
                return $this->obtenerClientes();
            case 'movimientos':
                return $this->obtenerMovimientos();
            case 'crear_email':

                Session::put('esperando_confirmacion', true);
                Session::put('destinatario', $destinatario);
                Session::save();

                //$_SESSION['esperando_confirmacion'] = true;  // Guardar que estamos esperando confirmación
                //$_SESSION['destinatario']=$destinatario;
               

                //Log::info("✅ la instruction es ".json_encode($instruction));
                 return $this->crear_email($instruction,$mensaje, $destinatario);  
            case 'modificar_email':
                $esperando = Session::get('esperando_confirmacion', false);
                $destinatario = Session::get('destinatario', '');
                $email_contenido = Session::get('email_contenido', '');

                if ($esperando ?? false) {  
                Log::info("Modificando Email con IA");
                // Llamar a OpenAI para corregir el email actual
                $nuevoContenido = $this->crear_email($instruction, "Corrige este email y hazlo más claro: " . $email_contenido, $destinatario);
                if (!$nuevoContenido) {
                return "❌ Error: No se pudo corregir el email.";
                }
                // Actualizar el contenido corregido en la sesión
                $_SESSION['email_contenido'] = $nuevoContenido;
                return $nuevoContenido . "<br><br><strong>¿Desea enviar este email corregido?</strong>";
            }
            case 'enviar_email':
                //Log::info('Esperando confirmacion session en enviar email::::::'.$_SESSION['esperando_confirmacion']);
                $esperando = Session::get('esperando_confirmacion', false);
                $destinatario = Session::get('destinatario', '');
                $email_contenido = Session::get('email_contenido', '');

                Log::info('Esperando en session es **********:'. $esperando );
                Log::info('El destinatario en session es**********:'. $destinatario );
                Log::info('Email contenido en session es**********:'. $email_contenido );

                //Log::info('destinatario:::::::::::::'. $_SESSION['destinatario']);
                //Log::info('email_contenido:::::::::::::'.$_SESSION['email_contenido']);
                if ($esperando ?? false) {  // Verificar si se está esperando confirmación
                unset($esperando);  // Resetear la variable de sesión

                 return $this->enviarEmail($destinatario,$email_contenido);  
                }
            case 'saludo':
                return 'Hola! La paz sea contigo! Bienvenido a SmartChatix';
            default:
                return $this->interpretarconIA($instruction,$mensaje);  
            // return response()->json(['respuesta' => "No entendí tu pregunta. ¿Puedes ser más específico?"]);
        }


                        
        
}


private function obtenerVentasfaiss($mensaje){
 // URL de Flask (ajusta según dónde lo estés ejecutando)
    $url = "http://127.0.0.1:5002/analizar"; // 

    // Enviar el mensaje a Flask
    $response = Http::post($url, ["mensaje" => $mensaje]);

    // Verificar si la respuesta fue exitosa
    if ($response->failed()) {
        return ["error" => "No se pudo obtener los datos de FAISS"];
    }

    $datos = $response->json();

    // Si Flask no encontró datos, devolver error
    if (isset($datos["error"])) {
        return ["error" => $datos["error"]];
    }

//    Log::info('respuesta es ', $datos);

    // Si la intención no es 'grafico', devolver solo la respuesta de Flask
    if ($datos["intencion"] !== "ventas") {
        return ["mensaje" => $datos["resultados"]];
    }

//    Log::info("Datos Resultados:::". $datos["resultados"]);





$promptia="Dar un resumen del siguiente resultado: a la pregunta: .$mensaje ";
$promptia.=$datos["resultados"];

Log::info("Proptia es :::::::::" .$promptia);

preg_match_all('/Total: \$(\d+\.\d+)/', $datos["resultados"], $matches);
$total_ventas = array_sum(array_map('floatval', $matches[1]));




                        $openAIResponse = Http::withToken(config('services.openai.api_key'))
                        ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => 'Eres un analista de ventas que resume datos para gerentes de negocios, haz el calculo de totales y porcentajes paso a paso para evitar errores y verifica que tu resultado sea correcto antes de responder.Analiza y da conclusiones de los resultados para su mejor entendimiento.l total real de ventas, que ya ha sido calculado, es $'.$total_ventas.'. No recalcules el total, solo analiza la información'],
                            ['role' => 'user', 'content' => $promptia]
                        ],                        
                        'max_tokens' => 4096,
                        'temperature' => 0.1,
                        ]);

                        // Manejar la respuesta de OpenAI
                        if ($openAIResponse->successful()) {
                            Log::info("respuestaopenai exitosa");

                            $responseContent = $openAIResponse->json();
                        }else{
                            Log::info("Hubo un error a openai");
                        }


                        return [
                        "assistant_response" => $responseContent['choices'][0]['message']['content'],
                        "chart_data" => null
                    ];

}

private function obtenerGrafico($mensaje) {
    // URL de Flask (ajusta según dónde lo estés ejecutando)
    $url = "http://127.0.0.1:5002/analizar"; // 

    // Enviar el mensaje a Flask
    $response = Http::post($url, ["mensaje" => $mensaje]);

    // Verificar si la respuesta fue exitosa
    if ($response->failed()) {
        return ["error" => "No se pudo obtener los datos de FAISS"];
    }

    $datos = $response->json();

    // Si Flask no encontró datos, devolver error
    if (isset($datos["error"])) {
        return ["error" => $datos["error"]];
    }

    // Si la intención no es 'grafico', devolver solo la respuesta de Flask
    if ($datos["intencion"] !== "grafico") {
        return ["mensaje" => $datos["respuesta"]];
    }

    // 🔹 Formatear datos para Chart.js si FAISS devolvió datos
    $chartData = [
        "labels" => array_column($datos["resultados"], "fecha_venta"),
        "datasets" => [
            [
                "label" => "Ventas",
                "data" => array_column($datos["resultados"], "total"),
                "borderColor" => "rgba(75, 192, 192, 1)",
                "backgroundColor" => "rgba(75, 192, 192, 0.2)",
            ]
        ]
    ];

    return [
        "assistant_response" => "Aquí tienes el gráfico de ventas:",
        "chart_data" => $chartData
    ];
}

private function crear_email($instruction, $mensaje, $destinatario){
                Log::info("Creando Email con IA");
                
                        $messages = array_merge($instruction, $_SESSION['chat_history']);
                        //Log::info("messages ::::::::::::::::::::::::" .json_encode($messages) );

                        // Llamar a la API de OpenAI
                        $openAIResponse = Http::withToken(config('services.openai.api_key'))
                        ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => $messages,
                        'max_tokens' => 4096,
                        'temperature' => 0.1,
                        ]);

                        // Manejar la respuesta de OpenAI
                        if ($openAIResponse->successful()) {
                            $responseContent = $openAIResponse->json();

                            // Agregar la respuesta al historial
                            $_SESSION['chat_history'][] = [
                                'role' => 'assistant',
                                'content' => $responseContent['choices'][0]['message']['content']
                            ];

                            $texto = $responseContent['choices'][0]['message']['content'];

                            



                            // Quita desde 'saludos' en adelante (ignorando mayúsculas y saltos de línea)
                            $textoSinFirma = preg_replace('/\n?saludos.*/is', '', $texto);

                            // Limpia espacios y saltos de línea sobrantes
                            $textoSinFirma = trim($textoSinFirma);

                            // Finalmente, muestra/devuelve el texto sin la despedida
                            

                            // Convertir la respuesta en HTML
                            $parsedown = new Parsedown();
                            $htmlContent = $parsedown->text($textoSinFirma);

                            // Guardar en sesión que estamos esperando confirmación del usuario
                            // $_SESSION['esperando_confirmacion'] = true;
                            Session::put('esperando_confirmacion', true);
                            //$_SESSION['email_contenido'] = $textoSinFirma;
                            Session::put('email_contenido', $textoSinFirma);

                            $nombreCliente = $destinatario;
                            $cliente = DB::connection('mysql2')
                                ->table('clientes')
                                ->where('nombre', 'LIKE', "%$nombreCliente%")
                                ->first();
                                Log::info("✅ El cliente es :::: ".$cliente->nombre);
                            //$_SESSION['email_destinatario'] = $cliente->email;
                            Session::put('email_destinatario', $cliente->email);
                            Session::save();

                            // Agregar la pregunta al usuario
                            $htmlContent .= "<br><br><strong>¿Desea enviar este email a ".$cliente->nombre." (" .$cliente->email.") ?</strong>";
                            return $htmlContent;
                        }else{
                            Log::info("📩 Destinatario detectado: Algo salio mal con la consulta ");

                            return "Algo salio mal con la consulta";
                        }

}

private function enviarEmail($destinatario, $contenido){
                Log::info("📩 Destinatario detectado: " . json_encode($destinatario));
                Log::info("✉️ Contenido del email: " . json_encode($contenido));
                Log::info("✅ Se detectó intención de email, llamando a procesarEnvioEmail()");


                // Llama a la función que se encarga de enviar el email usando los datos extraídos
                // Extraer los valores dinámicamente por nombre de grupo
                $nombreCliente = $destinatario;




                // 1. Elimina todo lo que va desde el inicio hasta (e incluyendo) "**Asunto:**"
                $textoDepurado = preg_replace('/^.*?\*\*Asunto:\*\*/is', '', $contenido);
                // Este regex dice:
                //  - ^    => desde el inicio de la cadena
                //  - .*?  => cualquier cosa (de forma no codiciosa)
                //  - \*\*Asunto:\*\* => busca literalmente "**Asunto:**"
                //  - /is  => i = case-insensitive, s = que '.' incluya saltos de línea

                // 2. A continuación, si también deseas quitar la palabra “Asunto:” que quedó, puedes hacerlo:
                $textoDepurado = preg_replace('/^asunto:\s*/i', '', ltrim($textoDepurado));

                // 3. Finalmente, limpia espacios
                $textoDepurado = trim($textoDepurado);


                $textoSinSaludo = preg_replace('/hola\s+[a-záéíóúüñ]+\,?/i', '', $textoDepurado);
                $textoSinSaludo = trim($textoSinSaludo);




                $mensajeEmail = $textoSinSaludo;



                Log::info("📩 Cliente: " . $nombreCliente . " | Mensaje: " . $mensajeEmail);

                $cliente = DB::connection('mysql2')
                    ->table('clientes')
                    ->where('nombre', 'LIKE', "%$nombreCliente%")
                    ->first();
                    Log::info("✅ El cliente es :::: ".$cliente->nombre);


                // Ahora puedes procesar el envío del email
                return $this->enviarEmailCliente($cliente->id, $mensajeEmail);
}

private function interpretarconIA($instruction,$mensaje){
                    Log::info("Interpretando con IA");


                         $messages = array_merge($instruction, $_SESSION['chat_history']);

                        Log::info("messages ::::::::::::::::::::::::" .json_encode($messages) );


                            Log::info('linea 1299');
                        // Llamar a la API de OpenAI
                        $openAIResponse = Http::withToken(config('services.openai.api_key'))
                        ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => $messages,
                        'max_tokens' => 4096,
                        'temperature' => 0.1,
                        ]);

                        // Manejar la respuesta de OpenAI
                        if ($openAIResponse->successful()) {
                            Log::info("respuestaopenai exitosa");

                            $responseContent = $openAIResponse->json();

                             Log::info("Linea 1323");

                            //Log::info("Respuesta de OpenAI:", $responseContent);

                            // Agregar la respuesta al historial
                            $_SESSION['chat_history'][] = [
                                'role' => 'assistant',
                                'content' => $responseContent['choices'][0]['message']['content']
                            ];
                        
                             Log::info("Linea 1333");

                            $parsedown = new Parsedown();
                            Log::info("Linea 1336");

                            $htmlContent = $parsedown->text($responseContent['choices'][0]['message']['content']);
                              Log::info("Linea 1339");



                            $chartData = null;
                                if (preg_match('/```json\s*(.*?)\s*```/s', $htmlContent, $matches)) {
                                    try {
                                        $chartData = json_decode($matches[1], true); // Convertir JSON a array PHP
                                        Log::info("Chart Data detectado: " . json_encode($chartData));
                                    } catch (Exception $e) {
                                        Log::error("Error al procesar JSON: " . $e->getMessage());
                                    }
                                } else {
                                    Log::info("No se detectó JSON en la respuesta de OpenAI.");
                                }


                            return [
                            'assistant_response' => $htmlContent,  // el texto
                            'chart_data' => null,                  // si no lo usas
                            // 'otros_campos' => ...
                            ];
                            Log::info("Linea 1345");


                            }else{
                                $statusCode = $openAIResponse->status();
                                Log::error("OpenAI call failed with status: {$statusCode}");

                                $body = $openAIResponse->body();
                                Log::error("OpenAI error body: {$body}");

                            return "Yo quiero mi primer millon";
                            }
                }

private function saludar(){
    return " 🤗 Hola ¡Bienvenido a SmartChatix!  ";
}

private function obtenerClientes(){
          $clients = DB::connection('mysql2')
                        ->table('clientes')
                        ->get();
                        // Crear un mensaje con las estadísticas de ventas y datos del cliente
                        $clientsInfo = "📊 *Resumen de los Clientes* 📊\n\n";
                        $clientsInfo .= "| id          | nombre                  | telefono       | email      | Direccion | Notas       | \n";
                        $clientsInfo .= "|------------------|------------------------|----------------|---------------|--------|-------------|\n";

                        foreach ($clients as $client) {
                            $clientsInfo .= "| {$client->id} | {$client->nombre} | {$client->telefono} | {$client->email} | {$client->direccion} | {$client->notas} | \n";
                        }
                        // Agregar información de ventas al historial
                        $_SESSION['chat_history'][] = [
                            'role' => 'assistant',
                            'content' => $clientsInfo
                        ];

                        return  $clientsInfo ;// Markdown para chat
                                                    

}

private function obtenerVentas(){
                    Log::info("✅ Se detectó intención de consulta de ventas, llamando a procesarConsultaVentas()");

                    // Consultar la base de datos secundaria
                    $ventas = DB::connection('mysql2')
                        ->table('ventas')
                        ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id') // Unimos las tablas
                        ->select(
                            'clientes.nombre as cliente_nombre',
                            'clientes.email',
                            'clientes.telefono',
                            'ventas.producto',
                            'ventas.precio_unitario',
                            'ventas.fecha_venta',
                            'ventas.cantidad',
                            'ventas.total'
                        )
                        ->get();

                        // Crear un mensaje con las estadísticas de ventas y datos del cliente
                        $salesInfo = "📊 *Resumen de Ventas* 📊\n\n";
                        $salesInfo .= "| Cliente          | Email                  | Teléfono       | Producto      | Precio | Fecha       | Cantidad | Total |\n";
                        $salesInfo .= "|------------------|------------------------|----------------|---------------|--------|-------------|----------|-------|\n";

                        foreach ($ventas as $venta) {
                            $salesInfo .= "| {$venta->cliente_nombre} | {$venta->email} | {$venta->telefono} | {$venta->producto} | {$venta->precio_unitario} | {$venta->fecha_venta} | {$venta->cantidad} | {$venta->total} |\n";
                        }
                        // Agregar información de ventas al historial
                        $_SESSION['chat_history'][] = [
                            'role' => 'assistant',
                            'content' => $salesInfo
                        ];

                        return  $salesInfo ;// Markdown para chat
                            
}

private function detectarIntencion($mensaje){
   $response = Http::post('http://127.0.0.1:5002/analizar', ['mensaje' => $mensaje]);
        $responseData = $response->json();

        $intencion     = $responseData['intencion'] ?? null;
        $destinatario  = $responseData['destinatario'] ?? null;
        
    // Devolver un array con la intención y el destinatario
            return [
                'intencion' => $intencion,
                'destinatario' => $destinatario
            ];
}

private function generarGraficoVentas($prompt, $mensaje){



}


// Generar respuesta para servicio
private function generateDeveloperResponse($assistant, $request)
{
   // Log::info('assistant:->'.$assistant);

    $prompt=$assistant->prompt;

    return $this->generateOpenAIResponse_programming($prompt,$request->user_input);
}

private function generateOpenAIResponse_programming($prompt , $mensaje)
{
        $generatedText='';
        Log::info('En funcion generateOpenAIResponse programming');

        $prompt.='Eres un experto desarrollador senior de software que ayuda a desarrollar programas y apps de manera rapida y sencilla, a la vez que los programas que desarrollas son sumamente exitosos';



        // Iniciar sesión para almacenar el historial (si aún no está iniciada)
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Crear o recuperar el historial desde la sesión
            if (!isset($_SESSION['chat_history'])) {
                $_SESSION['chat_history'] = []; // Inicializar historial si no existe
            }

            // Limitar el historial a los últimos 20 mensajes
            if (count($_SESSION['chat_history']) > 20) {
                $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
            }

            // Agregar el mensaje actual del usuario al historial
            $_SESSION['chat_history'][] = [
                'role' => 'user',
                'content' => $mensaje
            ];

        $instruction = [
            [
                "role" => "system", 
                "content" => $prompt
                
                ]
        ];

        // Combinar el historial con las instrucciones iniciales
        $messages = array_merge($instruction, $_SESSION['chat_history']);

        // Agregar el mensaje del usuario al prompt
        // $messages = array_merge($instruction, [
        //     [
        //         'role' => 'user', 
        //         'content' => $mensaje
        //     ]
        // ]);

        Log::info("elmensaje es:",$messages);


        $openAIResponse = Http::withToken(config('services.openai.api_key'))
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 4096,
            'temperature' => 0.1,
        ]);

         Log::info('Tlinea 1134 ' );


        if ($openAIResponse->successful()) {
            Log::info('Pasando por aquí.... en openAI response');
            $responseData = $openAIResponse->json();
            $generatedText = $responseData['choices'][0]['message']['content'];

            // Dividir el texto en partes: texto normal y bloques de código
            $codeBlocks = [];
            $generatedText = preg_replace_callback('/```(.*?)```/s', function ($matches) use (&$codeBlocks) {
                // Almacenar los bloques de código
                $codeBlocks[] = $matches[1]; // Guardamos solo el código
                return '{{CODE_' . (count($codeBlocks) - 1) . '}}'; // Reemplazamos por un marcador único
            }, $generatedText);

            // Procesar el texto normal (Markdown)
            $generatedText = nl2br($generatedText); // Convertir saltos de línea a <br>
            $generatedText = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $generatedText); // Negrita
            $generatedText = preg_replace('/^### (.+)/m', '<h6>$1</h6>', $generatedText); // Encabezados
            $generatedText = preg_replace('/^- (.+)/m', '<li>$1</li>', $generatedText); // Listas

            // Envolver listas en <ul>
            if (strpos($generatedText, '<li>') !== false) {
                $generatedText = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$0</ul>', $generatedText);
            }

            // Procesar imágenes
            $generatedText = preg_replace_callback(
                '/!\[(.*?)\]\((https?:\/\/[^\s]+\.(jpg|jpeg|png|gif|webp|svg))\)/i',
                function ($matches) {
                    return '<img src="' . htmlspecialchars($matches[2]) . '" alt="' . htmlspecialchars($matches[1]) . '" style="max-width:100%; height:auto;" />';
                },
                $generatedText
            );

            // Reemplazar los marcadores {{CODE_X}} por los bloques de código resaltados
            foreach ($codeBlocks as $index => $code) {
                $highlightedCode = '<pre><code class="php">' . htmlspecialchars($code) . '</code></pre>';
                // Reemplazar el marcador por el código resaltado
                $generatedText = preg_replace('/{{CODE_' . $index . '}}/', $highlightedCode, $generatedText);
            }


              Log::info('Texto generado Limea 1178__________________');
              Log::info('Texto generado Limea 1179__________________' .$generatedText);


            // Finalmente, devolver el texto procesado

        } else {
            Log::info("openAIResponse No fue successful");
        }

        // Log para verificar el texto generado
        Log::info('Texto generado_____________________: ' . $generatedText);

       // Log::info('Texto generado-M_M_M_:', ['generatedText' => $generatedText]);

                return $generatedText;
              //  return response()->json(['generatedText' => $generatedText]);

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


    // Aquí interpreto si la respuesta contiene un interés por hablar con un humano
    $intent = strtolower($user_input); // Extraer el intent del mensaje original

    Log::info('Intent (original): ' . $intent);

    // Utilizar expresiones regulares o técnicas de NLP más avanzadas para detectar la intención
    if (preg_match('/(hablar con un humano| numero |numero de contacto | contacto | contactar | numero de whatsapp| necesito hablar con un asesor |  quiero hablar con un humano | quiero hablar con un humano|quiero hablar con una persona|necesito un agente humano|quiero hablar con alguien)/i', $intent)) {
        return 'contactar_humano';
    }

    return 'sin_intencion_especifica';
}


public function uploadDocumentTraining(Request $request, $assistantId)
{
    // Validar y guardar el archivo (como ya lo tienes configurado)
    $request->validate([
        'file' => 'required|file|max:10240',
    ]);

    $file = $request->file('file');
    $extension = $file->getClientOriginalExtension();

    if ($extension !== 'jsonl') {
        return back()->withErrors(['file' => 'El archivo debe ser de tipo .jsonl.']);
    }

    $assistant = Assistant::findOrFail($assistantId);
    $path = $file->store('documents', 'public');

    $documentTraining = new DocumentTraining();
    $documentTraining->filename = $file->getClientOriginalName();
    $documentTraining->path = $path;
    $documentTraining->assistant_id = $assistant->id;
    $documentTraining->save();

    Log::info('Documento de entrenamiento creado:', ['documentTraining' => $documentTraining]);


    // Despachar el Job para realizar el fine-tuning

    FineTuneAssistantJob::dispatch($assistant->id, $documentTraining->id);

    //FineTuneAssistantJob::dispatch($assistant, $documentTraining);
    Log::info('Linea 558 ______');

    return back()->with('success', 'Documento de entrenamiento subido y proceso de fine-tuning iniciado.');
}


public function monitorFineTuning($id)
{
    Log::info('En método FineTuning con id: ' . $id);

    $assistant = Assistant::find($id);

    if (!$assistant || !$assistant->fine_tuning_job_id) {
        return response()->json(['error' => 'No hay un trabajo de fine-tuning asociado.'], 404);
    }

    Log::info('El assistant es: ' . $assistant->name);

    $apiKey = config('services.openai.api_key');

    // Obtener el estado del trabajo de fine-tuning
    $statusResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
    ])->get("https://api.openai.com/v1/fine_tuning/jobs/{$assistant->fine_tuning_job_id}");

    if (!$statusResponse->successful()) {
        return response()->json(['error' => 'Error al consultar el estado del fine-tuning.'], 500);
    }

    // Obtener los eventos del trabajo de fine-tuning
    $eventsResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
    ])->get("https://api.openai.com/v1/fine_tuning/jobs/{$assistant->fine_tuning_job_id}/events");

    if ($eventsResponse->successful()) {
        Log::info('Eventos del fine-tuning obtenidos.');

        // Consultar todos los trabajos de fine-tuning para encontrar el modelo
        $allJobsResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get("https://api.openai.com/v1/fine_tuning/jobs");

        if ($allJobsResponse->successful()) {
            $allJobs = $allJobsResponse->json()['data'];

            // Buscar el trabajo correspondiente al asistente
            $job = collect($allJobs)->firstWhere('id', $assistant->fine_tuning_job_id);

            if ($job && isset($job['fine_tuned_model'])) {
                $modelName = $job['fine_tuned_model'];

                // Guardar el modelo fine-tuned en la base de datos del asistente
                $assistant->fine_tuned_model = $modelName;
                $assistant->save();

                Log::info("Modelo fine-tuned guardado: $modelName");
            } else {
                Log::warning('No se encontró el modelo fine-tuned para este asistente.');
                $modelName = null;
            }
        } else {
            Log::error('Error al consultar todos los trabajos de fine-tuning.');
            return response()->json(['error' => 'Error al consultar trabajos de fine-tuning.'], 500);
        }

        // Retornar tanto el estado como los eventos
        return response()->json([
            'status' => $statusResponse->json(),
            'events' => $eventsResponse->json()['data'],
            'model_name' => $modelName // Nombre del modelo fine-tuneado
        ]);
    } else {
        Log::error('Error al obtener los eventos del fine-tuning.');
        return response()->json(['error' => 'Error al obtener los eventos del fine-tuning.'], 500);
    }
}


public function enviarEmailCliente($cliente_id, $mensaje)
{

    Log::info('📩 En función enviarEmailCliente() client_id...' .$cliente_id);

    // Buscar cliente en la base de datos secundaria (mysql2)
    $cliente = DB::connection('mysql2')
        ->table('clientes')
        ->where('id', $cliente_id)
        ->first();

    if (!$cliente || empty($cliente->email)) {
        Log::info("⚠ Cliente no encontrado o sin email.");
        return "⚠ No se encontró el cliente o no tiene email registrado.";
    }

    Log::info("✅ Cliente encontrado: " . json_encode($cliente));
    Log::info("📨 Enviando email a: " . $cliente->email);

    // Enviar email usando Laravel Mail
    Mail::to($cliente->email)->send(new EnviarEmailCliente($cliente, $mensaje));
    Log::info("el contenido enviado es :::".$mensaje);

    return "✅ Email enviado a {$cliente->nombre} ({$cliente->email}).";
}



}


// 25-01-07 13:59:59] production.INFO: Fine-tuning iniciado con éxito. Job ID: ftjob-nJa8H0uK13i9eaUn7u1b5kAm  
//Ver en postman el idjob estado:
// https://api.openai.com/v1/fine_tuning/jobs/ftjob-6wMUDxtpEtpllOOLcMCI1Ek3/events  // smartchatix
// https://api.openai.com/v1/fine_tuning/jobs/ftjob-7ghbHGPZh9wbIuwyYyzXJwSc/events // trubotec



//Ve los modelos generados en fine tunning
//https://api.openai.com/v1/fine_tuning/jobs con headers del bearer


