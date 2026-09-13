<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Vehiculos</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nuevo vehiculo
        </button>
    </div>

    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">{{ $editando_id ? 'Editar vehiculo' : 'Nuevo vehiculo' }}</h2>
        <form wire:submit="guardar" class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Placa</label>
                <input type="text" wire:model="placa" class="w-full border rounded px-3 py-2">
                @error('placa') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Marca</label>
                <input type="text" wire:model="marca" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Modelo</label>
                <input type="text" wire:model="modelo" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Año</label>
                <input type="number" wire:model="anio" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tipo</label>
                <input type="text" wire:model="tipo" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kilometraje</label>
                <input type="number" wire:model="kilometraje" class="w-full border rounded px-3 py-2">
            </div>
            <div class="col-span-3">
                <label class="block text-sm font-medium mb-1">Cliente propietario</label>
                <select wire:model="cliente_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- Selecciona un cliente --</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }} ({{ $cliente->doc_nit }})</option>
                    @endforeach
                </select>
                @error('cliente_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="col-span-3 flex gap-2">
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
                    <th class="px-4 py-2">Placa</th>
                    <th class="px-4 py-2">Marca/Modelo</th>
                    <th class="px-4 py-2">Año</th>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehiculos as $vehiculo)
                <tr class="border-t">
                    <td class="px-4 py-2 font-medium">{{ $vehiculo->placa }}</td>
                    <td class="px-4 py-2">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</td>
                    <td class="px-4 py-2">{{ $vehiculo->anio }}</td>
                    <td class="px-4 py-2">{{ $vehiculo->cliente->nombre ?? '-' }}</td>
                    <td class="px-4 py-2 flex gap-2">
                        <button wire:click="editar({{ $vehiculo->id }})" class="text-blue-600 hover:underline">Editar</button>
                        <button wire:click="eliminar({{ $vehiculo->id }})" wire:confirm="¿Eliminar este vehiculo?" class="text-red-600 hover:underline">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay vehiculos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>