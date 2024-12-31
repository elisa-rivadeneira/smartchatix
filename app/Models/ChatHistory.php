<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = ['assistant_id', 'user_message', 'assistant_response'];

    public function assistant()
    {
        return $this->belongsTo(Assistant::class);
    }
}
