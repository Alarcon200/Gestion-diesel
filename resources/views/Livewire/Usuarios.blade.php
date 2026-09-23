<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Personal</h1>
            <p class="text-sm text-gray-500">Administra los accesos al sistema</p>
        </div>
        <button wire:click="nuevo" class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700 transition">
            + Nuevo Usuario
        </button>
    </div>

    <!-- Mensaje de éxito -->
    @if (session()->has('mensaje'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
            {{ session('mensaje') }}
        </div>
    @endif

    <!-- Formulario de Creación -->
    @if($mostrar_formulario)
    <div class="bg-white p-6 rounded shadow mb-6 border-t-4 border-blue-500">
        <h2 class="font-semibold text-lg mb-4 text-gray-700">Registrar nuevo integrante</h2>
        <form wire:submit="guardar">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-600">Nombre</label>
                    <input type="text" wire:model="nombre" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                    @error('nombre') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium mb-1 text-gray-600">Apellidos</label>
                  <input type="text" wire:model="apellido" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                  @error('apellido') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-600">Correo Electrónico (Usuario)</label>
                    <input type="email" wire:model="email" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                    @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-600">Contraseña de acceso</label>
                    <input type="password" wire:model="password" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                    @error('password') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-600">Rol en el Taller</label>
                    <<select wire:model="rol" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                      <option value="">-- Selecciona un rol --</option>
                      <option value="1">Administrador</option>
                      <option value="2">Jefe de Taller</option>
                      <option value="3">Contadora</option>
                      <option value="4">Mecánico</option>
                      <option value="5">Mensajero</option>
                   </select>
                    @error('rol') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition shadow">
                    Guardar Usuario
                </button>
                <button type="button" wire:click="cancelar" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300 transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Tabla de Usuarios Activos -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 font-semibold">Nombre</th>
                    <th class="px-6 py-3 font-semibold">Correo / Usuario</th>
                    <th class="px-6 py-3 font-semibold">Rol</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($usuarios as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $user->nombre }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                    
                    <!-- 👉 AQUÍ PEGAS EXACTAMENTE EL CÓDIGO -->
                    <td class="px-6 py-4">
                        @if($user->rol_id == 1)
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-purple-100 text-purple-700">Administrador</span>
                        @elseif($user->rol_id == 2)
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-blue-100 text-blue-700">Jefe de Taller</span>
                        @elseif($user->rol_id == 3)
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-green-100 text-green-700">Contadora</span>
                        @elseif($user->rol_id == 4)
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-orange-100 text-orange-700">Mecánico</span>
                        @elseif($user->rol_id == 5)
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-yellow-100 text-yellow-700">Mensajero</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-gray-100 text-gray-700">Sin asignar</span>
                        @endif
                    </td>
                    <!-- TERMINA EL CÓDIGO NUEVO -->

                </tr>
                @empty
                <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">No hay usuarios registrados en el sistema</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>