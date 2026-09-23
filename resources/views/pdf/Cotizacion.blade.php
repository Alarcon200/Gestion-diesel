

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .info-box { margin-bottom: 15px; }
        .totales { margin-top: 15px; text-align: right; }
        .totales p { margin: 4px 0; }
        .total-final { font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Cotizacion - {{ $cotizacion->orden->numero_orden ?? 'Sin orden asignada' }}</h1>

    <div class="info-box">
        @php
            $vehiculo = $cotizacion->orden->vehiculo ?? $cotizacion->vehiculo ?? null;
        @endphp
        <strong>Vehiculo:</strong> {{ $vehiculo->placa ?? '-' }} - {{ $vehiculo->marca ?? '' }} {{ $vehiculo->modelo ?? '' }}<br>
        <strong>Cliente:</strong> {{ $vehiculo->cliente->nombre ?? '-' }}<br>
        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($cotizacion->fecha_creacion)->format('d/m/Y') }}<br>
        <strong>Estado:</strong> {{ ucfirst($cotizacion->estado) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Descripcion</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cotizacion->detalles as $detalle)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $detalle->tipo)) }}</td>
                <td>{{ $detalle->descripcion }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>${{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td>
                <td>${{ number_format($detalle->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <p>Subtotal mano de obra: ${{ number_format($cotizacion->subtotal_mano_obra, 0, ',', '.') }}</p>
        <p>Subtotal repuestos: ${{ number_format($cotizacion->subtotal_repuestos, 0, ',', '.') }}</p>
        <p class="total-final">Total: ${{ number_format($cotizacion->total, 0, ',', '.') }}</p>
    </div>
</body>
</html>
