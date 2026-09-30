<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asignacion;
use App\Models\Orden;

class AsignacionController extends Controller
{
    public function index()
    {
        return Asignacion::with('orden.vehiculo', 'usuario')
            ->orderByDesc('fecha_asignacion')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ingreso_id' => 'required|exists:ingresos,id',
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        $orden = Orden::obtenerOCrearParaIngreso($data['ingreso_id'], $request->user()->id);

        $asignacion = Asignacion::create([
            'orden_id' => $orden->id,
            'usuario_id' => $data['usuario_id'],
            'fecha_asignacion' => now(),
            'estado' => 'asignado',
        ]);

        return response()->json($asignacion->load('orden.vehiculo', 'usuario'), 201);
    }

    public function show(Asignacion $asignacion)
    {
        return $asignacion->load('orden.vehiculo', 'usuario');
    }

    public function update(Request $request, Asignacion $asignacion)
    {
        $data = $request->validate([
            'estado' => 'required|in:asignado,en_proceso,finalizado',
        ]);

        $asignacion->update($data);

        return $asignacion;
    }

    public function destroy(Asignacion $asignacion)
    {
        $asignacion->delete();

        return response()->json(null, 204);
    }
}