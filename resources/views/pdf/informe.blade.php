<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1c2330; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 22px; border-bottom: 2px solid #cf2027; padding-bottom: 4px; }
        .info-box { margin-bottom: 15px; line-height: 1.6; }
        .reparacion { margin-bottom: 18px; }
        .grilla { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .celda { width: 50%; vertical-align: top; text-align: center; padding: 6px; }
        .celda img { border: 1px solid #ccc; }
        .nota { font-size: 10px; color: #444; margin-top: 3px; }
        .faltante { font-size: 10px; color: #999; padding: 30px 0; border: 1px dashed #ccc; }
        .aviso { color: #777; font-style: italic; }
    </style>
</head>
<body>
    @php
        // Ajusta cada foto a una caja SIN deformarla (DomPDF no entiende object-fit),
        // corrige la rotación del celular y la reduce para que el PDF no pese demasiado.
        $fotoPdf = function ($ruta, $maxAncho = 320, $maxAlto = 230) {
            $archivo = storage_path('app/public/' . ltrim((string) $ruta, '/'));
            if (!is_file($archivo)) {
                return null;
            }
            $contenido = @file_get_contents($archivo);
            if ($contenido === false) {
                return null;
            }

            if (function_exists('imagecreatefromstring')) {
                $img = @imagecreatefromstring($contenido);
                if ($img) {
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($archivo);
                        $giros = [3 => 180, 6 => -90, 8 => 90];
                        $orientacion = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;
                        if (isset($giros[$orientacion])) {
                            $girada = imagerotate($img, $giros[$orientacion], 0);
                            if ($girada) {
                                imagedestroy($img);
                                $img = $girada;
                            }
                        }
                    }

                    $w = imagesx($img);
                    $h = imagesy($img);
                    $escala = min($maxAncho / $w, $maxAlto / $h, 1);
                    $ancho = max(1, (int) round($w * $escala));
                    $alto = max(1, (int) round($h * $escala));

                    // Se guarda al doble del tamaño mostrado: se ve nítida y pesa poco
                    $dw = min($w, $ancho * 2);
                    $dh = max(1, (int) round($h * $dw / $w));
                    $mini = imagecreatetruecolor($dw, $dh);
                    imagecopyresampled($mini, $img, 0, 0, 0, 0, $dw, $dh, $w, $h);
                    ob_start();
                    imagejpeg($mini, null, 82);
                    $jpg = ob_get_clean();
                    imagedestroy($img);
                    imagedestroy($mini);

                    return ['src' => 'data:image/jpeg;base64,' . base64_encode($jpg), 'ancho' => $ancho, 'alto' => $alto];
                }
            }

            $info = @getimagesizefromstring($contenido);
            if (!$info) {
                return null;
            }
            $escala = min($maxAncho / $info[0], $maxAlto / $info[1], 1);

            return [
                'src' => 'data:' . ($info['mime'] ?? 'image/jpeg') . ';base64,' . base64_encode($contenido),
                'ancho' => max(1, (int) round($info[0] * $escala)),
                'alto' => max(1, (int) round($info[1] * $escala)),
            ];
        };

        // Fotos de 2 en 2, cada una con su nota debajo
        $grillaFotos = function ($fotos) use ($fotoPdf) {
            $html = '<table class="grilla">';
            foreach ($fotos->chunk(2) as $fila) {
                $html .= '<tr>';
                foreach ($fila as $f) {
                    $p = $fotoPdf($f->ruta_archivo);
                    $html .= '<td class="celda">';
                    $html .= $p
                        ? '<img src="' . $p['src'] . '" width="' . $p['ancho'] . '" height="' . $p['alto'] . '">'
                        : '<div class="faltante">Foto no disponible</div>';
                    if (!empty($f->descripcion)) {
                        $html .= '<div class="nota">' . e($f->descripcion) . '</div>';
                    }
                    $html .= '</td>';
                }
                if ($fila->count() === 1) {
                    $html .= '<td class="celda"></td>';
                }
                $html .= '</tr>';
            }

            return $html . '</table>';
        };

        // Fotos del estado en que llegó el vehículo (ingreso e inspección)
        $fotosIngreso = collect();
        if ($orden->ingreso_id) {
            $inspeccionIds = \App\Models\Inspeccion::where('ingreso_id', $orden->ingreso_id)->pluck('id');
            $fotosIngreso = \App\Models\Fotografia::where(function ($q) use ($orden) {
                    $q->where('fotografiable_type', \App\Models\Ingreso::class)
                      ->where('fotografiable_id', $orden->ingreso_id);
                })
                ->orWhere(function ($q) use ($inspeccionIds) {
                    $q->where('fotografiable_type', \App\Models\Inspeccion::class)
                      ->whereIn('fotografiable_id', $inspeccionIds);
                })
                ->orderBy('fecha_subida')
                ->get();
        }
    @endphp

    <h1>Informe de Servicio - {{ $orden->numero_orden }}</h1>

    <div class="info-box">
        <strong>Vehiculo:</strong> {{ $orden->vehiculo->placa }} - {{ $orden->vehiculo->marca }} {{ $orden->vehiculo->modelo }}<br>
        <strong>Cliente:</strong> {{ $orden->vehiculo->cliente->nombre }}<br>
        <strong>Fecha de generacion:</strong> {{ $informe->fecha_generacion }}<br>
    </div>

    @if($fotosIngreso->isNotEmpty())
        <h2>Estado del vehiculo al ingresar</h2>
        {!! $grillaFotos($fotosIngreso) !!}
    @endif

    <h2>Diagnostico</h2>
    @forelse($orden->diagnosticos as $diagnostico)
        <p><strong>Problema:</strong> {{ $diagnostico->problema_encontrado }}</p>
        <p><strong>Trabajo a realizar:</strong> {{ $diagnostico->trabajo_a_realizar }}</p>
    @empty
        <p class="aviso">Sin diagnostico registrado.</p>
    @endforelse

    <h2>Reparaciones realizadas</h2>
    @forelse($orden->reparaciones as $reparacion)
        <div class="reparacion">
            <p><strong>Mecanico:</strong> {{ $reparacion->mecanico?->nombre }} {{ $reparacion->mecanico?->apellido }}</p>
            <p><strong>Trabajo realizado:</strong> {{ $reparacion->trabajo_realizado }}</p>
            @if($reparacion->observaciones)
                <p><strong>Observaciones:</strong> {{ $reparacion->observaciones }}</p>
            @endif

            @if($reparacion->fotografias->isNotEmpty())
                {!! $grillaFotos($reparacion->fotografias) !!}
            @endif
        </div>
    @empty
        <p class="aviso">Sin reparaciones registradas.</p>
    @endforelse

    <h2>Pruebas de calidad</h2>
    @forelse($orden->pruebas as $prueba)
        <p><strong>{{ $prueba->prueba_realizada }}:</strong> {{ $prueba->resultado }}</p>
        @if($prueba->observaciones)
            <p>{{ $prueba->observaciones }}</p>
        @endif
    @empty
        <p class="aviso">Sin pruebas registradas.</p>
    @endforelse

    @if($informe->observaciones_finales)
        <h2>Observaciones finales</h2>
        <p>{{ $informe->observaciones_finales }}</p>
    @endif
</body>
</html>
