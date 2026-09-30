<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoIngreso extends Model
{
    protected $table = 'historial_estados_ingreso';
    public $timestamps = false;
    protected $fillable = ['ingreso_id', 'estado_anterior', 'estado_nuevo', 'usuario_id', 'fecha'];

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}