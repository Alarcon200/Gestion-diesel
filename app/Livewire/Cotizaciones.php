<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;

class Cotizaciones extends Component
{
    public $orden_id = '';
    public $items = [];
    public $editando_id = null;
    public $mostrar_formulario = false;

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    private function itemVacio()
    {
        return [
            'tipo' => 'mano_obra',
            'descripcion' => '',
            'cantidad' => 1,
            'precio_unitario' => 0,
            'aplica_iva' => false,
            'iva_incluido' => false,
            'repuesto_id' => null,
        ];
    }

    public function nuevo()
    {
        $this->editando_id = null;
        $this->orden_id = '';
        $this->items = [$this->itemVacio()];
        $this->mostrar_formulario = true;
    }

    public function editar($id)
    {
        $cotizacion = Cotizacion::with('detalles')->findOrFail($id);

        $this->editando_id = $cotizacion->id;
        $this->orden_id = $cotizacion->orden_id;
        $this->items = $cotizacion->detalles->map(function ($detalle) {
            return [
                'tipo' => $detalle->tipo,
                'descripcion' => $detalle->descripcion,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'aplica_iva' => false,
                'iva_incluido' => false,
                'repuesto_id' => $detalle->repuesto_id,
            ];
        })->toArray();

        if (empty($this->items)) {
            $this->items = [$this->itemVacio()];
        }

        $this->mostrar_formulario = true;
    }

    public function agregarItem()
    {
        $this->items[] = $this->itemVacio();
    }

    public function quitarItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    private function subtotalItem($item)
    {
        $base = (float) ($item['cantidad'] ?? 0) * (float) ($item['precio_unitario'] ?? 0);

        if (!empty($item['aplica_iva']) && empty($item['iva_incluido'])) {
            return $base * 1.19;
        }

        return $base;
    }

    public function getTotalProperty()
    {
        return collect($this->items)->sum(fn($item) => $this->subtotalItem($item));
    }

    public function guardar()
    {
        $this->items = collect($this->items)
            ->filter(fn($item) => trim($item['descripcion'] ?? '') !== '')
            ->values()
            ->all();

        $this->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'items' => 'required|array|min:1',
            'items.*.tipo' => 'required|in:mano_obra,repuesto,servicio',
            'items.*.descripcion' => 'required|string|max:200',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Agrega al menos un item con descripcion antes de guardar.',
            'items.min' => 'Agrega al menos un item con descripcion antes de guardar.',
        ]);

        DB::transaction(function () {
            $subtotalManoObra = 0;
            $subtotalRepuestos = 0;

            foreach ($this->items as $item) {
                $subtotal = $this->subtotalItem($item);
                if ($item['tipo'] === 'mano_obra') {
                    $subtotalManoObra += $subtotal;
                } else {
                    $subtotalRepuestos += $subtotal;
                }
            }

            $datosCotizacion = [
                'orden_id' => $this->orden_id,
                'subtotal_mano_obra' => $subtotalManoObra,
                'subtotal_repuestos' => $subtotalRepuestos,
                'total' => $subtotalManoObra + $subtotalRepuestos,
            ];

            if ($this->editando_id) {
                $cotizacion = Cotizacion::find($this->editando_id);
                $cotizacion->update($datosCotizacion);
                $cotizacion->detalles()->delete();
            } else {
                $datosCotizacion['usuario_id'] = session('usuario_id');
                $datosCotizacion['estado'] = 'pendiente';
                $datosCotizacion['fecha_creacion'] = now();
                $cotizacion = Cotizacion::create($datosCotizacion);
            }

            foreach ($this->items as $item) {
                CotizacionDetalle::create([
                    'cotizacion_id' => $cotizacion->id,
                    'repuesto_id' => $item['repuesto_id'] ?: null,
                    'tipo' => $item['tipo'],
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $this->subtotalItem($item),
                ]);
            }
        });

        $this->mostrar_formulario = false;
        $this->editando_id = null;
    }

    public function autorizar($id)
    {
        Cotizacion::find($id)->update(['estado' => 'autorizada', 'fecha_respuesta' => now()]);
    }

    public function rechazar($id)
    {
        Cotizacion::find($id)->update(['estado' => 'rechazada', 'fecha_respuesta' => now()]);
    }

    public function cancelar()
    {
        $this->mostrar_formulario = false;
        $this->editando_id = null;
    }

    public function render()
    {
        return view('livewire.cotizaciones', [
            'cotizaciones' => Cotizacion::with('orden.vehiculo.cliente', 'usuario', 'detalles')->orderByDesc('fecha_creacion')->get(),
            'ordenes' => Orden::with('vehiculo')->orderByDesc('fecha_creacion')->get(),
        ])->layout('layouts.app');
    }
}