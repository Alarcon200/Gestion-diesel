<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Ordenes de trabajo</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nueva orden
        </button>
    </div>

    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">Nueva orden</h2>
        <form wire:submit="guardar">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Ingreso (vehiculo)</label>
                <select wire:model="ingreso_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- Selecciona un ingreso --</option>
                    @foreach($ingresos as $ingreso)
                        <option value="{{ $ingreso->id }}">
                            {{ $ingreso->vehiculo->placa ?? '-' }} - {{ $ingreso->vehiculo->cliente->nombre ?? '-' }} ({{ \Carbon\Carbon::parse($ingreso->fecha_ingreso)->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                @error('ingreso_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                @if($ingresos->isEmpty())
                    <p class="text-gray-500 text-xs mt-1">No hay ingresos disponibles sin orden asignada.</p>
                @endif
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
                    Guardar
                </button>
                <button type="button" wire:click="cancelar" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-2">Numero</th>
                    <th class="px-4 py-2">Vehiculo</th>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Fecha</th>
                    <th class="px-4 py-2">Creada por</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordenes as $orden)
                <tr class="border-t">
                    <td class="px-4 py-2 font-medium">{{ $orden->numero_orden }}</td>
                    <td class="px-4 py-2">{{ $orden->vehiculo->placa ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $orden->vehiculo->cliente->nombre ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                            @if($orden->estado === 'finalizada') bg-green-100 text-green-700
                            @elseif($orden->estado === 'pendiente') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700
                            @endif">
                            {{ ucfirst($orden->estado) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($orden->fecha_creacion)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $orden->usuario->nombre ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay ordenes registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>