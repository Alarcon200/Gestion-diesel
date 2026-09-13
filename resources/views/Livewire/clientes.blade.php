<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Clientes</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nuevo cliente
        </button>
    </div>

    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">{{ $editando_id ? 'Editar cliente' : 'Nuevo cliente' }}</h2>
        <form wire:submit="guardar" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nombre</label>
                <input type="text" wire:model="nombre" class="w-full border rounded px-3 py-2">
                @error('nombre') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Documento/NIT</label>
                <input type="text" wire:model="doc_nit" class="w-full border rounded px-3 py-2">
                @error('doc_nit') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telefono</label>
                <input type="text" wire:model="telefono" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Direccion</label>
                <input type="text" wire:model="direccion" class="w-full border rounded px-3 py-2">
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
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Documento</th>
                    <th class="px-4 py-2">Telefono</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $cliente->nombre }}</td>
                    <td class="px-4 py-2">{{ $cliente->doc_nit }}</td>
                    <td class="px-4 py-2">{{ $cliente->telefono }}</td>
                    <td class="px-4 py-2">{{ $cliente->email }}</td>
                    <td class="px-4 py-2 flex gap-2">
                        <button wire:click="editar({{ $cliente->id }})" class="text-blue-600 hover:underline">Editar</button>
                        <button wire:click="eliminar({{ $cliente->id }})" wire:confirm="¿Eliminar este cliente?" class="text-red-600 hover:underline">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay clientes registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>