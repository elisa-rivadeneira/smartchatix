<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Define los campos que se pueden llenar de forma masiva
    protected $fillable = ['conversation_id', 'sender', 'message', 'created_at'];

    // Relación con el modelo Conversation (un mensaje pertenece a una conversación)
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}