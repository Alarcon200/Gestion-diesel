<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'asignaciones';
    public $timestamps = false;
    protected $fillable = ['orden_id', 'usuario_id', 'fecha_asignacion', 'estado'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
