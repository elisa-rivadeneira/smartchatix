<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AIModel;

class AIModelController extends Controller
{
    // Listar modelos
    public function index()
    {
        $models = AIModel::all();
        return view('models.index', compact('models'));
    }

    // Mostrar formulario para crear un modelo
    public function create()
    {
        return view('models.create');
    }

    // Guardar un nuevo modelo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:255|unique:a_i_models,identifier',
            'description' => 'nullable|string',
        ]);

        AIModel::create($request->all());

        return redirect()->route('a-i-models.index')->with('success', 'Modelo creado exitosamente.');
    }

    // Mostrar un modelo en detalle
    public function show($id)
    {
        $model = AIModel::findOrFail($id);
        return view('models.show', compact('model'));
    }

    // Mostrar formulario para editar un modelo
    public function edit($id)
    {
        $model = AIModel::findOrFail($id);
        return view('models.edit', compact('model'));
    }

    // Actualizar un modelo
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => "required|string|max:255|unique:a_i_models,identifier,{$id}",
            'description' => 'nullable|string',
        ]);

        $model = AIModel::findOrFail($id);
        $model->update($request->all());

        return redirect()->route('a-i-models.index')->with('success', 'Modelo actualizado exitosamente.');
    }

    // Eliminar un modelo
    public function destroy($id)
    {
        $model = AIModel::findOrFail($id);
        $model->delete();

        return redirect()->route('a-i-models.index')->with('success', 'Modelo eliminado exitosamente.');
    }
}
