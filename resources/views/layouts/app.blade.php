
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Diesel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        @if(session('usuario_id'))
            <nav class="flex flex-wrap items-center justify-between gap-3 bg-slate-800 px-6 py-3 text-white">
                <a href="/panel" class="font-bold">Gestion Diesel</a>

                <div class="flex flex-wrap items-center gap-4 text-sm">
                    @if(session('usuario_rol') === 'Mecánico')
                        <a href="/panel" class="hover:underline">Mi panel</a>
                    @else
                        <a href="/panel" class="hover:underline">Panel</a>
                        <a href="/dashboard" class="hover:underline">Dashboard general</a>
                        <a href="/clientes" class="hover:underline">Clientes</a>
                        <a href="/vehiculos" class="hover:underline">Vehículos</a>
                        <a href="/ingresos" class="hover:underline">Ingresos</a>
                        <a href="/diagnosticos" class="hover:underline">Diagnósticos</a>

                        @if(in_array(session('usuario_rol'), ['Administrador', 'Jefe de taller', 'Jefe de Taller']))
                            <a href="/usuarios" class="hover:underline">Personal</a>
                            <a href="/asignaciones" class="hover:underline">Asignaciones</a>
                        @endif
                    @endif

                    <span class="text-gray-300">
                        {{ session('usuario_nombre') }} ({{ session('usuario_rol') }})
                    </span>

                    <a href="/logout" class="rounded bg-red-600 px-3 py-1 hover:bg-red-700">
                        Salir
                    </a>
                </div>
            </nav>
        @endif

        <main class="p-4 md:p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
