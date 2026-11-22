<div class="relative" wire:poll.15s="revisar" x-data="{}" @click.outside="if ($wire.abierto) $wire.cerrar()">
    <button type="button" wire:click="alternar" aria-label="Notificaciones"
            class="relative flex h-10 w-10 items-center justify-center rounded border border-slate-600 text-white hover:bg-slate-800">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($noLeidas > 0)
            <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-isa-red px-1 text-[11px] font-bold leading-none text-white">
                {{ $noLeidas > 99 ? '99+' : $noLeidas }}
            </span>
        @endif
    </button>

    @if($abierto)
        <div class="absolute right-0 z-50 mt-2 w-[22rem] max-w-[88vw] border border-slate-300 bg-white text-tinta shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-2.5">
                <span class="font-display text-lg font-bold uppercase tracking-wide">Notificaciones</span>
                @if($noLeidas > 0)
                    <button type="button" wire:click="marcarTodas" class="text-xs font-semibold text-isa-blue hover:underline">
                        Marcar todas como leídas
                    </button>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto">
                @forelse($lista as $n)
                    <button type="button" wire:click="abrirNotificacion({{ $n->id }})" wire:key="notif-{{ $n->id }}"
                            class="flex w-full gap-3 border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50 {{ $n->leida_at ? '' : 'bg-blue-50/60' }}">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $n->leida_at ? 'bg-slate-300' : 'bg-isa-red' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm {{ $n->leida_at ? 'font-medium' : 'font-bold' }}">{{ $n->titulo }}</span>
                            @if($n->mensaje)
                                <span class="mt-0.5 block text-xs text-slate-600">{{ $n->mensaje }}</span>
                            @endif
                            <span class="mt-1 block text-[11px] uppercase tracking-wide text-slate-400">
                                {{ $n->created_at ? $n->created_at->locale('es')->diffForHumans() : '' }}
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-slate-500">
                        No tienes notificaciones todavía.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
