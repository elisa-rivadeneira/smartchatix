<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToText\Pdf;


class DocumentController extends Controller
{
    public function processFile(Document $document) {
        $filePath = storage_path('app/public/' . $document->path);
        $content = '';

        if (str_ends_with($document->filename, '.pdf')) {
            $content = Pdf::getText($filePath);
        } elseif (str_ends_with($document->filename, '.docx')) {
            $phpWord = IOFactory::load($filePath);
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $content .= $element->getText() . " ";
                    }
                }
            }
        }

        $document->content = $content;
        $document->save();
    }

    public function uploadDocument(Request $request)
    {
        // Validar el archivo
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,docx',
            'assistant_id' => 'required|exists:assistants,id',
        ]);
    
        // Depuración: Verificar si el archivo está presente
        if (!$request->hasFile('file')) {
            dd('No se ha recibido un archivo.');
        }
    
        // Depuración: Verificar si el archivo es válido
        if (!$request->file('file')->isValid()) {
            dd('El archivo no es válido.');
        }
    
        // Intentar guardar el archivo en storage
        try {
           // $path = $request->file('file')->store('documents');
           $path = $request->file('file')->store('documents', 'public');
            
           //$filePath = $file->store('documents', 'public');
            //dd($path);  // Esto te dará la ruta completa del archivo guardado.

            
            //"documents/C1DkLt6KvGHRvIlIVJpZwEtUMUfA6dinEih4T2Vd.pdf" // app/Http/Controllers/DocumentController.php:54

        } catch (\Exception $e) {
            // Depuración: Capturar cualquier error al intentar almacenar el archivo
            return response()->json(['error' => 'Error al guardar el archivo: ' . $e->getMessage()], 500);
        }
    
        // Depuración: Verificar si el archivo se guardó correctamente
        if (!$path) {
            return response()->json(['error' => 'No se pudo guardar el archivo.'], 500);
        }
    
        
        // Crear el registro del documento
        $document = Document::create([
            'assistant_id' => $validated['assistant_id'],
            'filename' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
        ]);

    
        // Procesar el archivo para extraer el contenido (si corresponde)
        $this->processFile($document);
    
        //return response()->json(['message' => 'Documento subido y procesado correctamente.']);
        return back()->with('success', 'Documento subido y procesado correctamente.');

    }

    public function destroy(Document $document)
{
    // Eliminar el archivo físico
    $filePath = storage_path('app/' . $document->path);
    if (file_exists($filePath)) {
        unlink($filePath); // Elimina el archivo físico
    }

    // Eliminar el registro de la base de datos
    $document->delete();

    // Redirigir con mensaje de éxito
    return redirect()->route('assistants.edit', $document->assistant_id)
                     ->with('success', 'Documento eliminado correctamente');
}
}
