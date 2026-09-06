<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reparacion;
use App\Models\ReparacionRepuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReparacionController extends Controller
{
    public function index()
    {
        return Reparacion::with('orden.vehiculo', 'mecanico', 'repuestos.repuesto', 'fotografias')
            ->orderByDesc('fecha')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'nullable|exists:ordenes,id',
            'trabajo_realizado' => 'required|string',
            'observaciones' => 'nullable|string',
            'repuestos' => 'nullable|array',
            'repuestos.*.repuesto_id' => 'required_with:repuestos|exists:repuestos,id',
            'repuestos.*.cantidad' => 'required_with:repuestos|numeric|min:0.01',
            'fotos_ids' => 'required|array|min:1',
            'fotos_ids.*' => 'exists:fotografias,id',
        ], [
            'fotos_ids.required' => 'Debes subir al menos una foto de evidencia antes de registrar la reparacion.',
            'fotos_ids.min' => 'Debes subir al menos una foto de evidencia antes de registrar la reparacion.',
        ]);

        $reparacion = DB::transaction(function () use ($data, $request) {
            $reparacion = Reparacion::create([
                'orden_id' => $data['orden_id'] ?? null,
                'mecanico_id' => $request->user()->id,
                'trabajo_realizado' => $data['trabajo_realizado'],
                'observaciones' => $data['observaciones'] ?? null,
                'fecha' => now(),
            ]);

            if (!empty($data['repuestos'])) {
                foreach ($data['repuestos'] as $r) {
                    ReparacionRepuesto::create([
                        'reparacion_id' => $reparacion->id,
                        'repuesto_id' => $r['repuesto_id'],
                        'cantidad' => $r['cantidad'],
                    ]);
                }
            }

            \App\Models\Fotografia::whereIn('id', $data['fotos_ids'])
                ->update([
                    'fotografiable_id' => $reparacion->id,
                    'fotografiable_type' => Reparacion::class,
                ]);

            return $reparacion;
        });

        return response()->json(
            $reparacion->load('orden.vehiculo', 'mecanico', 'repuestos.repuesto', 'fotografias'),
            201
        );
    }

    public function show(Reparacion $reparacion)
    {
        return $reparacion->load('orden.vehiculo', 'mecanico', 'repuestos.repuesto', 'fotografias');
    }

    public function update(Request $request, Reparacion $reparacion)
    {
        $data = $request->validate([
            'trabajo_realizado' => 'sometimes|required|string',
            'observaciones' => 'nullable|string',
        ]);

        $reparacion->update($data);

        return $reparacion;
    }

    public function destroy(Reparacion $reparacion)
    {
        $reparacion->delete();

        return response()->json(null, 204);
    }
}