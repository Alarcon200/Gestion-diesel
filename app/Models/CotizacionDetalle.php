<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionDetalle extends Model
{
    protected $table = 'cotizacion_detalle';
    public $timestamps = false;
    protected $fillable = ['cotizacion_id', 'repuesto_id', 'tipo', 'descripcion', 'cantidad', 'precio_unitario', 'subtotal'];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class);
    }
}