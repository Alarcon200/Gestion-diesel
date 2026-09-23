<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Asignación de Trabajos</h1>
            <p class="text-sm text-gray-500">Reparte las órdenes a los mecánicos de turno</p>
        </div>
        <button wire:click="nuevaAsignacion" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700 transition">
            + Asignar Trabajo
        </button>
    </div>

    @if (session()->has('mensaje'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
            {{ session('mensaje') }}
        </div>
    @endif

    <!-- Formulario de Asignación -->
    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6 border-t-4 border-orange-500">
        <h2 class="font-semibold text-lg mb-4 text-gray-700">Nueva Asignación</h2>
        <form wire:submit="guardar">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-600">Vehículo / Orden Pendiente</label>
                    <select wire:model="orden_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-orange-300">
                        <option value="">-- Selecciona una orden --</option>
                        @foreach($ordenesPendientes as $orden)
                            <option value="{{ $orden->id }}">Orden #{{ $orden->numero_orden }} - {{ $orden->vehiculo->placa ?? 'Sin placa' }}</option>
                        @endforeach
                    </select>
                    @error('orden_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-600">Mecánico a cargo</label>
                    <select wire:model="mecanico_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-orange-300">
                        <option value="">-- Selecciona un mecánico --</option>
                        @foreach($mecanicos as $mecanico)
                            <option value="{{ $mecanico->id }}">{{ $mecanico->nombre }} {{ $mecanico->apellido }}</option>
                        @endforeach
                    </select>
                    @error('mecanico_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700 transition shadow">
                    Confirmar Asignación
                </button>
                <button type="button" wire:click="cancelar" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300 transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Tabla de Trabajos Asignados -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 font-semibold">Orden</th>
                    <th class="px-6 py-3 font-semibold">Vehículo</th>
                    <th class="px-6 py-3 font-semibold">Mecánico Asignado</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($asignacionesActivas as $asignacion)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800">#{{ $asignacion->numero_orden }}</td>
                    <td class="px-6 py-4">{{ $asignacion->vehiculo->placa ?? '-' }}</td>
                    <td class="px-6 py-4 font-semibold text-orange-600">
                        <!-- Asumimos que tienes una relación 'mecanico' en el modelo Orden -->
                        {{ $asignacion->mecanico->nombre ?? 'Mecánico Desconocido' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
                            {{ $asignacion->estado ?? 'En Proceso' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay trabajos asignados en este momento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>