<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Cotizaciones</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nueva cotizacion
        </button>
    </div>

    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">{{ $editando_id ? 'Editar cotizacion' : 'Nueva cotizacion' }}</h2>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Orden (opcional)</label>
            <select wire:model.live="orden_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Cotizacion libre, sin orden todavia --</option>
                @foreach($ordenes as $orden)
                    <option value="{{ $orden->id }}">{{ $orden->numero_orden }} - {{ $orden->vehiculo->placa ?? '-' }}</option>
                @endforeach
            </select>
            @error('orden_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        @if(!$orden_id)
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded p-4">
            <label class="block text-sm font-medium mb-1">Vehiculo (el carro aun no ha ingresado al taller)</label>

            @if(!$mostrar_form_vehiculo)
                <div class="flex gap-2 items-center">
                    <select wire:model="vehiculo_id" class="flex-1 border rounded px-3 py-2">
                        <option value="">-- Selecciona un vehiculo existente --</option>
                        @foreach($vehiculos as $vehiculo)
                            <option value="{{ $vehiculo->id }}">{{ $vehiculo->placa }} - {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->cliente->nombre ?? '-' }})</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="mostrarFormVehiculo" class="text-blue-600 text-sm whitespace-nowrap hover:underline">
                        + Vehiculo nuevo
                    </button>
                </div>
                @error('vehiculo_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 mt-1">Si el vehiculo no esta en la lista, registra uno nuevo con los datos de la tarjeta de propiedad.</p>
            @else
                <div class="bg-white border rounded p-4 mt-2">
                    <h4 class="font-medium text-sm mb-3">Registrar vehiculo nuevo</h4>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Placa</label>
                            <input type="text" wire:model="nuevo_placa" class="w-full border rounded px-2 py-1 text-sm uppercase">
                            @error('nuevo_placa') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Marca</label>
                            <input type="text" wire:model="nuevo_marca" class="w-full border rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Modelo</label>
                            <input type="text" wire:model="nuevo_modelo" class="w-full border rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Año</label>
                            <input type="number" wire:model="nuevo_anio" class="w-full border rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tipo</label>
                            <input type="text" wire:model="nuevo_tipo" class="w-full border rounded px-2 py-1 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Cliente propietario</label>
                            <select wire:model="nuevo_cliente_id" class="w-full border rounded px-2 py-1 text-sm">
                                <option value="">-- Selecciona --</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                            @error('nuevo_cliente_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="button" wire:click="guardarVehiculoNuevo" class="bg-slate-800 text-white px-3 py-1 rounded text-sm hover:bg-slate-700">
                            Guardar vehiculo
                        </button>
                        <button type="button" wire:click="cancelarFormVehiculo" class="bg-gray-200 px-3 py-1 rounded text-sm hover:bg-gray-300">
                            Cancelar
                        </button>
                    </div>
                </div>
            @endif
        </div>
        @endif

        <h3 class="font-medium mb-2">Items de la cotizacion</h3>

        <div class="grid grid-cols-12 gap-2 mb-1 text-xs font-semibold text-gray-500 px-1">
            <div class="col-span-2">Tipo</div>
            <div class="col-span-3">Descripcion</div>
            <div class="col-span-1">Cant.</div>
            <div class="col-span-2">Precio unit.</div>
            <div class="col-span-2">IVA</div>
            <div class="col-span-1">Subtotal</div>
            <div class="col-span-1"></div>
        </div>

        @foreach($items as $index => $item)
        <div class="grid grid-cols-12 gap-2 mb-2 items-center border-b pb-2" wire:key="item-index-{{ $index }}">
            <div class="col-span-2">
                <select wire:model.live="items.{{ $index }}.tipo" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="mano_obra">Mano de obra</option>
                    <option value="repuesto">Repuesto</option>
                    <option value="servicio">Servicio</option>
                </select>
            </div>

            <div class="col-span-3">
                <input type="text" wire:model.live.debounce.500ms="items.{{ $index }}.descripcion" placeholder="Descripcion" class="w-full border rounded px-2 py-1 text-sm">
            </div>

            <div class="col-span-1">
                <input type="number" step="1" min="1" wire:model.live="items.{{ $index }}.cantidad" placeholder="Cant." class="w-full border rounded px-2 py-1 text-sm text-center">
            </div>

            <!-- Formateador fluido sin entangle directo para evitar lag visual -->
            <div class="col-span-2"
                 x-data="{
                    formatear(valor) {
                        if (valor === '' || valor === null || valor === undefined) return '';
                        let limpio = valor.toString().replace(/\D/g, '');
                        return limpio ? new Intl.NumberFormat('es-CO').format(limpio) : '';
                    }
                 }">
                <input type="text"
                       inputmode="numeric"
                       placeholder="Precio unitario"
                       class="w-full border rounded px-2 py-1 text-sm text-right"
                       x-init="$el.value = formatear(@js($item['precio_unitario'] ?? 0))"
                       x-on:input="
                           let crudo = $event.target.value.replace(/\D/g, '');
                           $event.target.value = formatear(crudo);
                           $wire.set('items.{{ $index }}.precio_unitario', crudo ? parseInt(crudo, 10) : 0);
                       ">
                @if(($item['aplica_iva'] ?? false) && ($item['iva_incluido'] ?? false) && ($item['precio_unitario'] ?? 0) > 0)
                    <p class="text-xs text-gray-500 mt-1">
                        Sin IVA: ${{ number_format(($item['precio_unitario'] ?? 0) / 1.19, 0, ',', '.') }}
                    </p>
                @endif
            </div>

            <div class="col-span-2 flex flex-col gap-1 text-xs">
                <label class="flex items-center gap-1">
                    <input type="checkbox" wire:model.live="items.{{ $index }}.aplica_iva">
                    Aplica IVA (19%)
                </label>
                @if($item['aplica_iva'] ?? false)
                <label class="flex items-center gap-1">
                    <input type="checkbox" wire:model.live="items.{{ $index }}.iva_incluido">
                    Ya incluido en el precio
                </label>
                @endif
            </div>

            <div class="col-span-1 text-sm font-medium">
                ${{ number_format(((float)($item['cantidad'] ?? 0) * (float)($item['precio_unitario'] ?? 0)) * ((!empty($item['aplica_iva']) && empty($item['iva_incluido'])) ? 1.19 : 1), 0, ',', '.') }}
            </div>

            <div class="col-span-1">
                <button type="button" wire:click="quitarItem({{ $index }})" class="text-red-600 text-xs hover:underline">Quitar</button>
            </div>
        </div>
        @endforeach
        @error('items') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror

        <button type="button" wire:click="agregarItem" class="text-blue-600 text-sm hover:underline mb-4">+ Agregar item</button>

        <div class="text-right font-semibold mb-4 text-lg">
            Total: ${{ number_format($this->total, 0, ',', '.') }}
        </div>

        <div class="flex gap-2">
            <button wire:click="guardar" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
                {{ $editando_id ? 'Actualizar cotizacion' : 'Guardar cotizacion' }}
            </button>
            <button type="button" wire:click="cancelar" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                Cancelar
            </button>
        </div>
    </div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-2">Orden</th>
                    <th class="px-4 py-2">Vehiculo</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cotizaciones as $cotizacion)
                <tr class="border-t" wire:key="cotizacion-{{ $cotizacion->id }}">
                    <td class="px-4 py-2 font-medium">{{ $cotizacion->orden->numero_orden ?? '(sin orden)' }}</td>
                    <td class="px-4 py-2">
                        {{ $cotizacion->orden?->vehiculo?->placa ?? $cotizacion->vehiculo?->placa ?? '-' }}
                    </td>
                    <td class="px-4 py-2">${{ number_format($cotizacion->total ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                            @if($cotizacion->estado === 'autorizada') bg-green-100 text-green-700
                            @elseif($cotizacion->estado === 'rechazada') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700
                            @endif">
                            {{ ucfirst($cotizacion->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 flex gap-2 items-center flex-wrap">
                        <button wire:click="editar({{ $cotizacion->id }})" class="text-blue-600 hover:underline text-xs">Editar</button>
                        @if(!$cotizacion->orden_id)
                            <button wire:click="mostrarVincular({{ $cotizacion->id }})" class="text-purple-600 hover:underline text-xs">Vincular a orden</button>
                        @endif
                        @if($cotizacion->estado === 'pendiente')
                            <button wire:click="autorizar({{ $cotizacion->id }})" class="text-green-600 hover:underline text-xs">Autorizar</button>
                            <button wire:click="rechazar({{ $cotizacion->id }})" class="text-red-600 hover:underline text-xs">Rechazar</button>
                        @endif
                        <a href="/cotizaciones/{{ $cotizacion->id }}/pdf" target="_blank" class="text-blue-600 hover:underline text-xs">PDF</a>
                    </td>
                </tr>

                @if($vinculando_id === $cotizacion->id)
                <tr class="bg-purple-50" wire:key="vincular-{{ $cotizacion->id }}">
                    <td colspan="5" class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <select wire:model="orden_a_vincular" class="border rounded px-2 py-1 text-sm">
                                <option value="">-- Selecciona la orden --</option>
                                @foreach($ordenes as $orden)
                                    <option value="{{ $orden->id }}">{{ $orden->numero_orden }} - {{ $orden->vehiculo->placa ?? '-' }}</option>
                                @endforeach
                            </select>
                            <button wire:click="vincular" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">Confirmar</button>
                            <button wire:click="cancelarVincular" class="bg-gray-200 px-3 py-1 rounded text-sm hover:bg-gray-300">Cancelar</button>
                            @error('orden_a_vincular') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay cotizaciones registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>