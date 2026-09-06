<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asignacion;

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
            'orden_id' => 'required|exists:ordenes,id',
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        $data['fecha_asignacion'] = now();
        $data['estado'] = 'asignado';

        $asignacion = Asignacion::create($data);

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