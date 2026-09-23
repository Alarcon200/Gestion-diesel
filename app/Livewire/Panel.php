<?php

namespace App\Livewire;

use Livewire\Component;

class Panel extends Component
{
    public function mount()
    {
        // Protegemos el panel para que solo entre gente logueada
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function render()
    {
        // Aquí está la magia: le decimos que cargue la vista del panel
        // y que la envuelva con tu plantilla principal (layouts.app)
        return view('livewire.panel')->layout('layouts.app');
    }
}