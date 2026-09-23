<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Diesel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        @if(session('usuario_id'))
        <nav class="bg-slate-800 text-white px-6 py-3 flex justify-between items-center">
            <span class="font-bold">Gestion Diesel</span>
            <div class="flex gap-4 items-center text-sm">
                <a href="/panel" class="hover:underline">Panel</a>
                <a href="/clientes" class="hover:underline">Clientes</a>
                <a href="/vehiculos" class="hover:underline">Vehiculos</a>
                <a href="/ordenes" class="hover:underline">Ordenes</a>
                <a href="/ingresos" class="hover:underline">Ingresos</a>
                <a href="/diagnosticos" class="hover:underline">Diagnosticos</a>
                <a href="/cotizaciones" class="hover:underline">Cotizaciones</a>
                <a href="/usuarios" class="hover:underline">Personal</a>
                <a href="/asignaciones" class="hover:underline">Asignaciones</a>
                <span class="text-gray-300">{{ session('usuario_nombre') }} ({{ session('usuario_rol') }})</span>
                <a href="/logout" class="bg-red-600 px-3 py-1 rounded hover:bg-red-700">Salir</a>
            </div>
        </nav>
        @endif

        <main class="p-6">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
