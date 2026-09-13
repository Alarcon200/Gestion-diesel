<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cliente;

class Clientes extends Component
{
    public $nombre = '';
    public $doc_nit = '';
    public $telefono = '';
    public $email = '';
    public $direccion = '';
    public $editando_id = null;
    public $mostrar_formulario = false;

    protected $rules = [
        'nombre' => 'required|string|max:120',
        'doc_nit' => 'required|string|max:30',
        'telefono' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:120',
        'direccion' => 'nullable|string|max:200',
    ];

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function nuevo()
    {
        $this->resetForm();
        $this->mostrar_formulario = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->editando_id) {
            Cliente::find($this->editando_id)->update([
                'nombre' => $this->nombre,
                'doc_nit' => $this->doc_nit,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
            ]);
        } else {
            Cliente::create([
                'nombre' => $this->nombre,
                'doc_nit' => $this->doc_nit,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
            ]);
        }

        $this->resetForm();
        $this->mostrar_formulario = false;
    }

    public function editar($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->editando_id = $cliente->id;
        $this->nombre = $cliente->nombre;
        $this->doc_nit = $cliente->doc_nit;
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email;
        $this->direccion = $cliente->direccion;
        $this->mostrar_formulario = true;
    }

    public function eliminar($id)
    {
        Cliente::find($id)->delete();
    }

    public function cancelar()
    {
        $this->resetForm();
        $this->mostrar_formulario = false;
    }

    private function resetForm()
    {
        $this->reset(['nombre', 'doc_nit', 'telefono', 'email', 'direccion', 'editando_id']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clientes', [
            'clientes' => Cliente::orderBy('nombre')->get(),
        ])->layout('layouts.app');
    }
}