<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de Caja Mensual</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            padding: 2rem;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
            padding: 1.5rem;
        }

        .card h2 {
            font-size: 1.5rem;
            margin: 0 0 0.5rem;
        }

        .month {
            font-size: 1rem;
            color: #444;
            margin-bottom: 1rem;
        }

        .summary {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 1rem;
        }

        .summary h3 {
            margin-top: 0;
            font-size: 1.1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.3rem 0;
        }

        .separator {
            border-top: 1px solid #ddd;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>

    <div class="card">
        <h2>Resumen de Caja Mensual</h2>
        <p class="month">
            {{ \Carbon\Carbon::parse($caja['mes'] . '-01')->translatedFormat('F \\de Y') }}
        </p>

        <div class="summary">
            <h3>Totales del mes</h3>

            <div class="summary-item">
                <span>Cajas cerradas:</span>
                <span>{{ $caja['total_cajas'] }}</span>
            </div>

            <div class="summary-item">
                <span>Total ingresos:</span>
                <span><strong>Bs {{ number_format((float) $caja['total_ingresos'], 2, '.', ',') }}</strong></span>
            </div>

            <div class="summary-item">
                <span>Total ventas:</span>
                <span>{{ $caja['total_ventas'] }}</span>
            </div>

            <div class="separator"></div>

            @php
                $formasPago = [
                    '1' => 'Efectivo',
                    '2' => 'Tarjeta',
                    '3' => 'Transferencia',
                    '4' => 'QR',
                    '5' => 'Otros'
                ];
            @endphp

            @foreach ($formasPago as $clave => $label)
                @if (isset($caja['detalle_acumulado'][$clave]))
                    <div class="summary-item">
                        <span>{{ $label }}:</span>
                        <span>Bs {{ number_format((float) $caja['detalle_acumulado'][$clave], 2, '.', ',') }}</span>
                    </div>
                @endif
            @endforeach

        </div>
    </div>

</body>
</html>
