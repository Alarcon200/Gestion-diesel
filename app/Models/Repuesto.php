<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repuesto extends Model
{
    protected $table = 'repuestos';
    public $timestamps = false;
    protected $fillable = ['nombre', 'descripcion', 'precio_referencia', 'unidad_medida'];

    public function cotizacionDetalles()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(SolicitudRepuesto::class);
    }

    public function reparacionRepuestos()
    {
        return $this->hasMany(ReparacionRepuesto::class);
    }
}
