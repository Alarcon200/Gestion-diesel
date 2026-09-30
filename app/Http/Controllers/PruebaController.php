<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prueba;
use App\Models\Orden;

class PruebaController extends Controller
{
    public function index()
    {
        return Prueba::with('orden.vehiculo', 'usuario')
            ->orderByDesc('fecha')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ingreso_id' => 'required|exists:ingresos,id',
            'prueba_realizada' => 'required|string|max:200',
            'resultado' => 'required|in:correcto,con_observaciones',
            'observaciones' => 'nullable|string',
        ]);

        $orden = Orden::obtenerOCrearParaIngreso($data['ingreso_id'], $request->user()->id);

        $prueba = Prueba::create([
            'orden_id' => $orden->id,
            'usuario_id' => $request->user()->id,
            'prueba_realizada' => $data['prueba_realizada'],
            'resultado' => $data['resultado'],
            'observaciones' => $data['observaciones'] ?? null,
            'fecha' => now(),
        ]);

        return response()->json($prueba->load('orden.vehiculo', 'usuario'), 201);
    }

    public function show(Prueba $prueba)
    {
        return $prueba->load('orden.vehiculo', 'usuario');
    }

    public function update(Request $request, Prueba $prueba)
    {
        $data = $request->validate([
            'resultado' => 'sometimes|required|in:correcto,con_observaciones',
            'observaciones' => 'nullable|string',
        ]);

        $prueba->update($data);

        return $prueba;
    }

    public function destroy(Prueba $prueba)
    {
        $prueba->delete();

        return response()->json(null, 204);
    }
}