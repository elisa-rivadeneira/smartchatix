<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistant_id',
        'filename',
        'path',
        'content',
    ];

    // Relación con el modelo Asistente
    public function asistente()
    {
        return $this->belongsTo(Asistente::class);
    }
}