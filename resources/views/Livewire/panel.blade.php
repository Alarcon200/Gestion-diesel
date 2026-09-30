
<div>
    <h1 class="text-xl font-bold mb-1">Bienvenido, {{ session('usuario_nombre') }}</h1>
    <p class="text-gray-500 text-sm mb-6">Rol: {{ session('usuario_rol') }}</p>

    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-slate-800">{{ $totalEnTaller }}</p>
            <p class="text-xs text-gray-500 mt-1">Vehiculos en el taller</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-gray-600">{{ $conteos['en_espera_diagnostico'] }}</p>
            <p class="text-xs text-gray-500 mt-1">En espera de diagnostico</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $conteos['en_reparacion'] }}</p>
            <p class="text-xs text-gray-500 mt-1">En reparacion</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-green-600">{{ $salidosHoy }}</p>
            <p class="text-xs text-gray-500 mt-1">Salidas hoy</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-amber-600">{{ $conteos['pendiente_cotizacion'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendiente cotizacion</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-purple-600">{{ $conteos['pendiente_informe'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendiente informe</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-pink-600">{{ $conteos['pendiente_prefactura'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendiente prefactura</p>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-3xl font-bold text-indigo-600">{{ $conteos['orden_enviada'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Orden enviada</p>
        </div>
    </div>

    <h2 class="text-lg font-semibold mb-3">Ultimos ingresos</h2>
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-2">Vehiculo</th>
                    <th class="px-4 py-2">Cliente</th>
                    <th class="px-4 py-2">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ultimosIngresos as $ingreso)
                <tr class="border-t">
                    <td class="px-4 py-2 font-medium">{{ $ingreso->vehiculo->placa ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $ingreso->vehiculo->cliente->nombre ?? '-' }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($ingreso->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No hay ingresos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="/ingresos" class="inline-block mt-4 text-blue-600 hover:underline text-sm">Ver y gestionar todos los ingresos →</a>
</div>
