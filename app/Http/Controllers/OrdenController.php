<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orden;

class OrdenController extends Controller
{
    public function index()
    {
        return Orden::with('vehiculo.cliente', 'usuario', 'asignaciones.usuario')
            ->orderByDesc('fecha_creacion')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ingreso_id' => 'required|exists:ingresos,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
        ]);

        $data['usuario_id'] = $request->user()->id;
        $data['fecha_creacion'] = now();
        $data['estado'] = 'pendiente';
        $data['numero_orden'] = 'OT-' . str_pad((Orden::max('id') + 1), 5, '0', STR_PAD_LEFT);

        $orden = Orden::create($data);

        return response()->json($orden->load('vehiculo.cliente', 'usuario'), 201);
    }

    public function show(Orden $orden)
    {
        return $orden->load(
            'vehiculo.cliente',
            'usuario',
            'ingreso',
            'asignaciones.usuario',
            'diagnosticos',
            'cotizaciones.detalles',
            'reparaciones',
            'pruebas'
        );
    }

    public function update(Request $request, Orden $orden)
    {
        $data = $request->validate([
            'estado' => 'sometimes|required|string|max:30',
            'fecha_finalizacion' => 'nullable|date',
        ]);

        $orden->update($data);

        return $orden;
    }

    public function destroy(Orden $orden)
    {
        $orden->delete();

        return response()->json(null, 204);
    }
}
