<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspeccionDetalle extends Model
{
    protected $table = 'inspeccion_detalle';
    public $timestamps = false;
    protected $fillable = ['inspeccion_id', 'item', 'estado', 'observacion'];

    public function inspeccion()
    {
        return $this->belongsTo(Inspeccion::class);
    }
}
