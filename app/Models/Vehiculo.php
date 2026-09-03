<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';
    protected $fillable = ['placa', 'marca', 'modelo', 'anio', 'tipo', 'kilometraje', 'cliente_id'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ingresos()
    {
        return $this->hasMany(Ingreso::class);
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class);
    }
}