<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformeServicio extends Model
{
    protected $table = 'informes_servicio';
    public $timestamps = false;
    protected $fillable = ['orden_id', 'usuario_id', 'valor_servicio', 'observaciones_finales', 'fecha_generacion'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
