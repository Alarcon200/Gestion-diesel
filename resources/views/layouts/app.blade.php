<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión Diesel</title>
    <link rel="icon" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-concreto font-sans text-tinta antialiased">
    <div class="min-h-screen">
        @if(session('usuario_id'))
            @php
                $rol = session('usuario_rol');
                $esMecanico = $rol === 'Mecánico';
                $esJefe = in_array($rol, ['Administrador', 'Jefe de taller', 'Jefe de Taller']);
                $enlace = fn ($ruta, $texto) => '<a href="/' . $ruta . '" class="whitespace-nowrap border-b-2 pb-1 '
                    . (request()->is($ruta) ? 'border-isa-red text-white' : 'border-transparent text-slate-300 hover:text-white')
                    . '">' . $texto . '</a>';
            @endphp

            <header class="border-b-4 border-isa-red bg-black text-white">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-8 gap-y-3 px-4 py-3 md:px-8">
                    <a href="/panel" class="flex items-center gap-3">
                        <img src="/logo.png" alt="I.S.A." class="h-14 w-14 rounded-full">
                        <span class="font-display text-2xl font-bold leading-none tracking-wide">
                            Gestión Diesel
                        </span>
                    </a>

                    <nav class="flex flex-1 items-center gap-6 overflow-x-auto text-[15px] font-medium">
                        @if($esMecanico)
                            {!! $enlace('panel', 'Mi panel') !!}
                        @else
                            {!! $enlace('panel', 'Panel') !!}
                            {!! $enlace('dashboard', 'Resumen') !!}
                            {!! $enlace('ingresos', 'Ingresos') !!}
                            {!! $enlace('clientes', 'Clientes') !!}
                            {!! $enlace('vehiculos', 'Vehículos') !!}
                            {!! $enlace('diagnosticos', 'Diagnósticos') !!}
                            @if($esJefe)
                                {!! $enlace('asignaciones', 'Asignaciones') !!}
                                {!! $enlace('usuarios', 'Personal') !!}
                            @endif
                        @endif
                    </nav>

                    <div class="flex items-center gap-4 text-sm">
                        <livewire:notificaciones />
                        <span class="text-slate-300">
                            {{ session('usuario_nombre') }}
                            <span class="text-slate-500">({{ $rol }})</span>
                        </span>
                        <a href="/logout" class="rounded bg-isa-red px-3 py-1.5 font-medium text-white hover:bg-red-700">
                            Salir
                        </a>
                    </div>
                </div>
            </header>
        @endif

        <main class="mx-auto max-w-7xl p-4 md:p-8">
            {{ $slot }}
        </main>
    </div>

    @if(session('usuario_id'))
        <div id="avisos" class="pointer-events-none fixed bottom-4 right-4 z-[60] flex w-80 max-w-[90vw] flex-col gap-2"></div>
        <script>
            // Aviso emergente + sonido corto cuando llega una notificación nueva
            window.addEventListener('nueva-notificacion', function (e) {
                var d = Array.isArray(e.detail) ? e.detail[0] : e.detail;
                d = d || {};
                var caja = document.getElementById('avisos');
                if (caja) {
                    var aviso = document.createElement('div');
                    aviso.className = 'pointer-events-auto border-l-8 border-isa-red bg-white p-3 shadow-2xl';
                    var t = document.createElement('div');
                    t.className = 'text-sm font-bold text-tinta';
                    t.textContent = d.titulo || 'Notificación nueva';
                    aviso.appendChild(t);
                    if (d.mensaje) {
                        var m = document.createElement('div');
                        m.className = 'mt-0.5 text-xs text-slate-600';
                        m.textContent = d.mensaje;
                        aviso.appendChild(m);
                    }
                    caja.appendChild(aviso);
                    setTimeout(function () { aviso.remove(); }, 9000);
                }
                try {
                    var Ctx = window.AudioContext || window.webkitAudioContext;
                    var ctx = new Ctx();
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = 880;
                    gain.gain.value = 0.08;
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.18);
                } catch (err) { /* el navegador puede bloquear el sonido */ }
            });
        </script>
    @endif

    @livewireScripts
</body>
</html>
