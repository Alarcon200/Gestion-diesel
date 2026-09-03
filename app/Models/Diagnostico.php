<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    protected $table = 'diagnosticos';
    public $timestamps = false;
    protected $fillable = ['orden_id', 'usuario_id', 'problema_encontrado', 'causa_probable', 'trabajo_a_realizar', 'recomendaciones', 'fecha'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function fotografias()
    {
        return $this->morphMany(Fotografia::class, 'fotografiable');
    }
}
