<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspeccion extends Model
{
    protected $table = 'inspecciones';
    protected $fillable = ['ingreso_id', 'usuario_id', 'fecha_inspeccion', 'estado_general', 'nivel_combustible', 'kilometraje', 'observaciones'];

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function detalles()
    {
        return $this->hasMany(InspeccionDetalle::class);
    }

    public function fotografias()
    {
        return $this->morphMany(Fotografia::class, 'fotografiable');
    }
}