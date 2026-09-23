<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Usuario; 
use Illuminate\Support\Facades\Hash;

class Usuarios extends Component
{
    // 1. Agregamos $apellido aquí arriba
    public $nombre, $apellido, $email, $password, $rol;
    public $mostrar_formulario = false;

    public function mount()
    {
        if (!session('usuario_id') || session('usuario_rol') !== 'Administrador') {
            return redirect('/panel');
        }
    }

    public function render()
    {
        $usuarios = Usuario::all(); 
        return view('livewire.usuarios', compact('usuarios'))->layout('layouts.app');
    }

    public function nuevo()
    {
        $this->limpiarCampos();
        $this->mostrar_formulario = true;
    }

    public function cancelar()
    {
        $this->limpiarCampos();
        $this->mostrar_formulario = false;
    }

    public function guardar()
    {
        // 2. Agregamos la validación del apellido
        $this->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255', 
            'email' => 'required|email|unique:usuarios,email', 
            'password' => 'required|min:6',
            'rol' => 'required'
        ]);

        // 3. Lo mandamos a guardar en la base de datos
        Usuario::create([
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'password' => Hash::make($this->password), 
            'rol_id' => $this->rol, // <-- Cambiamos 'rol' por 'rol_id'
        ]);

        $this->limpiarCampos();
        $this->mostrar_formulario = false;
        
        session()->flash('mensaje', 'Usuario creado exitosamente.');
    }

    public function limpiarCampos()
    {
        $this->nombre = '';
        $this->apellido = ''; // Limpiamos el campo
        $this->email = '';
        $this->password = '';
        $this->rol = '';
    }
}