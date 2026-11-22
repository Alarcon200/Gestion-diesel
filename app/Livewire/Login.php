<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $error = '';

    public function ingresar()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // El correo se compara sin importar mayúsculas o minúsculas
        $usuario = Usuario::with('rol')
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($this->email))])
            ->first();

        if (!$usuario || !Hash::check($this->password, $usuario->password)) {
            $this->error = 'Credenciales incorrectas.';
            return;
        }

        if (!$usuario->activo) {
            $this->error = 'Este usuario está desactivado.';
            return;
        }

        if (!$usuario->rol) {
            $this->error = 'El usuario no tiene un rol asignado.';
            return;
        }

        session()->regenerate();

        session([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre . ' ' . $usuario->apellido,
            'usuario_rol' => trim($usuario->rol->nombre),
        ]);

        return redirect('/panel');
    }

    public function render()
    {
        return view('livewire.login')->layout('layouts.app');
    }
}
