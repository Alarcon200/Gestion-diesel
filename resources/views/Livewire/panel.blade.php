
<div>
    @if(session('usuario_rol') === 'Mecánico')

        {{-- PANEL EXCLUSIVO DEL MECÁNICO --}}
        <div class="space-y-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Mi panel de mecánico
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Bienvenido, {{ session('usuario_nombre') }}.
                </p>
            </div>

            @if($mensaje)
                <div class="rounded bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ $mensaje }}
                </div>
            @endif

            {{-- ESTADÍSTICAS DEL MECÁNICO --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow">
                    <p class="text-sm text-slate-500">Mis órdenes activas</p>
                    <p class="mt-2 text-3xl font-bold text-blue-700">
                        {{ $misOrdenesActivas }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <p class="text-sm text-slate-500">Vehículos reparados</p>
                    <p class="mt-2 text-3xl font-bold text-green-700">
                        {{ $misReparaciones }}
                    </p>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <p class="text-sm text-slate-500">Vehículos entregados</p>
                    <p class="mt-2 text-3xl font-bold text-purple-700">
                        {{ $misVehiculosSalidos }}
                    </p>
                </div>
            </div>

            {{-- ÓRDENES SIN MECÁNICO ASIGNADO --}}
            <section class="rounded-lg bg-white shadow">
                <div class="border-b px-5 py-4">
                    <h2 class="font-semibold text-slate-800">
                        Órdenes disponibles
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Estas órdenes todavía no tienen mecánico asignado.
                        El jefe de taller es quien realiza las asignaciones.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Orden</th>
                                <th class="px-5 py-3">Placa</th>
                                <th class="px-5 py-3">Vehículo</th>
                                <th class="px-5 py-3">Fecha</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($ordenesDisponibles as $orden)
                                <tr>
                                    <td class="px-5 py-3 font-medium">
                                        {{ $orden->numero_orden ?? ('Orden #' . $orden->id) }}
                                    </td>

                                    <td class="px-5 py-3">
                                        {{ $orden->vehiculo->placa ?? '-' }}
                                    </td>

                                    <td class="px-5 py-3">
                                        {{ $orden->vehiculo->marca ?? '' }}
                                        {{ $orden->vehiculo->modelo ?? '' }}
                                    </td>

                                    <td class="px-5 py-3">
                                        {{ $orden->fecha_creacion
                                            ? \Carbon\Carbon::parse($orden->fecha_creacion)->format('d/m/Y')
                                            : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                                        No hay órdenes disponibles sin asignar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ÓRDENES ASIGNADAS A ESTE MECÁNICO --}}
            <section class="rounded-lg bg-white shadow">
                <div class="border-b px-5 py-4">
                    <h2 class="font-semibold text-slate-800">
                        Mis órdenes asignadas
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Aquí puedes consultar y actualizar únicamente tus propias órdenes.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-3">Orden</th>
                                <th class="px-5 py-3">Vehículo</th>
                                <th class="px-5 py-3">Estado actual</th>
                                <th class="px-5 py-3">Actualizar estado</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @forelse($misOrdenes as $orden)
                                <tr>
                                    <td class="px-5 py-3 font-medium">
                                        {{ $orden->numero_orden ?? ('Orden #' . $orden->id) }}
                                    </td>

                                    <td class="px-5 py-3">
                                        {{ $orden->vehiculo->placa ?? '-' }}
                                        — {{ $orden->vehiculo->marca ?? '' }}
                                        {{ $orden->vehiculo->modelo ?? '' }}
                                    </td>

                                    <td class="px-5 py-3">
                                        {{ ucfirst(str_replace('_', ' ', $orden->estado)) }}
                                    </td>

                                    <td class="px-5 py-3">
                                        @if(!in_array($orden->estado, [
                                            'finalizada',
                                            'entregada',
                                            'cancelada'
                                        ]))
                                            <div class="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    wire:click="actualizarEstado({{ $orden->id }}, 'en_reparacion')"
                                                    class="rounded bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                                                    En reparación
                                                </button>

                                                <button
                                                    type="button"
                                                    wire:click="actualizarEstado({{ $orden->id }}, 'finalizada')"
                                                    wire:confirm="¿Confirmas que terminaste esta reparación?"
                                                    class="rounded bg-green-600 px-3 py-2 text-white hover:bg-green-700">
                                                    Finalizar
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-slate-500">
                                                Sin acciones disponibles
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                                        Todavía no tienes órdenes asignadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- REPORTE DE FALLAS --}}
            <section class="rounded-lg bg-white p-5 shadow">
                <h2 class="font-semibold text-slate-800">
                    Reportar falla mecánica
                </h2>

                <p class="mb-4 mt-1 text-sm text-slate-500">
                    Describe la falla detectada para que el jefe de taller pueda revisarla.
                </p>

                <form wire:submit="reportarFalla" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Orden asignada
                        </label>

                        <select
                            wire:model="orden_id"
                            class="w-full rounded border-slate-300">
                            <option value="">Selecciona una orden</option>

                            @foreach($misOrdenes as $orden)
                                <option value="{{ $orden->id }}">
                                    {{ $orden->numero_orden ?? ('Orden #' . $orden->id) }}
                                    — {{ $orden->vehiculo->placa ?? 'Sin placa' }}
                                </option>
                            @endforeach
                        </select>

                        @error('orden_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Falla encontrada
                        </label>

                        <textarea
                            wire:model="problema_encontrado"
                            rows="3"
                            class="w-full rounded border-slate-300"
                            placeholder="Describe la falla encontrada..."></textarea>

                        @error('problema_encontrado')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Comentario adicional
                        </label>

                        <textarea
                            wire:model="comentario"
                            rows="2"
                            class="w-full rounded border-slate-300"
                            placeholder="Notas para el jefe de taller..."></textarea>

                        @error('comentario')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="rounded bg-slate-800 px-4 py-2 text-white hover:bg-slate-700">
                        Enviar reporte
                    </button>
                </form>
            </section>
        </div>

    @else

        {{-- DASHBOARD GENERAL PARA LOS DEMÁS ROLES --}}
        <div>
            <h1 class="mb-1 text-xl font-bold">
                Bienvenido, {{ session('usuario_nombre') }}
            </h1>

            <p class="mb-6 text-sm text-gray-500">
                Rol: {{ session('usuario_rol') }}
            </p>

            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $totalEnTaller }}</p>
                    <p class="mt-1 text-xs text-gray-500">Vehículos en el taller</p>
                </div>

                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $conteos['en_espera_diagnostico'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">En espera de diagnóstico</p>
                </div>

                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $conteos['en_reparacion'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">En reparación</p>
                </div>

                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $conteos['pendiente_cotizacion'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">Pendiente de cotización</p>
                </div>

                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $conteos['pendiente_informe'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">Pendiente de informe</p>
                </div>

                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $conteos['pendiente_prefactura'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">Pendiente de prefactura</p>
                </div>

                <div class="rounded bg-white p-4 text-center shadow">
                    <p class="text-3xl font-bold">{{ $conteos['orden_enviada'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">Orden enviada</p>
                </div>
            </div>

            <h2 class="mb-3 text-lg font-semibold">
                Últimos ingresos
            </h2>

            <div class="overflow-x-auto rounded bg-white shadow">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-2">Vehículo</th>
                            <th class="px-4 py-2">Cliente</th>
                            <th class="px-4 py-2">Fecha</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($ultimosIngresos as $ingreso)
                            <tr class="border-t">
                                <td class="px-4 py-2">
                                    {{ $ingreso->vehiculo->placa ?? '-' }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ $ingreso->vehiculo->cliente->nombre ?? '-' }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ $ingreso->fecha_ingreso
                                        ? \Carbon\Carbon::parse($ingreso->fecha_ingreso)->format('d/m/Y H:i')
                                        : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                                    No hay ingresos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif
</div>

