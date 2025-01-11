<?php

namespace App\Http\Controllers;
use App\Models\Assistant; // 
use App\Models\User; // 
use App\Models\Conversation; // 
use Illuminate\Support\Facades\DB; // Asegúrate de importar DB aquí
use App\Models\Message; // 



use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Variables que necesitas
        $asistentesCount = Assistant::where('user_id', auth()->id())->count(); // Total de asistentes del usuario actual
        $conversacionesCount = DB::table('conversations')
        ->join('assistants', 'conversations.assistant_id', '=', 'assistants.id') // Relación entre conversations y assistants
        ->where('assistants.user_id', auth()->id()) // Filtrar por el usuario autenticado
        ->whereMonth('conversations.created_at', now()->month) // Filtrar por el mes actual
        ->count();
        $interaccionesTotales = DB::table('messages')
        ->join('conversations', 'messages.conversation_id', '=', 'conversations.id') // Relación entre messages y conversations
        ->join('assistants', 'conversations.assistant_id', '=', 'assistants.id') // Relación entre conversations y assistants
        ->where('assistants.user_id', auth()->id()) // Filtrar por el usuario autenticado
        ->count(); // Contar la cantidad total de mensajes
        
        // Datos para el gráfico de actividad mensual
        $actividadLabels = [];
        $actividadData = [];
        $interaccionesPorDia = Message::selectRaw('DATE(messages.created_at) as fecha, COUNT(*) as total')
        ->join('conversations', 'messages.conversation_id', '=', 'conversations.id') // Relación entre messages y conversations
        ->join('assistants', 'conversations.assistant_id', '=', 'assistants.id') // Relación entre conversations y assistants
        ->where('assistants.user_id', auth()->id())
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        foreach ($interaccionesPorDia as $item) {
            $actividadLabels[] = $item->fecha;
            $actividadData[] = $item->total;
        }

        // Retorna la vista con las variables necesarias
        return view('home', [
            'asistentesCount' => $asistentesCount,
            'conversacionesCount' => $conversacionesCount,
            'interaccionesTotales' => $interaccionesTotales,
            'actividadLabels' => $actividadLabels,
            'actividadData' => $actividadData,
        ]);
    }
}
