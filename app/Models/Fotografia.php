<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fotografia extends Model
{
    protected $table = 'fotografias';
    public $timestamps = false;
    protected $fillable = ['fotografiable_id', 'fotografiable_type', 'ruta_archivo', 'descripcion', 'fecha_subida'];

    public function fotografiable()
    {
        return $this->morphTo();
    }
}
