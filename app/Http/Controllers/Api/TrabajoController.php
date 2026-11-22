<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ingreso;
use App\Models\Orden;
use App\Models\Usuario;

class TrabajoController extends Controller
{
    private const ESTADOS_CERRADOS = ['finalizada', 'entregada', 'cancelada'];

    // Órdenes asignadas al mecánico que inició sesión (las que está haciendo van primero)
    public function misTrabajos(Request $request)
    {
        $trabajos = Orden::where('mecanico_id', $request->user()->id)
            ->whereNotIn('estado', ['entregada', 'cancelada'])
            ->with('vehiculo.cliente', 'ingreso')
            ->orderByRaw("CASE estado WHEN 'en_reparacion' THEN 0 WHEN 'pendiente' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'mensaje' => 'Trabajos recuperados con éxito',
            'cantidad' => $trabajos->count(),
            'trabajos' => $trabajos,
        ], 200);
    }

    // El mecánico marca su orden "en_reparacion" o "finalizada"; el vehículo se actualiza solo
    public function cambiarEstado(Request $request, Orden $orden)
    {
        $data = $request->validate([
            'estado' => 'required|in:en_reparacion,finalizada',
        ]);

        if ((int) $orden->mecanico_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Esta orden no está asignada a ti.'], 403);
        }

        $orden->cambiarEstadoPorMecanico($data['estado'], (int) $request->user()->id);

        return response()->json($orden->load('vehiculo.cliente', 'ingreso'));
    }

    // ---- Administrador y Jefe de taller: asignar trabajos desde el celular ----

    // Órdenes de los vehículos que siguen en el taller, con su mecánico (si ya tienen)
    public function ordenesAsignables(Request $request)
    {
        // Todo vehículo activo debe tener su orden (igual que en la web)
        $sinOrden = Ingreso::whereNotIn('estado', ['salio_taller'])
            ->whereDoesntHave('ordenes')
            ->get();

        foreach ($sinOrden as $ingreso) {
            Orden::obtenerOCrearParaIngreso($ingreso->id, $request->user()->id);
        }

        $ordenes = Orden::with('vehiculo.cliente', 'mecanico:id,nombre,apellido', 'ingreso:id,motivo,estado,fecha_ingreso')
            ->whereNotIn('estado', self::ESTADOS_CERRADOS)
            ->whereHas('ingreso', fn ($q) => $q->where('estado', '!=', 'salio_taller'))
            ->orderByDesc('id')
            ->get();

        return response()->json($ordenes);
    }

    // Mecánicos activos y cuántos trabajos abiertos tiene cada uno
    public function mecanicos()
    {
        $abiertos = Orden::whereNotNull('mecanico_id')
            ->whereNotIn('estado', self::ESTADOS_CERRADOS)
            ->selectRaw('mecanico_id, count(*) as total')
            ->groupBy('mecanico_id')
            ->pluck('total', 'mecanico_id');

        $mecanicos = Usuario::whereHas('rol', fn ($q) => $q->where('nombre', 'Mecánico'))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido'])
            ->map(fn ($m) => [
                'id' => $m->id,
                'nombre' => trim($m->nombre . ' ' . ($m->apellido ?? '')),
                'trabajos_abiertos' => (int) ($abiertos[$m->id] ?? 0),
            ]);

        return response()->json($mecanicos->values());
    }

    // Asigna (o cambia) el mecánico de una orden
    public function asignarMecanico(Request $request, Orden $orden)
    {
        $data = $request->validate([
            'mecanico_id' => 'required|integer|exists:usuarios,id',
        ]);

        if (in_array($orden->estado, self::ESTADOS_CERRADOS, true)) {
            return response()->json(['message' => 'Esta orden ya está cerrada.'], 422);
        }

        $mecanico = Usuario::where('id', $data['mecanico_id'])
            ->where('activo', true)
            ->whereHas('rol', fn ($q) => $q->where('nombre', 'Mecánico'))
            ->first();

        if (!$mecanico) {
            return response()->json(['message' => 'Selecciona un mecánico activo.'], 422);
        }

        $orden->mecanico_id = $mecanico->id;
        $orden->save();

        return response()->json($orden->load('vehiculo.cliente', 'mecanico:id,nombre,apellido', 'ingreso:id,motivo,estado,fecha_ingreso'));
    }
}
