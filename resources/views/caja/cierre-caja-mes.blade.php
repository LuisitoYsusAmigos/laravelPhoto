<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja mensual</title>
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
            max-width: 400px;
            margin: auto;
            padding: 1.5rem;
        }

        .card h2 {
            font-size: 1.4rem;
            margin: 0;
        }

        .date {
            font-size: 0.95rem;
            color: #666;
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
        <h2>Cierre de Caja Mensual</h2>
        <p class="date">
            {{ \Carbon\Carbon::parse($caja['fecha'])->translatedFormat('l, d \\de F \\de Y') }}
        </p>

        <div class="summary">
            <h3>Resumen del Mes</h3>

            <div class="summary-item">
                <span>Total de ventas:</span>
                <span><strong>Bs {{ number_format((float) $caja['total'], 2, '.', ',') }}</strong></span>
            </div>

            <div class="summary-item">
                <span>Número de ventas:</span>
                <span>{{ $caja['ventas'] }}</span>
            </div>

            <div class="separator"></div>

            @php
                $formasPago = [
                    '1' => 'Efectivo',
                    '2' => 'Tarjeta',
                    '3' => 'Transferencia',
                ];
            @endphp

            @foreach ($formasPago as $codigo => $label)
                @if (!empty($caja['detalle'][$codigo]))
                    <div class="summary-item">
                        <span>{{ $label }}:</span>
                        <span>Bs {{ number_format((float) $caja['detalle'][$codigo], 2, '.', ',') }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

</body>
</html>
