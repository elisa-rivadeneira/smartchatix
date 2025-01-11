<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

class AIModel extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'identifier', 'description'];

        // Si el nombre de la tabla no sigue la convención, se debe especificar
        protected $table = 'a_i_models';

    public function assistants()
    {
        return $this->hasMany(Assistant::class);
    }
}
