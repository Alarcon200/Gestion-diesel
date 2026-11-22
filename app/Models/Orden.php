<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $table = 'ordenes';
    protected $fillable = ['numero_orden', 'ingreso_id', 'vehiculo_id', 'usuario_id', 'fecha_creacion', 'estado', 'fecha_finalizacion'];

    public static function obtenerOCrearParaIngreso($ingresoId, $usuarioId)
{
    $orden = self::where('ingreso_id', $ingresoId)->first();

    if ($orden) {
        return $orden;
    }

    return self::create([
        'numero_orden' => 'OT-' . str_pad((self::max('id') + 1), 5, '0', STR_PAD_LEFT),
        'ingreso_id' => $ingresoId,
        'vehiculo_id' => \App\Models\Ingreso::find($ingresoId)->vehiculo_id,
        'usuario_id' => $usuarioId,
        'fecha_creacion' => now(),
        'estado' => 'pendiente',
    ]);
}

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class);
    }

    /**
     * El mecánico cambia el estado de SU orden y el vehículo (ingreso) se actualiza solo,
     * para que la tabla del jefe y de la secretaria lo muestre de inmediato.
     *   en_reparacion -> el vehículo pasa a "En reparación"
     *   finalizada    -> el vehículo pasa a "Pendiente de informe"
     */
    public function cambiarEstadoPorMecanico(string $estado, int $usuarioId): void
    {
        $this->estado = $estado;

        if ($estado === 'finalizada') {
            $this->fecha_finalizacion = now();
        }

        $this->save();

        $mapa = [
            'en_reparacion' => 'en_reparacion',
            'finalizada' => 'pendiente_informe',
        ];

        if (!isset($mapa[$estado])) {
            return;
        }

        $ingreso = $this->ingreso;
        $nuevo = $mapa[$estado];

        if (!$ingreso || $ingreso->estado === 'salio_taller' || $ingreso->estado === $nuevo) {
            return;
        }

        $anterior = $ingreso->estado;
        $ingreso->update(['estado' => $nuevo]);

        HistorialEstadoIngreso::create([
            'ingreso_id' => $ingreso->id,
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'usuario_id' => $usuarioId,
            'fecha' => now(),
        ]);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class);
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function solicitudesRepuestos()
    {
        return $this->hasMany(SolicitudRepuesto::class);
    }

    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class);
    }

    public function pruebas()
    {
        return $this->hasMany(Prueba::class);
    }

    public function informe()
    {
        return $this->hasOne(InformeServicio::class);
    }

    public function mecanico()
    {
        // Le indicamos a Laravel que 'mecanico_id' se conecta con el modelo Usuario
        return $this->belongsTo(Usuario::class, 'mecanico_id');
    }

    public function informes()
{
    return $this->hasMany(InformeServicio::class);
}
}
