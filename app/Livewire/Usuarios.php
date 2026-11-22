<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orden;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Usuarios extends Component
{
    public $nombre, $apellido, $email, $password, $rol;
    public $mostrar_formulario = false;

    public function mount()
    {
        if (!session('usuario_id') || session('usuario_rol') !== 'Administrador') {
            return redirect('/panel');
        }
    }

    private function soloAdministrador(): void
    {
        if (session('usuario_rol') !== 'Administrador') {
            abort(403);
        }
    }

    private function esUltimoAdministrador(Usuario $usuario): bool
    {
        $esAdmin = optional($usuario->rol)->nombre === 'Administrador';
        if (!$esAdmin) {
            return false;
        }

        $activos = Usuario::where('activo', true)
            ->whereHas('rol', fn ($q) => $q->where('nombre', 'Administrador'))
            ->count();

        return $activos <= 1;
    }

    public function render()
    {
        return view('livewire.usuarios', [
            'usuarios' => Usuario::with('rol')->orderBy('nombre')->get(),
            // Los roles salen de la base de datos, así el rol elegido siempre es el correcto
            'roles' => Rol::orderBy('id')->get(),
        ])->layout('layouts.app');
    }

    public function nuevo()
    {
        $this->soloAdministrador();
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
        $this->soloAdministrador();

        $this->validate([
            'nombre' => 'required|string|max:80',
            'apellido' => 'required|string|max:80',
            'email' => 'required|email|max:120|unique:usuarios,email',
            'password' => 'required|min:6',
            'rol' => 'required|exists:roles,id',
        ]);

        Usuario::create([
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => trim($this->email),
            'password' => Hash::make($this->password),
            'rol_id' => $this->rol,
            'activo' => true,
        ]);

        $this->limpiarCampos();
        $this->mostrar_formulario = false;

        session()->flash('mensaje', 'Usuario creado exitosamente.');
    }

    // Quita o devuelve el acceso sin borrar el historial del usuario
    public function alternarActivo($id)
    {
        $this->soloAdministrador();

        $usuario = Usuario::with('rol')->find($id);
        if (!$usuario) {
            return;
        }

        if ((int) $usuario->id === (int) session('usuario_id')) {
            session()->flash('error', 'No puedes desactivar tu propio usuario.');
            return;
        }

        if ($usuario->activo && $this->esUltimoAdministrador($usuario)) {
            session()->flash('error', 'No se puede desactivar al único administrador.');
            return;
        }

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        // Si se desactiva, también se cierra su sesión en la app del celular
        if (!$usuario->activo) {
            $usuario->tokens()->delete();
        }

        session()->flash('mensaje', $usuario->activo
            ? $usuario->nombre . ' ya puede entrar de nuevo al sistema.'
            : $usuario->nombre . ' fue desactivado y ya no puede entrar.');
    }

    public function eliminar($id)
    {
        $this->soloAdministrador();

        $usuario = Usuario::with('rol')->find($id);
        if (!$usuario) {
            return;
        }

        if ((int) $usuario->id === (int) session('usuario_id')) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            return;
        }

        if ($this->esUltimoAdministrador($usuario)) {
            session()->flash('error', 'No se puede eliminar al único administrador.');
            return;
        }

        $mensajeConHistorial = $usuario->nombre . ' tiene trabajos, ingresos o informes registrados y no se puede eliminar sin perder ese historial. Usa "Desactivar" para quitarle el acceso.';

        // El mecánico no tiene llave foránea, así que se revisa a mano
        if (Orden::where('mecanico_id', $usuario->id)->exists()) {
            session()->flash('error', $mensajeConHistorial);
            return;
        }

        try {
            DB::transaction(function () use ($usuario) {
                $usuario->tokens()->delete();
                $usuario->delete();
            });
        } catch (QueryException $e) {
            // Tiene registros que dependen de él (ingresos, inspecciones, etc.)
            session()->flash('error', $mensajeConHistorial);
            return;
        }

        session()->flash('mensaje', 'Usuario eliminado: ' . $usuario->nombre . ' ' . $usuario->apellido . '.');
    }

    public function limpiarCampos()
    {
        $this->nombre = '';
        $this->apellido = '';
        $this->email = '';
        $this->password = '';
        $this->rol = '';
        $this->resetValidation();
    }
}
