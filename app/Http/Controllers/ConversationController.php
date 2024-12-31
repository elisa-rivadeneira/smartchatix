<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConversationController extends Controller
{
    // Mostrar todas las conversaciones
// App\Http\Controllers\ConversationController.php
    public function index()
    {
        $user = auth()->user(); // Obtén al usuario autenticado

        // Obtener todos los asistentes del usuario
        $assistants = $user->assistants; // Asegúrate de que exista la relación 'assistants' en el modelo User

        // Filtrar conversaciones por cada asistente
       $conversations = Conversation::whereIn('assistant_id', $assistants->pluck('id'))->get();

        $assistantNames = $assistants->pluck('name', 'id');

        // Agregar el nombre del asistente a cada conversación
        $conversations = $conversations->map(function($conversation) use ($assistantNames) {
            $conversation->assistant_name = $assistantNames[$conversation->assistant_id] ?? 'Nombre no disponible'; // Asignar el nombre del asistente
                $conversation->created_at = $conversation->created_at->format('Y-m-d H:i'); // Formatear la fecha de creación para que se vea en la vista

            return $conversation;
        });

        //dd($conversations);
        return view('conversations.index', compact('conversations'));
    }




    public function show($id)
{
    Log::info('$id de conversacion:: ' . $id); // Usamos toJson() para ver los datos de los mensajes

    // Obtener la conversación junto con los mensajes y el nombre del asistente
    $conversation = Conversation::with('messages')  // Cargar los mensajes relacionados
        ->join('assistants', 'conversations.assistant_id', '=', 'assistants.id')
        ->select('conversations.*', 'assistants.name as assistant_name') // Selecciona todos los campos de conversation y el nombre del asistente
        ->where('conversations.id', $id)  // Buscar por el ID de la conversación
        ->firstOrFail();  // Obtener la conversación o fallar si no existe

    $whatsappNumber = $conversation->assistant->whatsapp_number;

    $whatsappLink = "https://wa.me/{$whatsappNumber}?text=Hola,%20estoy%20interesado%20en%20hablar%20con%20un%20asesor";


    // Obtener el nombre del asistente a través de la relación con 'assistants'
    $assistantName = $conversation->assistant->name ?? 'Nombre no disponible'; // Usa la relación para obtener el nombre del asistente

    // Obtener los mensajes relacionados (esto ya se hizo con 'with')
    $messages = $conversation->messages;

    // Log para ver los mensajes
    Log::info('Los mensajes son:: ' . $messages->toJson()); // Usamos toJson() para ver los datos de los mensajes

    // Retornar la vista con la conversación, mensajes y el nombre del asistente
    return view('conversations.show', compact('conversation', 'messages', 'assistantName', 'whatsappLink'));
}
    // Crear una nueva conversación
    public function create()
    {
        return view('conversations.create');
    }

    // Almacenar una nueva conversación en la base de datos
    public function store(Request $request)
    {
        // Validar los datos del request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id', // Asumiendo que tienes usuarios en tu base de datos
        ]);

        // Crear una nueva conversación
        $conversation = new Conversation();
        $conversation->title = $validated['title'];
        $conversation->user_id = $validated['user_id'];  // Puedes usar esta relación si tienes un campo 'user_id' en Conversation
        $conversation->save();

        return redirect()->route('conversations.show', $conversation->id);  // Redirigir al detalle de la conversación
    }

    // Crear un mensaje dentro de una conversación
    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Validar el contenido del mensaje
        $validated = $request->validate([
            'message' => 'required|string',
            'sender' => 'required|in:user,assistant',  // El mensaje puede ser de un usuario o asistente
        ]);

        // Crear el mensaje
        $message = new Message();
        $message->conversation_id = $conversation->id;
        $message->sender = $validated['sender'];
        $message->message = $validated['message'];
        $message->save();

        // Redirigir de vuelta al detalle de la conversación con el nuevo mensaje
        return redirect()->route('conversations.show', $conversation->id);
    }


    public function destroy($id)
    {
        // Buscar y eliminar el asistente
        $conversation = Conversation::findOrFail($id);
        $conversation->delete();
    
        // Redirigir a la vista index con un mensaje de éxito
        return redirect()->route('conversations.index')->with('success', 'Conversación eliminada con éxito.');
                                  
    }
}
