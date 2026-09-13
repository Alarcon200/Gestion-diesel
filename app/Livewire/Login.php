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

        $usuario = Usuario::where('email', $this->email)->first();

        if (!$usuario || !Hash::check($this->password, $usuario->password)) {
            $this->error = 'Credenciales incorrectas.';
            return;
        }

        if (!$usuario->activo) {
            $this->error = 'Este usuario esta desactivado.';
            return;
        }

        session([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre . ' ' . $usuario->apellido,
            'usuario_rol' => $usuario->rol->nombre,
        ]);

        return redirect('/panel');
    }

    public function render()
    {
        return view('livewire.login')->layout('layouts.app');
    }
}