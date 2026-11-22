<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // Últimas notificaciones del usuario y cuántas están sin leer
    public function index(Request $request)
    {
        $uid = $request->user()->id;

        $lista = Notificacion::where('usuario_id', $uid)
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'tipo' => $n->tipo,
                'titulo' => $n->titulo,
                'mensaje' => $n->mensaje,
                'ingreso_id' => $n->ingreso_id,
                'leida' => $n->leida_at !== null,
                'creada' => $n->created_at ? $n->created_at->toIso8601String() : null,
            ]);

        $noLeidas = Notificacion::where('usuario_id', $uid)->whereNull('leida_at')->count();

        return response()->json(['no_leidas' => $noLeidas, 'notificaciones' => $lista]);
    }

    // Solo la cuenta (para el globito de la campana)
    public function contador(Request $request)
    {
        return response()->json([
            'no_leidas' => Notificacion::where('usuario_id', $request->user()->id)->whereNull('leida_at')->count(),
        ]);
    }

    public function leerTodas(Request $request)
    {
        Notificacion::where('usuario_id', $request->user()->id)
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);

        return response()->json(['no_leidas' => 0]);
    }
}
