<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';
    public $timestamps = false;
    protected $fillable = ['orden_id', 'usuario_id', 'subtotal_mano_obra', 'subtotal_repuestos', 'total', 'estado', 'fecha_creacion', 'fecha_respuesta'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function detalles()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }
}
