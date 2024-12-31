<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assistant extends Model
{
    use HasFactory;

    // Especificamos qué campos pueden ser asignados masivamente
    protected $fillable = [
        'name',
        'prompt',
        'user_id',
        'whatsapp_number',  // Agregar el campo aquí

    ];

    public function chatHistories()
    {
        return $this->hasMany(ChatHistory::class);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}

}

