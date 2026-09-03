<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inspeccion;
use App\Models\InspeccionDetalle;
use Illuminate\Support\Facades\DB;

class InspeccionController extends Controller
{
    public function index()
    {
        return Inspeccion::with('ingreso.vehiculo', 'usuario', 'detalles')
            ->orderByDesc('fecha_inspeccion')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ingreso_id' => 'required|exists:ingresos,id',
            'fecha_inspeccion' => 'required|date',
            'estado_general' => 'nullable|string|max:50',
            'nivel_combustible' => 'nullable|string|max:20',
            'kilometraje' => 'nullable|integer',
            'observaciones' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item' => 'required_with:items|string|max:60',
            'items.*.estado' => 'required_with:items|string|max:30',
            'items.*.observacion' => 'nullable|string|max:200',
        ]);

        $inspeccion = DB::transaction(function () use ($data, $request) {
            $inspeccion = Inspeccion::create([
                'ingreso_id' => $data['ingreso_id'],
                'usuario_id' => $request->user()->id,
                'fecha_inspeccion' => $data['fecha_inspeccion'],
                'estado_general' => $data['estado_general'] ?? null,
                'nivel_combustible' => $data['nivel_combustible'] ?? null,
                'kilometraje' => $data['kilometraje'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    InspeccionDetalle::create([
                        'inspeccion_id' => $inspeccion->id,
                        'item' => $item['item'],
                        'estado' => $item['estado'],
                        'observacion' => $item['observacion'] ?? null,
                    ]);
                }
            }

            return $inspeccion;
        });

        return response()->json($inspeccion->load('detalles', 'ingreso.vehiculo', 'usuario'), 201);
    }

    public function show(Inspeccion $inspeccion)
    {
        return $inspeccion->load('detalles', 'ingreso.vehiculo', 'usuario', 'fotografias');
    }

    public function update(Request $request, Inspeccion $inspeccion)
    {
        $data = $request->validate([
            'estado_general' => 'nullable|string|max:50',
            'nivel_combustible' => 'nullable|string|max:20',
            'kilometraje' => 'nullable|integer',
            'observaciones' => 'nullable|string',
        ]);

        $inspeccion->update($data);

        return $inspeccion->load('detalles');
    }

    public function destroy(Inspeccion $inspeccion)
    {
        $inspeccion->delete();

        return response()->json(null, 204);
    }
}