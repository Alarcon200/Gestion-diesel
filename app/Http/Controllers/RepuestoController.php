<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repuesto;

class RepuestoController extends Controller
{
    public function index()
    {
        return Repuesto::orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'descripcion' => 'nullable|string',
            'precio_referencia' => 'nullable|numeric',
            'unidad_medida' => 'nullable|string|max:20',
        ]);

        $repuesto = Repuesto::create($data);

        return response()->json($repuesto, 201);
    }

    public function show(Repuesto $repuesto)
    {
        return $repuesto;
    }

    public function update(Request $request, Repuesto $repuesto)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:120',
            'descripcion' => 'nullable|string',
            'precio_referencia' => 'nullable|numeric',
            'unidad_medida' => 'nullable|string|max:20',
        ]);

        $repuesto->update($data);

        return $repuesto;
    }

    public function destroy(Repuesto $repuesto)
    {
        $repuesto->delete();

        return response()->json(null, 204);
    }
}