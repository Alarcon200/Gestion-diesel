<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InspeccionController extends Controller
{
    public function store(Request $request)
    {
        // DB::beginTransaction nos asegura que ambas tablas se guarden juntas
        DB::beginTransaction();

        try {
            // 1. Guardar en la tabla maestra (inspecciones)
            $inspeccionId = DB::table('inspecciones')->insertGetId([
                'ingreso_id' => 1, // Temporal: ID de ingreso simulado
                'usuario_id' => 1, // Temporal: El ID de tu usuario admin
                'fecha_inspeccion' => Carbon::now(),
                'estado_general' => 'En revisión',
                'nivel_combustible' => 'No reportado', // No lo tenemos en Flutter aún
                'kilometraje' => 0, // No lo tenemos en Flutter aún
                'observaciones' => $request->novedades,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 2. Preparamos los 4 estados que vienen de Flutter para la tabla de detalles
            $detalles = [
                ['item' => 'Exterior', 'estado' => $request->estado_exterior],
                ['item' => 'Interior', 'estado' => $request->estado_interior],
                ['item' => 'Llantas', 'estado' => $request->estado_llantas],
                ['item' => 'Mecánica', 'estado' => $request->estado_mecanica],
            ];

            // 3. Guardar cada estado como una fila nueva en inspeccion_detalle
            foreach ($detalles as $detalle) {
                DB::table('inspeccion_detalle')->insert([
                    'inspeccion_id' => $inspeccionId,
                    'item' => $detalle['item'],
                    'estado' => $detalle['estado'],
                    'observacion' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Inspección registrada con éxito',
                'inspeccion_id' => $inspeccionId
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Hubo un problema al guardar',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}