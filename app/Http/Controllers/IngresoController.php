<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingreso;

class IngresoController extends Controller
{
    public function index()
    {
        return Ingreso::with('vehiculo.cliente', 'usuario')
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
        $data['estado'] = 'en_taller';

        $ingreso = Ingreso::create($data);

        return response()->json($ingreso->load('vehiculo.cliente', 'usuario'), 201);
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
            'estado' => 'sometimes|required|string|max:30',
            'observaciones' => 'nullable|string',
        ]);

        $ingreso->update($data);

        return $ingreso;
    }

    public function destroy(Ingreso $ingreso)
    {
        $ingreso->delete();

        return response()->json(null, 204);
    }
}