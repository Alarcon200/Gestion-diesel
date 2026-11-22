<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingreso;
use App\Models\HistorialEstadoIngreso;
use App\Models\Orden;

class IngresoController extends Controller
{
    private const ESTADOS = 'en_espera_diagnostico,en_reparacion,pendiente_cotizacion,pendiente_informe,pendiente_prefactura,orden_enviada,salio_taller';

    public function index()
    {
        return Ingreso::with('vehiculo.cliente', 'usuario', 'ordenes')
            ->orderByDesc('fecha_ingreso')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'conductor' => 'nullable|string|max:120',
            'fecha_ingreso' => 'required|date',
            'motivo' => 'required|string|max:200',
            'kilometraje' => 'nullable|integer',
            'observaciones' => 'nullable|string',
        ]);

        $data['usuario_id'] = $request->user()->id;
        $data['estado'] = 'en_espera_diagnostico';

        $ingreso = Ingreso::create($data);

        HistorialEstadoIngreso::create([
            'ingreso_id' => $ingreso->id,
            'estado_anterior' => null,
            'estado_nuevo' => 'en_espera_diagnostico',
            'usuario_id' => $request->user()->id,
            'fecha' => now(),
        ]);

        // La orden se crea desde el ingreso para poder asignar mecánico de una vez
        Orden::obtenerOCrearParaIngreso($ingreso->id, $request->user()->id);

        return response()->json($ingreso->load('vehiculo.cliente', 'usuario', 'ordenes'), 201);
    }

    public function show(Ingreso $ingreso)
    {
        return $ingreso->load('vehiculo.cliente', 'usuario', 'inspecciones', 'ordenes');
    }

    public function update(Request $request, Ingreso $ingreso)
    {
        $data = $request->validate([
            'conductor' => 'nullable|string|max:120',
            'motivo' => 'sometimes|required|string|max:200',
            'kilometraje' => 'nullable|integer',
            'observaciones' => 'nullable|string',
        ]);

        $ingreso->update($data);

        return $ingreso->load('vehiculo.cliente', 'usuario');
    }

    // Cambiar solo el estado del vehículo (Administrador, Jefe de taller y Contabilidad)
    public function cambiarEstado(Request $request, Ingreso $ingreso)
    {
        $data = $request->validate([
            'estado' => 'required|in:' . self::ESTADOS,
        ]);

        $anterior = $ingreso->estado;

        if ($anterior !== $data['estado']) {
            $ingreso->update([
                'estado' => $data['estado'],
                'fecha_salida' => $data['estado'] === 'salio_taller' ? now() : null,
            ]);

            HistorialEstadoIngreso::create([
                'ingreso_id' => $ingreso->id,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $data['estado'],
                'usuario_id' => $request->user()->id,
                'fecha' => now(),
            ]);
        }

        return $ingreso->load('vehiculo.cliente', 'usuario', 'ordenes');
    }

    public function destroy(Ingreso $ingreso)
    {
        $ingreso->delete();

        return response()->json(null, 204);
    }
}
