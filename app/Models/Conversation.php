<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'assistant_id', 'session_id', 'total_tokens']; // Asegúrate de incluir 'assistant_id'

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // App\Models\Conversation.php
    public function assistant()
    {
        return $this->belongsTo(Assistant::class);
    }


}
