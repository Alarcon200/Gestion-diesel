<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario; // Importamos tu modelo de usuarios
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validamos que el celular nos envíe correo y contraseña
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Buscamos al usuario en la base de datos
        $usuario = Usuario::where('email', $request->email)->first();

        // 3. Verificamos si existe y si la contraseña coincide
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'mensaje' => 'El correo o la contraseña son incorrectos.'
            ], 401);
        }

        // 4. Si todo está bien, le creamos su Token de acceso
        $token = $usuario->createToken('app-mecanico')->plainTextToken;

        // 5. Le devolvemos al celular el Token y los datos del mecánico
        return response()->json([
            'mensaje' => '¡Bienvenido!',
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'rol_id' => $usuario->rol_id,
            ]
        ], 200);
    }
}