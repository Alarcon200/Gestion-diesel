<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-4xl font-bold uppercase tracking-wide">Personal</h1>
            <p class="text-sm text-slate-600">Quién puede entrar al sistema y con qué rol</p>
        </div>
        <button wire:click="nuevo" class="rounded bg-black px-4 py-2.5 font-semibold text-white hover:bg-slate-800">
            + Nuevo usuario
        </button>
    </div>

    @if (session()->has('mensaje'))
        <div class="mb-4 border-l-8 border-green-600 bg-green-50 p-4 text-sm font-medium text-green-800">
            {{ session('mensaje') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 border-l-8 border-isa-red bg-red-50 p-4 text-sm font-medium text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($mostrar_formulario)
        <div class="mb-6 border border-slate-300 border-t-4 border-t-isa-blue bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Registrar nuevo integrante</h2>
            <form wire:submit="guardar">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
                        <input type="text" wire:model="nombre" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-isa-blue focus:outline-none">
                        @error('nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Apellidos</label>
                        <input type="text" wire:model="apellido" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-isa-blue focus:outline-none">
                        @error('apellido') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Correo electrónico (usuario)</label>
                        <input type="email" wire:model="email" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-isa-blue focus:outline-none">
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Contraseña de acceso</label>
                        <input type="password" wire:model="password" class="w-full rounded border border-slate-300 px-3 py-2 focus:border-isa-blue focus:outline-none">
                        @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Rol en el taller</label>
                        <select wire:model="rol" class="w-full rounded border border-slate-300 bg-white px-3 py-2 focus:border-isa-blue focus:outline-none">
                            <option value="">-- Selecciona un rol --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->nombre }}@if($r->nombre === 'Contabilidad') (secretaria)@endif</option>
                            @endforeach
                        </select>
                        @error('rol') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <button type="submit" class="rounded bg-isa-blue px-6 py-2 font-semibold text-white hover:bg-blue-700">
                        Guardar usuario
                    </button>
                    <button type="button" wire:click="cancelar" class="rounded bg-slate-200 px-6 py-2 font-semibold text-slate-700 hover:bg-slate-300">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    @endif

    @php
        $colores = [
            'Administrador' => 'bg-purple-100 text-purple-800 border-purple-300',
            'Jefe de taller' => 'bg-blue-100 text-blue-800 border-blue-300',
            'Mecánico' => 'bg-orange-100 text-orange-800 border-orange-300',
            'Contabilidad' => 'bg-green-100 text-green-800 border-green-300',
            'Mensajero' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        ];
    @endphp

    <div class="overflow-x-auto border border-slate-300 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
                <tr>
                    <th class="px-5 py-3 font-semibold">Nombre</th>
                    <th class="px-5 py-3 font-semibold">Correo / usuario</th>
                    <th class="px-5 py-3 font-semibold">Rol</th>
                    <th class="px-5 py-3 font-semibold">Estado</th>
                    <th class="px-5 py-3 text-right font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($usuarios as $user)
                    @php $esYo = (int) $user->id === (int) session('usuario_id'); @endphp
                    <tr class="hover:bg-slate-50 {{ $user->activo ? '' : 'bg-slate-50 text-slate-400' }}" wire:key="usuario-{{ $user->id }}">
                        <td class="px-5 py-3.5 font-semibold">
                            {{ $user->nombre }} {{ $user->apellido }}
                            @if($esYo) <span class="ml-1 text-xs font-medium text-slate-500">(tú)</span> @endif
                        </td>
                        <td class="px-5 py-3.5">{{ $user->email }}</td>
                        <td class="px-5 py-3.5">
                            <span class="border px-2 py-1 text-xs font-bold uppercase tracking-wide {{ $colores[$user->rol->nombre ?? ''] ?? 'bg-slate-100 text-slate-700 border-slate-300' }}">
                                {{ $user->rol->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($user->activo)
                                <span class="font-semibold text-green-700">Activo</span>
                            @else
                                <span class="font-semibold text-slate-500">Desactivado</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if(!$esYo)
                                <button type="button" wire:click="alternarActivo({{ $user->id }})"
                                        class="mr-2 border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                    {{ $user->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button type="button" wire:click="eliminar({{ $user->id }})"
                                        wire:confirm="¿Eliminar a {{ $user->nombre }} {{ $user->apellido }}? Esta acción no se puede deshacer."
                                        class="border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                    Eliminar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No hay usuarios registrados en el sistema</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-xs text-slate-500">
        Si un usuario ya tiene trabajos, ingresos o informes registrados no se puede eliminar (se perdería el historial):
        usa "Desactivar" y no podrá volver a entrar.
    </p>
</div>
