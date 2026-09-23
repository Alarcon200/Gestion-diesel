<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Resumen del Taller</h1>
        <p class="text-sm text-gray-500">Métricas y estado general al día de hoy</p>
    </div>

    <!-- 4 Tarjetas Superiores -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Tarjeta: Órdenes Activas -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Vehículos en Patio</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $ordenesEnProceso }}</h3>
                </div>
                <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Cotizaciones Pendientes -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Cotizaciones Pendientes</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $cotizacionesPendientes }}</h3>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Ingresos de la semana -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Ingresos (Esta semana)</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $ingresosSemana }}</h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Dinero Aprobado -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Ventas Aprobadas (Semana)</p>
                    <h3 class="text-2xl font-bold text-gray-800">${{ number_format($dineroAprobadoSemana, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-green-100 p-3 rounded-full text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Inferior: Acción Rápida -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800">Atención Requerida: Últimas Cotizaciones Pendientes</h3>
        </div>
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-6 py-3 font-semibold">Fecha</th>
                    <th class="px-6 py-3 font-semibold">Orden / Vehículo</th>
                    <th class="px-6 py-3 font-semibold text-right">Total</th>
                    <th class="px-6 py-3 font-semibold text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($ultimasCotizaciones as $cotizacion)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($cotizacion->fecha_creacion)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        {{ $cotizacion->orden->numero_orden ?? 'Sin Orden' }} 
                        <span class="text-gray-500 font-medium ml-1">({{ $cotizacion->orden?->vehiculo?->placa ?? $cotizacion->vehiculo?->placa ?? '-' }})</span>
                    </td>
                    <td class="px-6 py-4 text-right font-medium">${{ number_format($cotizacion->total, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-center">
                        <a href="/cotizaciones" class="text-blue-600 hover:underline text-sm">Gestionar &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        No hay cotizaciones pendientes en este momento. ¡Todo al día!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>