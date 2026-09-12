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

    public function pdf(InformeServicio $informesServicio)
{
    $orden = $informesServicio->orden()->with(
        'vehiculo.cliente',
        'diagnosticos',
        'reparaciones.fotografias',
        'reparaciones.mecanico',
        'pruebas'
    )->first();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.informe', [
        'informe' => $informesServicio,
        'orden' => $orden,
    ]);

    return $pdf->download('informe-' . $orden->numero_orden . '.pdf');
}

}


