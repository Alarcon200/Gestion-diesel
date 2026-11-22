<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notificacion;
use App\Models\Usuario;

/**
 * Campana de notificaciones del encabezado. Se actualiza sola cada 15 segundos.
 */
class Notificaciones extends Component
{
    public bool $abierto = false;
    public int $ultimoId = 0;

    public function mount(): void
    {
        // Lo que ya existe al cargar la página no se anuncia como "nuevo"
        $this->ultimoId = (int) Notificacion::where('usuario_id', (int) session('usuario_id'))->max('id');
    }

    public function alternar(): void
    {
        $this->abierto = !$this->abierto;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
    }

    // Se ejecuta con cada actualización automática: avisa si llegó algo nuevo
    public function revisar()
    {
        // Si el usuario fue eliminado o desactivado, se le cierra la sesión
        $sigueActivo = Usuario::where('id', (int) session('usuario_id'))->where('activo', true)->exists();
        if (!$sigueActivo) {
            session()->flush();
            return $this->redirect('/login');
        }

        $nuevas = Notificacion::where('usuario_id', (int) session('usuario_id'))
            ->where('id', '>', $this->ultimoId)
            ->orderBy('id')
            ->get();

        if ($nuevas->isEmpty()) {
            return null;
        }

        $ultima = $nuevas->last();
        $this->ultimoId = (int) $ultima->id;

        $this->dispatch(
            'nueva-notificacion',
            titulo: $nuevas->count() > 1 ? $nuevas->count() . ' notificaciones nuevas' : $ultima->titulo,
            mensaje: $nuevas->count() > 1 ? $ultima->titulo : (string) $ultima->mensaje
        );

        return null;
    }

    public function abrirNotificacion($id)
    {
        Notificacion::where('id', $id)
            ->where('usuario_id', (int) session('usuario_id'))
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);

        return $this->redirect('/panel');
    }

    public function marcarTodas(): void
    {
        Notificacion::where('usuario_id', (int) session('usuario_id'))
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);
    }

    public function render()
    {
        $uid = (int) session('usuario_id');

        return view('livewire.notificaciones', [
            'noLeidas' => Notificacion::where('usuario_id', $uid)->whereNull('leida_at')->count(),
            'lista' => $this->abierto
                ? Notificacion::where('usuario_id', $uid)->orderByDesc('id')->limit(15)->get()
                : collect(),
        ]);
    }
}
