<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Vehiculo;
use App\Models\Cliente;

class Vehiculos extends Component
{
    public $placa = '';
    public $marca = '';
    public $modelo = '';
    public $anio = '';
    public $tipo = '';
    public $kilometraje = '';
    public $cliente_id = '';
    public $editando_id = null;
    public $mostrar_formulario = false;

    protected $rules = [
        'placa' => 'required|string|max:15',
        'marca' => 'nullable|string|max:50',
        'modelo' => 'nullable|string|max:50',
        'anio' => 'nullable|integer',
        'tipo' => 'nullable|string|max:50',
        'kilometraje' => 'nullable|integer',
        'cliente_id' => 'required|exists:clientes,id',
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

        $data = [
            'placa' => $this->placa,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio ?: null,
            'tipo' => $this->tipo,
            'kilometraje' => $this->kilometraje ?: null,
            'cliente_id' => $this->cliente_id,
        ];

        if ($this->editando_id) {
            Vehiculo::find($this->editando_id)->update($data);
        } else {
            Vehiculo::create($data);
        }

        $this->resetForm();
        $this->mostrar_formulario = false;
    }

    public function editar($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $this->editando_id = $vehiculo->id;
        $this->placa = $vehiculo->placa;
        $this->marca = $vehiculo->marca;
        $this->modelo = $vehiculo->modelo;
        $this->anio = $vehiculo->anio;
        $this->tipo = $vehiculo->tipo;
        $this->kilometraje = $vehiculo->kilometraje;
        $this->cliente_id = $vehiculo->cliente_id;
        $this->mostrar_formulario = true;
    }

    public function eliminar($id)
    {
        Vehiculo::find($id)->delete();
    }

    public function cancelar()
    {
        $this->resetForm();
        $this->mostrar_formulario = false;
    }

    private function resetForm()
    {
        $this->reset(['placa', 'marca', 'modelo', 'anio', 'tipo', 'kilometraje', 'cliente_id', 'editando_id']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.vehiculos', [
            'vehiculos' => Vehiculo::with('cliente')->orderBy('placa')->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
        ])->layout('layouts.app');
    }
}