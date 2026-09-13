<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Diagnosticos</h1>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700">
            + Nuevo diagnostico
        </button>
    </div>

    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">Nuevo diagnostico</h2>
        <form wire:submit="guardar">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Orden</label>
                <select wire:model="orden_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- Selecciona una orden --</option>
                    @foreach($ordenes as $orden)
                        <option value="{{ $orden->id }}">{{ $orden->numero_orden }} - {{ $orden->vehiculo->placa ?? '-' }}</option>
                    @endforeach
                </select>
                @error('orden_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Problema encontrado</label>
                <textarea wire:model="problema_encontrado" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                @error('problema_encontrado') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Causa probable</label>
                <textarea wire:model="causa_probable" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Trabajo a realizar</label>
                <textarea wire:model="trabajo_a_realizar" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                @error('trabajo_a_realizar') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Recomendaciones</label>
                <textarea wire:model="recomendaciones" rows="2" class="w-full border rounded px-3 py-2"></textarea>
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
                    <th class="px-4 py-2">Orden</th>
                    <th class="px-4 py-2">Vehiculo</th>
                    <th class="px-4 py-2">Problema</th>
                    <th class="px-4 py-2">Trabajo a realizar</th>
                    <th class="px-4 py-2">Registrado por</th>
                </tr>
            </thead>
            <tbody>
                @forelse($diagnosticos as $diagnostico)
                <tr class="border-t">
                    <td class="px-4 py-2 font-medium">{{ $diagnostico->orden->numero_orden ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $diagnostico->orden->vehiculo->placa ?? '-' }}</td>
                    <td class="px-4 py-2">{{ Str::limit($diagnostico->problema_encontrado, 60) }}</td>
                    <td class="px-4 py-2">{{ Str::limit($diagnostico->trabajo_a_realizar, 60) }}</td>
                    <td class="px-4 py-2">{{ $diagnostico->usuario->nombre ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay diagnosticos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>