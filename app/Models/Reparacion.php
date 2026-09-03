<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reparacion extends Model
{
    protected $table = 'reparaciones';
    public $timestamps = false;
    protected $fillable = ['orden_id', 'mecanico_id', 'trabajo_realizado', 'observaciones', 'fecha'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function mecanico()
    {
        return $this->belongsTo(Usuario::class, 'mecanico_id');
    }

    public function repuestos()
    {
        return $this->hasMany(ReparacionRepuesto::class);
    }

    public function fotografias()
    {
        return $this->morphMany(Fotografia::class, 'fotografiable');
    }
}
