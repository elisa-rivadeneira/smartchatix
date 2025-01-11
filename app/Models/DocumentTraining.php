<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTraining extends Model
{
    use HasFactory;

    // Definir la tabla si el nombre no sigue el estándar plural (document_trainings)
    protected $table = 'document_trainings';

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = [
        'filename',
        'path',
        'assistant_id',
    ];

    // Relación con el modelo Assistant
    public function assistant()
    {
        return $this->belongsTo(Assistant::class);
    }
}
