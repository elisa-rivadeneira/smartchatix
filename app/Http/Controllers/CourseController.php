<?php
namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;



class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {

        Log::info($request->all());
        // Valida la entrada
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher' => 'required|string|max:255',
            'description_teacher' => 'nullable|string',
            'modalidad' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'duration' => 'nullable|integer', // Cambiado a integer
            'category' => 'nullable|string',
            'price' => 'nullable|string',

        ]);
    
        // Crea una nueva instancia de Course
        $course = new Course();
    
        // Si se ha subido una imagen
        if ($request->hasFile('imagen')) {
            // Obtiene la extensión del archivo
            $extension = $request->file('imagen')->getClientOriginalExtension();
    
            // Renombra el archivo para evitar conflictos
            $fileName = time() . '.' . $extension;
    
            // Mueve el archivo a la carpeta 'courses' dentro de 'storage/app/public'
            $filePath = $request->file('imagen')->storeAs('courses', $fileName, 'public');
    
            // Guarda la ruta del archivo en la base de datos
            $course->imagen = $filePath;
        }
    
        // Asigna los demás campos validados al curso
        $course->title = $validated['title'];
        $course->description = $validated['description'];
        $course->teacher = $validated['teacher'];
        $course->description_teacher = $validated['description_teacher'];
        $course->modalidad = $validated['modalidad'];
    
        // Guarda el curso en la base de datos
        $course->save();
    
        // Redirige con un mensaje de éxito
        return redirect()->route('courses.index')->with('success', 'Curso creado con éxito.');
    }
    
    

    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        Log::info('Course: ', $course->toArray()); // Verifica curso como array
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'teacher' => 'required|string|max:255',
                'description_teacher' => 'nullable|string',
                'modalidad' => 'nullable|string',
                'duration' => 'nullable|integer', // Cambiado a integer
                'category' => 'nullable|string',
                'price' => 'nullable|string',
            ]);
            Log::info('Validation successful', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ', $e->errors());
            return response()->json(['errors' => $e->errors()], 422); // O cualquier respuesta apropiada
        }
    
        // Si se sube una nueva imagen, procesa y guarda la ruta en la base de datos
        if ($request->hasFile('imagen')) {
            // Elimina la imagen anterior si existe
            if ($course->imagen) {
                Storage::delete($course->imagen);
            }
    
            // Obtiene la extensión del archivo original
            $extension = $request->file('imagen')->getClientOriginalExtension();
    
            // Renombra el archivo antes de guardarlo
            $fileName = time() . '.' . $extension;
            Log::info('$fileName: ' . $fileName);
    
            // Mueve el archivo a la carpeta 'courses' en 'storage/app/public'
            $filePath = $request->file('imagen')->storeAs('courses', $fileName, 'public');
    
            // Guarda la ruta del archivo en la base de datos
            try {
                $course->imagen = $filePath; // Actualiza la imagen en la base de datos
                $course->save();
                Log::info('Imagen guardada en: ' . $filePath);
            } catch (\Exception $e) {
                Log::error('Error al guardar la imagen del curso: ' . $e->getMessage());
            }
        }
    
        // Actualiza el resto de los campos del curso
        $course->update($validated);
    
        return redirect()->route('courses.index')->with('success', 'Curso actualizado con éxito.');
    }
    

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Curso eliminado exitosamente.');
    }
}
