<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$rolesPermitidos): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $rolUsuario = $usuario->rol->nombre ?? null;

        if (!in_array($rolUsuario, $rolesPermitidos)) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta accion.',
            ], 403);
        }

        return $next($request);
    }
}