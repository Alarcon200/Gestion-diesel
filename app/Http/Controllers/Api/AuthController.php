<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function datosUsuario(Usuario $usuario): array
    {
        $usuario->loadMissing('rol');

        return [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'rol_id' => $usuario->rol_id,
            // La app móvil lee usuario.rol.nombre para decidir qué opciones mostrar
            'rol' => [
                'id' => $usuario->rol?->id,
                'nombre' => $usuario->rol ? trim($usuario->rol->nombre) : null,
            ],
        ];
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::with('rol')
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($request->email))])
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'mensaje' => 'El correo o la contraseña son incorrectos.',
            ], 401);
        }

        if (!$usuario->activo) {
            return response()->json([
                'mensaje' => 'Este usuario está desactivado.',
            ], 403);
        }

        $token = $usuario->createToken('app-movil')->plainTextToken;

        return response()->json([
            'mensaje' => '¡Bienvenido!',
            'token' => $token,
            'usuario' => $this->datosUsuario($usuario),
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json($this->datosUsuario($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }
}
