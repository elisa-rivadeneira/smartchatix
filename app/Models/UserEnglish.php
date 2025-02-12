<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEnglish extends Model
{
    use HasFactory;

    protected $table = 'users_english';
    protected $fillable = ['user_id', 'level', 'progress', 'history'];

    protected $casts = [
        'history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
