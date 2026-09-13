<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Ingresos</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nuevo ingreso
        </button>
    </div>

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
                @if($vehiculos->isEmpty())
                    <p class="text-gray-500 text-xs mt-1">No hay vehiculos registrados. Crea uno primero en la seccion Vehiculos.</p>
                @endif
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
                    <th class="px-4 py-2">Vehiculo</th>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Conductor</th>
                    <th class="px-4 py-2">Motivo</th>
                    <th class="px-4 py-2">Fecha</th>
                    <th class="px-4 py-2">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingresos as $ingreso)
                <tr class="border-t">
                    <td class="px-4 py-2 font-medium">{{ $ingreso->vehiculo->placa ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $ingreso->vehiculo->cliente->nombre ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $ingreso->conductor }}</td>
                    <td class="px-4 py-2">{{ $ingreso->motivo }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($ingreso->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $ingreso->estado }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay ingresos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>