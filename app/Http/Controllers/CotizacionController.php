<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use Illuminate\Support\Facades\DB;

class CotizacionController extends Controller
{
    public function index()
    {
        return Cotizacion::with('orden.vehiculo.cliente', 'usuario', 'detalles.repuesto')
            ->orderByDesc('fecha_creacion')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'items' => 'required|array|min:1',
            'items.*.tipo' => 'required|in:mano_obra,repuesto,servicio',
            'items.*.descripcion' => 'required|string|max:200',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.repuesto_id' => 'nullable|exists:repuestos,id',
        ]);

        $cotizacion = DB::transaction(function () use ($data, $request) {
            $subtotalManoObra = 0;
            $subtotalRepuestos = 0;

            foreach ($data['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                if ($item['tipo'] === 'mano_obra') {
                    $subtotalManoObra += $subtotal;
                } else {
                    $subtotalRepuestos += $subtotal;
                }
            }

            $cotizacion = Cotizacion::create([
                'orden_id' => $data['orden_id'],
                'usuario_id' => $request->user()->id,
                'subtotal_mano_obra' => $subtotalManoObra,
                'subtotal_repuestos' => $subtotalRepuestos,
                'total' => $subtotalManoObra + $subtotalRepuestos,
                'estado' => 'pendiente',
                'fecha_creacion' => now(),
            ]);

            foreach ($data['items'] as $item) {
                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'repuesto_id' => $item['repuesto_id'] ?? null,
                    'tipo' => $item['tipo'],
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                ]);
            }

            return $cotizacion;
        });

        return response()->json($cotizacion->load('detalles.repuesto', 'orden.vehiculo.cliente'), 201);
    }

    public function show(Cotizacion $cotizacion)
    {
        return $cotizacion->load('detalles.repuesto', 'orden.vehiculo.cliente', 'usuario');
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $data = $request->validate([
            'estado' => 'required|in:pendiente,autorizada,rechazada',
        ]);

        $data['fecha_respuesta'] = now();

        $cotizacion->update($data);

        return $cotizacion;
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();

        return response()->json(null, 204);
    }
}