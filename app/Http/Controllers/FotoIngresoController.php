<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fotografia;
use App\Models\Ingreso;
use App\Models\Inspeccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Fotos de un vehículo que ingresó al taller, cada una con su nota.
 * No hay endpoint para borrar: las fotos son la evidencia del estado del vehículo.
 */
class FotoIngresoController extends Controller
{
    // Fotos del ingreso + las que se tomaron en su inspección de ingreso
    public function index(Request $request, Ingreso $ingreso)
    {
        $inspeccionIds = $ingreso->inspecciones()->pluck('id');

        $fotos = Fotografia::where(function ($q) use ($ingreso) {
                $q->where('fotografiable_type', Ingreso::class)
                  ->where('fotografiable_id', $ingreso->id);
            })
            ->orWhere(function ($q) use ($inspeccionIds) {
                $q->where('fotografiable_type', Inspeccion::class)
                  ->whereIn('fotografiable_id', $inspeccionIds);
            })
            ->orderByDesc('fecha_subida')
            ->orderByDesc('id')
            ->get();

        return response()->json($fotos->map(fn ($f) => $this->formatear($request, $f))->values());
    }

    public function store(Request $request, Ingreso $ingreso)
    {
        $request->validate([
            'foto' => 'required|image|max:10240',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $ruta = $request->file('foto')->store('fotografias', 'public');

        $datos = [
            'fotografiable_id' => $ingreso->id,
            'fotografiable_type' => Ingreso::class,
            'ruta_archivo' => $ruta,
            'descripcion' => $request->input('descripcion') ?: null,
            'fecha_subida' => now(),
        ];

        // Solo si ya se corrió la migración que agrega la columna
        if (Schema::hasColumn('fotografias', 'usuario_id')) {
            $datos['usuario_id'] = $request->user()->id;
        }

        $foto = Fotografia::create($datos);

        return response()->json($this->formatear($request, $foto), 201);
    }

    private function formatear(Request $request, Fotografia $f): array
    {
        $autor = null;
        if ($f->usuario_id ?? null) {
            $u = \App\Models\Usuario::find($f->usuario_id);
            $autor = $u ? trim($u->nombre . ' ' . ($u->apellido ?? '')) : null;
        }

        return [
            'id' => $f->id,
            // La URL usa el mismo servidor por el que llegó la petición (sirve desde el celular)
            'url' => $request->getSchemeAndHttpHost() . '/storage/' . ltrim($f->ruta_archivo, '/'),
            'descripcion' => $f->descripcion,
            'fecha_subida' => $f->fecha_subida,
            'origen' => $f->fotografiable_type === Inspeccion::class ? 'Inspección' : 'Ingreso',
            'autor' => $autor,
        ];
    }
}
