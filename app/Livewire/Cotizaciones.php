<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Orden;
use App\Models\Vehiculo;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class Cotizaciones extends Component
{
    // Propiedades de la cotización
    public $orden_id = '';
    public $vehiculo_id = '';
    public $items = [];
    public $editando_id = null;
    public $mostrar_formulario = false;
    public $vinculando_id = null;
    public $orden_a_vincular = '';

    // Propiedades para el formulario modal/desplegable de vehículos
    public $mostrar_form_vehiculo = false;
    public $nuevo_placa = '';
    public $nuevo_marca = '';
    public $nuevo_modelo = '';
    public $nuevo_anio = '';
    public $nuevo_tipo = '';
    public $nuevo_cliente_id = '';

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
        $this->vehiculo_id = '';
        $this->mostrar_form_vehiculo = false;
        $this->items = [$this->itemVacio()];
        $this->mostrar_formulario = true;
    }

    public function editar($id)
    {
        $cotizacion = Cotizacion::with('detalles')->findOrFail($id);

        $this->editando_id = $cotizacion->id;
        $this->orden_id = $cotizacion->orden_id ?? '';
        $this->vehiculo_id = $cotizacion->vehiculo_id ?? '';
        $this->mostrar_form_vehiculo = false;
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

    public function mostrarFormVehiculo()
    {
        $this->reset(['nuevo_placa', 'nuevo_marca', 'nuevo_modelo', 'nuevo_anio', 'nuevo_tipo', 'nuevo_cliente_id']);
        $this->mostrar_form_vehiculo = true;
    }

    public function cancelarFormVehiculo()
    {
        $this->mostrar_form_vehiculo = false;
    }

    public function guardarVehiculoNuevo()
    {
        $this->validate([
            'nuevo_placa' => 'required|string|max:15|unique:vehiculos,placa',
            'nuevo_marca' => 'nullable|string|max:50',
            'nuevo_modelo' => 'nullable|string|max:50',
            'nuevo_anio' => 'nullable|integer',
            'nuevo_tipo' => 'nullable|string|max:50',
            'nuevo_cliente_id' => 'required|exists:clientes,id',
        ]);

        $vehiculo = Vehiculo::create([
            'placa' => strtoupper($this->nuevo_placa),
            'marca' => $this->nuevo_marca,
            'modelo' => $this->nuevo_modelo,
            'anio' => $this->nuevo_anio ?: null,
            'tipo' => $this->nuevo_tipo,
            'cliente_id' => $this->nuevo_cliente_id,
        ]);

        $this->vehiculo_id = $vehiculo->id;
        $this->mostrar_form_vehiculo = false;
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
            'orden_id' => 'nullable|exists:ordenes,id',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
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
                'orden_id' => $this->orden_id ?: null,
                'vehiculo_id' => $this->orden_id ? null : ($this->vehiculo_id ?: null),
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

    public function mostrarVincular($id)
    {
        $this->vinculando_id = $id;
        $this->orden_a_vincular = '';
    }

    public function vincular()
    {
        $this->validate([
            'orden_a_vincular' => 'required|exists:ordenes,id',
        ]);

        Cotizacion::find($this->vinculando_id)->update([
            'orden_id' => $this->orden_a_vincular,
            'vehiculo_id' => null,
        ]);

        $this->vinculando_id = null;
        $this->orden_a_vincular = '';
    }

    public function cancelarVincular()
    {
        $this->vinculando_id = null;
        $this->orden_a_vincular = '';
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
            'cotizaciones' => Cotizacion::with('orden.vehiculo.cliente', 'vehiculo.cliente', 'usuario', 'detalles')->orderByDesc('fecha_creacion')->get(),
            'ordenes' => Orden::with('vehiculo')->orderByDesc('fecha_creacion')->get(),
            'vehiculos' => Vehiculo::with('cliente')->orderBy('placa')->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
        ])->layout('layouts.app');
    }
}