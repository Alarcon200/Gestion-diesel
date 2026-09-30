<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InformeServicio;
use App\Models\Orden;

class InformeServicioController extends Controller
{
    public function index()
    {
        return InformeServicio::with('orden.vehiculo.cliente', 'usuario')
            ->orderByDesc('fecha_generacion')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ingreso_id' => 'required|exists:ingresos,id',
            'valor_servicio' => 'nullable|numeric',
            'observaciones_finales' => 'nullable|string',
        ]);

        $orden = Orden::obtenerOCrearParaIngreso($data['ingreso_id'], $request->user()->id);
        $orden->load('diagnosticos', 'reparaciones.fotografias', 'pruebas');

        $informe = InformeServicio::create([
            'orden_id' => $orden->id,
            'usuario_id' => $request->user()->id,
            'valor_servicio' => $data['valor_servicio'] ?? 0,
            'observaciones_finales' => $data['observaciones_finales'] ?? null,
            'fecha_generacion' => now(),
        ]);

        $orden->update(['estado' => 'finalizada', 'fecha_finalizacion' => now()]);

        return response()->json([
            'informe' => $informe,
            'resumen' => [
                'vehiculo' => $orden->vehiculo->placa ?? null,
                'cliente' => $orden->vehiculo->cliente->nombre ?? null,
                'diagnosticos' => $orden->diagnosticos,
                'reparaciones' => $orden->reparaciones,
                'pruebas' => $orden->pruebas,
            ],
        ], 201);
    }

    public function show(InformeServicio $informesServicio)
    {
        $orden = $informesServicio->orden()->with(
            'vehiculo.cliente',
            'diagnosticos',
            'reparaciones.fotografias',
            'pruebas'
        )->first();

        return [
            'informe' => $informesServicio->load('usuario'),
            'resumen' => $orden,
        ];
    }

    public function pdf(InformeServicio $informesServicio)
    {
        $orden = $informesServicio->orden()->with(
            'vehiculo.cliente',
            'diagnosticos',
            'reparaciones.fotografias',
            'reparaciones.mecanico',
            'pruebas'
        )->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.informe', [
            'informe' => $informesServicio,
            'orden' => $orden,
        ]);

        return $pdf->download('informe-' . $orden->numero_orden . '.pdf');
    }

    public function destroy(InformeServicio $informesServicio)
    {
        $informesServicio->delete();

        return response()->json(null, 204);
    }
}