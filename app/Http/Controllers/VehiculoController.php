<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;

class VehiculoController extends Controller
{
    public function index()
    {
        return Vehiculo::with('cliente')->orderBy('placa')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'placa' => 'required|string|max:15|unique:vehiculos,placa',
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'anio' => 'nullable|integer',
            'tipo' => 'nullable|string|max:50',
            'kilometraje' => 'nullable|integer',
            'cliente_id' => 'required|exists:clientes,id',
        ]);

        $vehiculo = Vehiculo::create($data);

        return response()->json($vehiculo->load('cliente'), 201);
    }

    public function show(Vehiculo $vehiculo)
    {
        return $vehiculo->load('cliente', 'ingresos');
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $data = $request->validate([
            'placa' => 'sometimes|required|string|max:15|unique:vehiculos,placa,' . $vehiculo->id,
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'anio' => 'nullable|integer',
            'tipo' => 'nullable|string|max:50',
            'kilometraje' => 'nullable|integer',
            'cliente_id' => 'sometimes|required|exists:clientes,id',
        ]);

        $vehiculo->update($data);

        return $vehiculo;
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();

        return response()->json(null, 204);
    }
}