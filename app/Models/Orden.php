<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $table = 'ordenes';
    protected $fillable = ['numero_orden', 'ingreso_id', 'vehiculo_id', 'usuario_id', 'fecha_creacion', 'estado', 'fecha_finalizacion'];

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class);
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function solicitudesRepuestos()
    {
        return $this->hasMany(SolicitudRepuesto::class);
    }

    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class);
    }

    public function pruebas()
    {
        return $this->hasMany(Prueba::class);
    }

    public function informe()
    {
        return $this->hasOne(InformeServicio::class);
    }
}
