<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingresos';
    protected $fillable = ['vehiculo_id', 'usuario_id', 'conductor', 'fecha_ingreso', 'motivo', 'kilometraje', 'estado', 'observaciones'];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function inspecciones()
    {
        return $this->hasMany(Inspeccion::class);
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class);
    }
}
