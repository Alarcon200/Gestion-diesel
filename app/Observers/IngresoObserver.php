<?php

namespace App\Observers;

use App\Models\Ingreso;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Log;

/**
 * Avisa automáticamente cuando entra un vehículo y cuando cambia su estado.
 * Funciona igual si el cambio viene de la web o de la app.
 * Si algo falla al notificar, NUNCA se rompe el registro del vehículo.
 */
class IngresoObserver
{
    public function created(Ingreso $ingreso): void
    {
        try {
            $ingreso->loadMissing('vehiculo.cliente');

            $placa = $ingreso->vehiculo->placa ?? 'sin placa';
            $cliente = $ingreso->vehiculo->cliente->nombre ?? null;
            $detalle = trim(($cliente ? $cliente . ' · ' : '') . ($ingreso->motivo ?? ''));

            Notificacion::aRoles(
                Notificacion::ROLES_GESTION,
                'ingreso',
                'Ingresó el vehículo ' . $placa,
                $detalle !== '' ? $detalle : null,
                $ingreso->id,
                Notificacion::actorId()
            );
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar el ingreso: ' . $e->getMessage());
        }
    }

    public function updated(Ingreso $ingreso): void
    {
        if (!$ingreso->wasChanged('estado')) {
            return;
        }

        try {
            $ingreso->loadMissing('vehiculo');

            $actorId = Notificacion::actorId();
            $placa = $ingreso->vehiculo->placa ?? 'sin placa';
            $anterior = Notificacion::etiquetaEstado($ingreso->getOriginal('estado'));
            $nuevo = Notificacion::etiquetaEstado($ingreso->estado);

            Notificacion::aRoles(
                Notificacion::ROLES_GESTION,
                'estado',
                $placa . ': ' . $nuevo,
                Notificacion::nombreActor($actorId) . ' lo pasó de "' . $anterior . '" a "' . $nuevo . '"',
                $ingreso->id,
                $actorId
            );
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar el cambio de estado: ' . $e->getMessage());
        }
    }
}
