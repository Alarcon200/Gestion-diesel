<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orden; // Importamos el modelo de las órdenes

class TrabajoController extends Controller
{
    public function misTrabajos(Request $request)
    {
        // 1. Identificamos qué mecánico está haciendo la petición (gracias al Token)
        $mecanico_id = $request->user()->id;

        // 2. Buscamos en la base de datos las órdenes que tiene asignadas
        $trabajos = Orden::where('mecanico_id', $mecanico_id)
                        ->with('vehiculo') // Traemos también los datos del camión (placa, marca, etc)
                        ->orderBy('created_at', 'desc')
                        ->get();

        // 3. Le devolvemos la lista al celular
        return response()->json([
            'mensaje' => 'Trabajos recuperados con éxito',
            'cantidad' => $trabajos->count(),
            'trabajos' => $trabajos
        ], 200);
    }   
}