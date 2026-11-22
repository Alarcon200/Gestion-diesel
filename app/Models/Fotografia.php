<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fotografia extends Model
{
    protected $table = 'fotografias';
    public $timestamps = false;
    protected $fillable = ['fotografiable_id', 'fotografiable_type', 'ruta_archivo', 'descripcion', 'fecha_subida', 'usuario_id'];

    public function fotografiable()
    {
        return $this->morphTo();
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
