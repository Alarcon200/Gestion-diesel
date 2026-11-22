<div wire:poll.30s class="space-y-8">

    <div>
        <h1 class="font-display text-4xl font-bold leading-none">
            Hola, {{ explode(' ', trim(session('usuario_nombre')))[0] }}
        </h1>
        <p class="mt-2 text-slate-600">
            @if($totalEnTaller === 1)
                Hay 1 vehículo en el taller.
            @else
                Hay {{ $totalEnTaller }} vehículos en el taller.
            @endif
        </p>
    </div>

    @if($mensaje)
        <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            {{ $mensaje }}
        </div>
    @endif

    {{-- Etapas del taller, en el orden del flujo --}}
    <section aria-label="Vehículos por etapa">
        <div class="flex flex-col gap-2 md:flex-row md:gap-0">
            @foreach($etapas as $clave => $etapa)
                @php $n = $conteos[$clave]; @endphp
                <div class="etapa flex flex-1 items-center gap-4 border-b-4 bg-white px-5 py-4 md:block {{ $n > 0 ? $etapa['borde'] : 'border-slate-200' }}">
                    <p class="font-display text-5xl font-bold leading-none {{ $n > 0 ? 'text-tinta' : 'text-slate-300' }}">{{ $n }}</p>
                    <p class="text-sm font-medium leading-snug {{ $n > 0 ? 'text-slate-700' : 'text-slate-400' }} md:mt-2">{{ $etapa['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Vehículos en el taller --}}
    <section>
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <h2 class="font-display text-3xl font-bold leading-none">En el taller</h2>
            @if($puedeCambiar)
                <p class="text-sm text-slate-500">Cambia el estado desde la columna Estado.</p>
            @endif
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500">
                        <th class="px-5 py-3 font-medium">Placa</th>
                        <th class="px-5 py-3 font-medium">Cliente</th>
                        <th class="px-5 py-3 font-medium">Motivo</th>
                        <th class="px-5 py-3 font-medium">ST</th>
                        <th class="px-5 py-3 font-medium">Estado</th>
                        <th class="px-5 py-3 font-medium">Ingresó</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($ingresos as $ing)
                        @php
                            $st = $ing->ordenes->first(fn ($o) => str_starts_with((string) $o->numero_orden, 'ST'));
                            $info = $estados[$ing->estado] ?? ['label' => $ing->estado, 'chip' => 'bg-slate-100 text-slate-700 border-slate-300'];
                        @endphp
                        <tr wire:key="ingreso-{{ $ing->id }}" class="hover:bg-slate-50">
                            <td class="px-5 py-3.5">
                                <span class="placa">{{ $ing->vehiculo->placa ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium">{{ $ing->vehiculo->cliente->nombre ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $ing->motivo }}</td>
                            <td class="px-5 py-3.5">
                                @if($st)
                                    <span class="font-semibold">{{ $st->numero_orden }}</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800">Sin ST</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($puedeCambiar)
                                    <select
                                        wire:change="cambiarEstado({{ $ing->id }}, $event.target.value)"
                                        aria-label="Estado de {{ $ing->vehiculo->placa ?? 'vehículo' }}"
                                        class="rounded-md border py-1.5 pl-2.5 pr-8 text-sm font-medium focus:outline-2 focus:outline-offset-2 focus:outline-isa-blue {{ $info['chip'] }}">
                                        @unless(isset($estados[$ing->estado]))
                                            <option value="{{ $ing->estado }}" selected disabled>{{ $ing->estado }}</option>
                                        @endunless
                                        @foreach($estados as $clave => $e)
                                            <option value="{{ $clave }}" @selected($ing->estado === $clave)>{{ $e['label'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="inline-block rounded-md border px-2.5 py-1.5 text-sm font-medium {{ $info['chip'] }}">{{ $info['label'] }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $ing->fecha_ingreso ? \Carbon\Carbon::parse($ing->fecha_ingreso)->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                No hay vehículos en el taller. Registra un ingreso desde la sección Ingresos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
