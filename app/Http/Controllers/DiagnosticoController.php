<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnostico;

class DiagnosticoController extends Controller
{
    public function index()
    {
        return Diagnostico::with('orden.vehiculo.cliente', 'usuario')
            ->orderByDesc('fecha')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'problema_encontrado' => 'required|string',
            'causa_probable' => 'nullable|string',
            'trabajo_a_realizar' => 'required|string',
            'recomendaciones' => 'nullable|string',
        ]);

        $data['usuario_id'] = $request->user()->id;
        $data['fecha'] = now();

        $diagnostico = Diagnostico::create($data);

        return response()->json($diagnostico->load('orden.vehiculo.cliente', 'usuario'), 201);
    }

    public function show(Diagnostico $diagnostico)
    {
        return $diagnostico->load('orden.vehiculo.cliente', 'usuario', 'fotografias');
    }

    public function update(Request $request, Diagnostico $diagnostico)
    {
        $data = $request->validate([
            'problema_encontrado' => 'sometimes|required|string',
            'causa_probable' => 'nullable|string',
            'trabajo_a_realizar' => 'sometimes|required|string',
            'recomendaciones' => 'nullable|string',
        ]);

        $diagnostico->update($data);

        return $diagnostico;
    }

    public function destroy(Diagnostico $diagnostico)
    {
        $diagnostico->delete();

        return response()->json(null, 204);
    }
}