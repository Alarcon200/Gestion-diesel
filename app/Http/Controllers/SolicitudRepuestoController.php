<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudRepuesto;

class SolicitudRepuestoController extends Controller
{
    public function index()
    {
        return SolicitudRepuesto::with('orden.vehiculo', 'mecanico', 'mensajero', 'repuesto')
            ->orderByDesc('fecha_solicitud')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'repuesto_id' => 'required|exists:repuestos,id',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        $data['mecanico_id'] = $request->user()->id;
        $data['estado'] = 'solicitado';
        $data['fecha_solicitud'] = now();

        $solicitud = SolicitudRepuesto::create($data);

        return response()->json($solicitud->load('orden.vehiculo', 'mecanico', 'repuesto'), 201);
    }

    public function show(SolicitudRepuesto $solicitudesRepuesto)
    {
        return $solicitudesRepuesto->load('orden.vehiculo', 'mecanico', 'mensajero', 'repuesto');
    }

    public function update(Request $request, SolicitudRepuesto $solicitudesRepuesto)
{
    $data = $request->validate([
        'estado' => 'required|in:solicitado,asignado,recibida',
        'mensajero_id' => 'nullable|exists:usuarios,id',
        'precio_compra' => 'required_if:estado,recibida|nullable|numeric|min:0',
        'lugar_compra' => 'required_if:estado,recibida|nullable|string|max:100',
    ]);

    if ($data['estado'] === 'asignado' && empty($data['mensajero_id'])) {
        $data['mensajero_id'] = $request->user()->id;
    }

    if ($data['estado'] === 'recibida') {
        $data['fecha_entrega'] = now();
    }

    $solicitudesRepuesto->update($data);

    return $solicitudesRepuesto->load('orden.vehiculo', 'mecanico', 'mensajero', 'repuesto');
}

    public function destroy(SolicitudRepuesto $solicitudesRepuesto)
    {
        $solicitudesRepuesto->delete();

        return response()->json(null, 204);
    }
}