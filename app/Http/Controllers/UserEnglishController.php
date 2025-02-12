<?php

namespace App\Http\Controllers;

use App\Models\UserEnglish;
use Illuminate\Http\Request;

class UserEnglishController extends Controller
{
    // Obtener información de un usuario
    public function show($id)
    {

    //         return response()->json([
    //     'message' => "User English con ID $id aún no existe, pero la API funciona."
    // ]);
        return response()->json(UserEnglish::where('user_id', $id)->first());
    }

    // Crear o actualizar datos del usuario
    public function update(Request $request, $id)
    {
        $userEnglish = UserEnglish::updateOrCreate(
            ['user_id' => $id],
            $request->only(['level', 'progress', 'history'])
        );

        return response()->json($userEnglish);
    }
}
