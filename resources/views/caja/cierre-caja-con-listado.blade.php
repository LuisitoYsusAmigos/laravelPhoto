<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja Diario</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            padding: 2rem;
            color: #333;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Aumentado un poco para que la tabla luzca mejor */
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
            margin-bottom: 1.5rem;
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

        /* Estilos para la tabla de pagos */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.85rem;
        }

        .payments-table th {
            text-align: left;
            background-color: #f1f5f9;
            padding: 8px;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
        }

        .payments-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .monto-negrita {
            font-weight: 600;
            color: #1e293b;
        }

        .badge-pago {
            background-color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="card">
        <h2>Cierre de Caja Diario</h2>
        <p class="date">
            {{ \Carbon\Carbon::parse($caja['fecha'])->translatedFormat('l, d \\de F \\de Y') }}
        </p>

        <div class="summary">
            <h3>Resumen del día</h3>

            <div class="summary-item">
                <span>Total de ventas:</span>
                <span><strong>Bs {{ number_format((float) $caja['total']/100, 2, '.', ',') }}</strong></span>
            </div>

            <div class="summary-item">
                <span>Número de ventas:</span>
                <span>{{ $caja['ventas'] }}</span>
            </div>

            <div class="summary-item">
                <span>Observaciones:</span>
                <span>{{ $caja['observaciones'] }}</span>
            </div>

            <div class="separator"></div>

            @php
                $formasPago = \App\Models\FormaDePago::pluck('nombre', 'id')->toArray();
                $detalleDecodificado = is_string($caja['detalle']) ? json_decode($caja['detalle'], true) : $caja['detalle'];
            @endphp

            @foreach ($formasPago as $codigo => $label)
                @if (!empty($detalleDecodificado[$codigo]))
                    <div class="summary-item">
                        <span>{{ $label }}:</span>
                        <span>Bs {{ number_format((float) $detalleDecodificado[$codigo]/100, 2, '.', ',') }}</span>
                    </div>
                @endif
            @endforeach
        </div>

        <h3>Detalle de Movimientos</h3>
<table class="payments-table">
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Venta</th> <th>Método</th>
            <th class="text-right">Monto</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pagos as $pago)
        <tr>
            <td>
                {{ $pago->nombre_cliente }} {{ $pago->apellido_cliente }}
            </td>
            <td>
                {{-- Lógica de etiquetas de venta --}}
                @if($pago->precioProducto > 0 && $pago->precioPerzonalizado > 0)
                    Producto y Marcos
                @elseif($pago->precioPerzonalizado > 0)
                    Marco Perzonalizado
                @elseif($pago->precioProducto > 0)
                    Producto
                @else
                    Otro
                @endif
            </td>
            <td>
                <span class="badge-pago">{{ $pago->nombre_forma_pago }}</span>
            </td>
            <td class="text-right monto-negrita">
                Bs {{ number_format($pago->monto/100, 2) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
    </div>

</body>
</html>