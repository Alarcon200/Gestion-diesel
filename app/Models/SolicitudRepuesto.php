<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudRepuesto extends Model
{
    protected $table = 'solicitudes_repuestos';
    public $timestamps = false;
    protected $fillable = ['orden_id', 'mecanico_id', 'mensajero_id', 'repuesto_id', 'cantidad', 'estado', 'fecha_solicitud', 'fecha_entrega', 'precio_compra', 'lugar_compra'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function mecanico()
    {
        return $this->belongsTo(Usuario::class, 'mecanico_id');
    }

    public function mensajero()
    {
        return $this->belongsTo(Usuario::class, 'mensajero_id');
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class);
    }
}