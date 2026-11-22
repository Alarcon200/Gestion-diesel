<div wire:poll.30s>
    {{-- Encabezado --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-4xl font-bold uppercase tracking-wide">Resumen del taller</h1>
            <p class="text-sm capitalize text-slate-600">{{ $hoy->copy()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</p>
        </div>
        <div class="flex items-center gap-2 border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-600">
            <span class="relative flex h-2.5 w-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-500 opacity-60"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-600"></span>
            </span>
            En vivo · actualizado {{ $hoy->format('H:i') }}
        </div>
    </div>

    {{-- Indicadores --}}
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="border border-slate-300 border-l-8 border-l-isa-blue bg-white p-4 md:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">En el taller</p>
            <p class="font-display text-5xl font-bold leading-tight md:text-6xl">{{ $totalEnTaller }}</p>
            <p class="text-xs text-slate-500">{{ $totalEnTaller === 1 ? 'vehículo' : 'vehículos' }} dentro ahora</p>
        </div>

        <div class="border border-slate-300 border-l-8 {{ $totalSinST > 0 ? 'border-l-amber-500' : 'border-l-green-600' }} bg-white p-4 md:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sin número ST</p>
            <p class="font-display text-5xl font-bold leading-tight md:text-6xl {{ $totalSinST > 0 ? 'text-amber-600' : '' }}">{{ $totalSinST }}</p>
            <p class="text-xs text-slate-500">{{ $totalSinST > 0 ? 'falta pedir la cotización' : 'todo al día' }}</p>
        </div>

        <div class="border border-slate-300 border-l-8 {{ $totalSinInspeccion > 0 ? 'border-l-isa-red' : 'border-l-green-600' }} bg-white p-4 md:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sin inspección</p>
            <p class="font-display text-5xl font-bold leading-tight md:text-6xl {{ $totalSinInspeccion > 0 ? 'text-isa-red' : '' }}">{{ $totalSinInspeccion }}</p>
            <p class="text-xs text-slate-500">{{ $totalSinInspeccion > 0 ? 'sin fotos del estado de llegada' : 'todos con evidencia' }}</p>
        </div>

        <div class="border border-slate-300 border-l-8 border-l-violet-500 bg-white p-4 md:p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ingresos de la semana</p>
            <p class="font-display text-5xl font-bold leading-tight md:text-6xl">{{ $ingresosSemana }}</p>
            <p class="text-xs text-slate-500">de lunes a domingo</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Columna principal --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Requieren atención --}}
            <section class="border border-slate-300 bg-white">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                    <h2 class="font-display text-xl font-bold uppercase tracking-wide">Requieren atención</h2>
                    <span class="text-xs font-semibold {{ count($atencion) ? 'text-isa-red' : 'text-green-700' }}">
                        {{ count($atencion) }} {{ count($atencion) === 1 ? 'vehículo' : 'vehículos' }}
                    </span>
                </div>

                @forelse($atencion as $fila)
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-slate-100 px-5 py-3 last:border-b-0" wire:key="atencion-{{ $fila['ingreso']->id }}">
                        <span class="placa text-xl!">{{ $fila['ingreso']->vehiculo->placa ?? '-' }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm font-medium">
                            {{ $fila['ingreso']->vehiculo->cliente->nombre ?? '-' }}
                        </span>
                        <span class="flex flex-wrap gap-1.5">
                            @foreach($fila['razones'] as $r)
                                <span class="border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide {{ $r['clase'] }}">{{ $r['texto'] }}</span>
                            @endforeach
                        </span>
                    </div>
                @empty
                    <div class="flex items-center gap-3 px-5 py-6 text-sm font-medium text-green-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-lg font-bold">&#10003;</span>
                        Todo al día: ningún vehículo está esperando ST, inspección o lleva demasiado tiempo.
                    </div>
                @endforelse
            </section>

            {{-- Vehículos en el taller --}}
            <section class="border border-slate-300 bg-white">
                <div class="border-b border-slate-200 px-5 py-3">
                    <h2 class="font-display text-xl font-bold uppercase tracking-wide">Vehículos en el taller</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-2.5 font-semibold">Placa</th>
                                <th class="px-3 py-2.5 font-semibold">Cliente</th>
                                <th class="px-3 py-2.5 font-semibold">ST</th>
                                <th class="px-3 py-2.5 font-semibold">Estado</th>
                                <th class="px-3 py-2.5 font-semibold">Inspección</th>
                                <th class="px-5 py-2.5 text-right font-semibold">Días</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($enTaller as $ing)
                                @php
                                    $orden = $ing->ordenes->first(fn ($o) => str_starts_with((string) $o->numero_orden, 'ST'));
                                    $etapa = $etapas[$ing->estado] ?? ['label' => $ing->estado, 'chip' => 'bg-slate-100 text-slate-700 border-slate-300'];
                                @endphp
                                <tr class="hover:bg-slate-50" wire:key="fila-{{ $ing->id }}">
                                    <td class="px-5 py-3"><span class="placa text-lg!">{{ $ing->vehiculo->placa ?? '-' }}</span></td>
                                    <td class="px-3 py-3">
                                        <div class="font-medium">{{ $ing->vehiculo->cliente->nombre ?? '-' }}</div>
                                        <div class="max-w-[16rem] truncate text-xs text-slate-500">{{ $ing->motivo }}</div>
                                    </td>
                                    <td class="px-3 py-3 font-semibold">
                                        @if($orden)
                                            {{ $orden->numero_orden }}
                                        @else
                                            <span class="text-xs font-bold uppercase text-amber-600">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="whitespace-nowrap border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide {{ $etapa['chip'] }}">{{ $etapa['label'] }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        @if($ing->inspecciones_count > 0)
                                            <span class="font-semibold text-green-700">Registrada</span>
                                        @else
                                            <span class="font-semibold text-isa-red">Falta</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold {{ ($dias[$ing->id] ?? 0) >= 7 ? 'text-isa-red' : '' }}">
                                        {{ $dias[$ing->id] ?? 0 }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                        No hay vehículos en el taller en este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- Columna lateral --}}
        <div class="space-y-6">

            {{-- Estado de los vehículos --}}
            <section class="border border-slate-300 bg-white p-5">
                <h2 class="mb-4 font-display text-xl font-bold uppercase tracking-wide">Por etapa</h2>
                <div class="space-y-3.5">
                    @foreach($etapas as $clave => $etapa)
                        @php $pct = $totalEnTaller > 0 ? round($conteos[$clave] / $totalEnTaller * 100) : 0; @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium">{{ $etapa['label'] }}</span>
                                <span class="font-display text-xl font-bold leading-none">{{ $conteos[$clave] }}</span>
                            </div>
                            <div class="h-2 bg-slate-200">
                                <div class="h-2 {{ $etapa['barra'] }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Ingresos 7 días --}}
            <section class="border border-slate-300 bg-white p-5">
                <h2 class="mb-4 font-display text-xl font-bold uppercase tracking-wide">Ingresos, últimos 7 días</h2>
                <div class="flex h-32 items-end gap-2">
                    @foreach($ultimosDias as $d)
                        @php $alto = $d['total'] > 0 ? max(10, (int) round($d['total'] / $maxDia * 88)) : 3; @endphp
                        <div class="flex flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-bold {{ $d['total'] > 0 ? '' : 'text-slate-300' }}">{{ $d['total'] }}</span>
                            <div class="w-full {{ $d['hoy'] ? 'bg-isa-red' : ($d['total'] > 0 ? 'bg-black' : 'bg-slate-200') }}" style="height: {{ $alto }}px"></div>
                            <span class="text-[11px] uppercase {{ $d['hoy'] ? 'font-bold text-isa-red' : 'text-slate-500' }}">{{ $d['dia'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Mecánicos --}}
            <section class="border border-slate-300 bg-white p-5">
                <h2 class="mb-4 font-display text-xl font-bold uppercase tracking-wide">Trabajo de los mecánicos</h2>

                @forelse($carga as $c)
                    <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-b-0">
                        <div>
                            <div class="text-sm font-semibold">{{ $c['nombre'] }}</div>
                            <div class="text-xs text-slate-500">
                                {{ $c['reparando'] }} en reparación
                            </div>
                        </div>
                        <span class="flex h-9 min-w-9 items-center justify-center bg-black px-2 font-display text-xl font-bold text-white">{{ $c['total'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Todavía no hay trabajos asignados.</p>
                @endforelse

                @if($sinMecanico > 0)
                    <a href="/asignaciones" class="mt-3 block border-l-4 border-amber-500 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                        {{ $sinMecanico }} {{ $sinMecanico === 1 ? 'vehículo sin mecánico' : 'vehículos sin mecánico' }} &rarr; asignar
                    </a>
                @endif
            </section>
        </div>
    </div>
</div>
