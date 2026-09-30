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
        'estado' => 'sometimes|required|in:en_espera_diagnostico,en_reparacion,pendiente_cotizacion,pendiente_informe,pendiente_prefactura,orden_enviada,salio_taller',
        'observaciones' => 'nullable|string',
        'fecha_salida' => 'nullable|date',
    ]);

    if (($data['estado'] ?? null) === 'salio_taller' && empty($data['fecha_salida'])) {
        $data['fecha_salida'] = now();
    }

    $ingreso->update($data);

    return $ingreso->load('vehiculo.cliente', 'usuario');
}

    public function destroy(Ingreso $ingreso)
    {
        $ingreso->delete();

        return response()->json(null, 204);
    }
}