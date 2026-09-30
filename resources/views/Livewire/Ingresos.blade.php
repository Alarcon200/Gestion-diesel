
<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Ingresos</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nuevo ingreso
        </button>
    </div>

    @if(session('mensaje_estado'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-sm">
            {{ session('mensaje_estado') }}
        </div>
    @endif

    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">Nuevo ingreso</h2>
        <form wire:submit="guardar" class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Vehiculo</label>
                <select wire:model="vehiculo_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- Selecciona un vehiculo --</option>
                    @foreach($vehiculos as $vehiculo)
                        <option value="{{ $vehiculo->id }}">
                            {{ $vehiculo->placa }} - {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->cliente->nombre ?? '-' }})
                        </option>
                    @endforeach
                </select>
                @error('vehiculo_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Conductor</label>
                <input type="text" wire:model="conductor" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Fecha y hora de ingreso</label>
                <input type="datetime-local" wire:model="fecha_ingreso" class="w-full border rounded px-3 py-2">
                @error('fecha_ingreso') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Motivo de ingreso</label>
                <input type="text" wire:model="motivo" class="w-full border rounded px-3 py-2">
                @error('motivo') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kilometraje</label>
                <input type="number" wire:model="kilometraje" class="w-full border rounded px-3 py-2">
            </div>
            <div class="col-span-2 flex gap-2">
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">Guardar</button>
                <button type="button" wire:click="cancelar" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancelar</button>
            </div>
        </form>
    </div>
    @endif

    <div class="bg-white p-4 rounded shadow mb-4">
        <div class="grid grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium mb-1">Placa</label>
                <input type="text" wire:model.live.debounce.400ms="filtro_placa" class="w-full border rounded px-2 py-1 text-sm" placeholder="Ej: ABC123">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Numero de orden</label>
                <input type="text" wire:model.live.debounce.400ms="filtro_orden" class="w-full border rounded px-2 py-1 text-sm" placeholder="Ej: ST1234">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Cliente</label>
                <input type="text" wire:model.live.debounce.400ms="filtro_cliente" class="w-full border rounded px-2 py-1 text-sm" placeholder="Nombre del cliente">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Desde</label>
                <input type="date" wire:model.live="filtro_fecha_desde" class="w-full border rounded px-2 py-1 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Hasta</label>
                <input type="date" wire:model.live="filtro_fecha_hasta" class="w-full border rounded px-2 py-1 text-sm">
            </div>
        </div>
        <button wire:click="limpiarFiltros" class="text-blue-600 text-xs hover:underline mt-2">Limpiar filtros</button>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-2">Vehiculo</th>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Orden</th>
                    <th class="px-4 py-2">Fecha ingreso</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Cambiar estado</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingresos as $ingreso)
                <tr class="border-t">
                    <td class="px-4 py-2 font-medium">{{ $ingreso->vehiculo->placa ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $ingreso->vehiculo->cliente->nombre ?? '-' }}</td>
                    <td class="px-4 py-2">
                        @if($ingreso->ordenes->first()?->numero_orden)
                            <span class="font-mono text-xs">{{ $ingreso->ordenes->first()->numero_orden }}</span>
                        @else
                            <button wire:click="mostrarAsignarOrden({{ $ingreso->id }})" class="text-blue-600 hover:underline text-xs">Ingresar numero de orden</button>
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($ingreso->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs {{ $estados[$ingreso->estado]['color'] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $estados[$ingreso->estado]['label'] ?? ucfirst($ingreso->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <select
                            wire:change="cambiarEstado({{ $ingreso->id }}, $event.target.value)"
                            wire:confirm="¿Cambiar el estado de este vehiculo?"
                            class="border rounded px-2 py-1 text-xs"
                        >
                            @foreach($estados as $valor => $info)
                                <option value="{{ $valor }}" @selected($ingreso->estado === $valor)>{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-2 flex gap-2 items-center flex-wrap">
                        <button wire:click="verHistorial({{ $ingreso->id }})" class="text-slate-600 hover:underline text-xs">
                            {{ $viendo_historial_id === $ingreso->id ? 'Ocultar historial' : 'Ver historial' }}
                        </button>
                        @if($ingreso->estado === 'pendiente_informe')
                            <button wire:click="generarInforme({{ $ingreso->id }})" wire:confirm="¿Generar el informe de este vehiculo?" class="text-purple-600 hover:underline text-xs">
                                Generar informe
                            </button>
                        @endif
                        @if($ingreso->ordenes->first()?->informes->isNotEmpty())
                            <a href="/informes/{{ $ingreso->ordenes->first()->informes->last()->id }}/pdf" target="_blank" class="text-green-600 hover:underline text-xs">PDF informe</a>
                        @endif
                    </td>
                </tr>

                @if($asignando_orden_id === $ingreso->id)
                <tr class="bg-blue-50">
                    <td colspan="7" class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-sm">ST</span>
                            <input type="text" wire:model="numero_orden_sufijo" placeholder="1234" class="border rounded px-2 py-1 text-sm w-32">
                            <button wire:click="guardarNumeroOrden" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Guardar</button>
                            <button wire:click="cancelarAsignarOrden" class="bg-gray-200 px-3 py-1 rounded text-sm hover:bg-gray-300">Cancelar</button>
                            @error('numero_orden_sufijo') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </td>
                </tr>
                @endif

                @if($viendo_historial_id === $ingreso->id)
                <tr class="bg-slate-50">
                    <td colspan="7" class="px-4 py-3">
                        <p class="font-medium text-sm mb-2">Historial de cambios</p>
                        @forelse($ingreso->historial as $registro)
                            <div class="text-xs text-gray-700 mb-1 flex gap-2">
                                <span class="text-gray-400">{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y H:i') }}</span>
                                <span>
                                    @if($registro->estado_anterior)
                                        {{ $estados[$registro->estado_anterior]['label'] ?? $registro->estado_anterior }} →
                                    @endif
                                    <strong>{{ $estados[$registro->estado_nuevo]['label'] ?? $registro->estado_nuevo }}</strong>
                                </span>
                                <span class="text-gray-400">por {{ $registro->usuario->nombre ?? '-' }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500">Sin historial registrado.</p>
                        @endforelse
                    </td>
                </tr>
                @endif

                @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No hay ingresos que coincidan con la busqueda</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
