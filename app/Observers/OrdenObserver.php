<?php

namespace App\Observers;

use App\Models\Notificacion;
use App\Models\Orden;
use Illuminate\Support\Facades\Log;

/**
 * Avisa al mecánico cuando le asignan (o le quitan) un vehículo.
 */
class OrdenObserver
{
    public function updated(Orden $orden): void
    {
        if (!$orden->wasChanged('mecanico_id')) {
            return;
        }

        try {
            $orden->loadMissing('vehiculo', 'ingreso');

            $actorId = Notificacion::actorId();
            $placa = $orden->vehiculo->placa ?? 'sin placa';
            $anterior = $orden->getOriginal('mecanico_id');
            $nuevo = $orden->mecanico_id;

            if ($nuevo && (int) $nuevo !== (int) $actorId) {
                $detalle = trim(($orden->numero_orden ?? '') . ' · ' . ($orden->ingreso->motivo ?? ''), ' ·');
                Notificacion::aUsuario(
                    (int) $nuevo,
                    'asignacion',
                    'Nuevo trabajo asignado: ' . $placa,
                    $detalle !== '' ? $detalle : null,
                    $orden->ingreso_id
                );
            }

            if ($anterior && (int) $anterior !== (int) $nuevo && (int) $anterior !== (int) $actorId) {
                Notificacion::aUsuario(
                    (int) $anterior,
                    'asignacion',
                    'Ya no tienes asignado: ' . $placa,
                    'El jefe de taller pasó este trabajo a otro mecánico.',
                    $orden->ingreso_id
                );
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la asignación: ' . $e->getMessage());
        }
    }
}
