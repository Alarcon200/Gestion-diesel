<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReparacionRepuesto extends Model
{
    protected $table = 'reparacion_repuesto';
    public $timestamps = false;
    protected $fillable = ['reparacion_id', 'repuesto_id', 'cantidad'];

    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function repuesto()
    {
        return $this->belongsTo(Repuesto::class);
    }
}
