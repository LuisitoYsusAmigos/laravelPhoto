<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Recibo Administrativo - Detalle de Producción</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      font-size: 13px;
    }

    .recibo-container {
      max-width: 800px;
      margin: auto;
      border: 1px solid #000;
      padding: 20px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
    }

    .title {
      text-align: center;
      font-weight: bold;
    }

    .info-empresa {
      font-size: 12px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    table,
    th,
    td {
      border: 1px solid #000;
    }

    th,
    td {
      padding: 8px;
      text-align: left;
    }

    .no-border {
      border: none;
    }

    .right {
      text-align: right;
    }

    .detalle-pago td {
      border: none;
      padding: 4px;
    }

    .material-box {
      margin-top: 10px;
      border: 1px dashed #444;
      padding: 10px;
      background-color: #f9f9f9;
    }

    .footer-line {
      border-top: 1px solid black;
      margin-top: 40px;
      text-align: center;
      font-size: 10px;
      padding-top: 5px;
    }

    h3 {
      margin-top: 25px;
      text-transform: uppercase;
      font-size: 14px;
      background: #eee;
      padding: 5px;
    }
  </style>
</head>

<body>
  <div class="recibo-container">
    <div class="header">
      <div class="info-empresa">
        <strong>PhotoStore / Diego Sandoval</strong><br>
        Dir: Bartolo la Pampa, Av. La Paz y Potosí<br>
        Cel: +591 7299 3550 / 7299 1362<br>
        Correo: fotografiatarija@gmail.com
      </div>
      <div class="title">
        <h2 style="margin:0">RECIBO ADMINISTRATIVO</h2>
        <p><strong>Nro:</strong> {{ $venta['id'] }}</p>
      </div>
    </div>

    {{-- Información de la Venta --}}
    <table>
      <tr>
        <th>Fecha de Venta</th>
        <th>Cliente</th>
        <th>Teléfono</th>
        <th>Sucursal</th>
      </tr>
      <tr>
        <td>{{ \Carbon\Carbon::parse($venta['fecha'])->format('d/m/Y H:i') }}</td>
        <td>{{ $venta['cliente']['nombre'] }} {{ $venta['cliente']['apellido'] }}</td>
        <td>{{ $venta['cliente']['telefono'] }}</td>
        <td>{{ $venta['sucursal']['lugar'] }}</td>
      </tr>
    </table>

    {{-- Productos normales --}}
    @if(!empty($venta['detalle_venta_productos']))
      <h3>Productos</h3>
      <table>
        <tr>
          <th>ID</th>
          <th>Producto</th>
          <th>Cant.</th>
          <th>P. Unitario</th>
          <th>Total</th>
        </tr>
        @foreach ($venta['detalle_venta_productos'] as $producto)
          <tr>
            <td>{{ $producto['id'] }}</td>
            <td>{{ $producto['nombreProducto'] ?? 'Producto General' }}</td>
            <td>{{ $producto['cantidad'] }}</td>
            <td>{{ number_format($producto['precio'] / 100 / $producto['cantidad'], 2) }}</td>
            <td>{{ number_format($producto['precio'] / 100, 2) }}</td>
          </tr>
        @endforeach
      </table>
    @endif

    {{-- Productos personalizados --}}
    @if(!empty($venta['detalle_venta_personalizadas']))
      <h3>Productos Personalizados (Marcos)</h3>
      <table>
        <tr>
          <th>ID</th>
          <th>Descripción</th>
          <th>Dimensiones Marco</th>
          <th>Total Item</th>
        </tr>
        @foreach ($venta['detalle_venta_personalizadas'] as $personalizado)
          <tr>
            <td>{{ $personalizado['id'] }}</td>
            <td>Marco personalizado</td>
            <td>{{ $personalizado['lado_a'] / 10 }} x {{ $personalizado['lado_b'] / 10 }} cm</td>
            <td>{{ number_format(($venta['precioTotal'] - $venta['precioProducto']) / 100, 2) }}</td>
          </tr>
        @endforeach
      </table>

      {{-- SECCIÓN DE MATERIALES Y CORTES --}}
      <h3 style="background: #333; color: #fff;">Guía de Cortes y Materiales Utilizados</h3>
      @foreach ($venta['detalle_venta_personalizadas'] as $personalizado)
        @foreach($personalizado['materiales_venta_personalizadas'] as $material)
          @php
            $tipoMaterial = "DESCONOCIDO";
            $descMaterial = "N/A";
            $codigoMaterial = "N/A";
            $dimOriginal = "N/A";

            if ($material['stock_varilla']) {
              $tipoMaterial = "VARILLA";
              $descMaterial = $personalizado['descripcion_materia_prima_varillas'] ?? 'N/A';
              $codigoMaterial = $personalizado['codigo_materia_prima_varillas'] ?? 'N/A';
              $dimOriginal = "Largo: " . ($material['stock_varilla']['largo'] / 10) . " cm";
            } elseif ($material['stock_trupan']) {
              $tipoMaterial = "TRUPAN";
              $descMaterial = $personalizado['descripcion_materia_prima_trupans'] ?? 'N/A';
              $codigoMaterial = $personalizado['codigo_materia_prima_trupans'] ?? 'N/A';
              $dimOriginal = ($material['stock_trupan']['largo'] / 10) . " x " . ($material['stock_trupan']['alto'] / 10) . " cm";
            } elseif ($material['stock_vidrio']) {
              $tipoMaterial = "VIDRIO";
              $descMaterial = $personalizado['descripcion_materia_prima_vidrios'] ?? 'N/A';
              $codigoMaterial = $personalizado['codigo_materia_prima_vidrios'] ?? 'N/A';
              $dimOriginal = ($material['stock_vidrio']['largo'] / 10) . " x " . ($material['stock_vidrio']['alto'] / 10) . " cm";
            } elseif ($material['stock_contorno']) {
              $tipoMaterial = "CONTORNO";
              $descMaterial = $personalizado['descripcion_materia_prima_contornos'] ?? 'N/A';
              $codigoMaterial = $personalizado['codigo_materia_prima_contornos'] ?? 'N/A';
              $dimOriginal = ($material['stock_contorno']['largo'] / 10) . " x " . ($material['stock_contorno']['alto'] / 10) . " cm";
            }

            $numCortes = count($material['cortes']);
          @endphp

          <div class="material-box">
            <strong>{{ $tipoMaterial }}</strong><br>
            <strong>DESCRIPCIÓN:</strong> {{ $descMaterial }}<br>
            <strong>CÓDIGO:</strong> {{ $codigoMaterial }}<br>
            <strong>TAMAÑO ORIGINAL:</strong> {{ $dimOriginal }}<br>
            <br>
            <span style="text-decoration: underline;">
              @if($numCortes > 1)
                De este objeto deben salir {{ $numCortes }} cortes de medidas:
              @else
                De este objeto debe salir 1 corte de medida:
              @endif
            </span>
            <ul style="margin: 5px 0 0 15px; padding: 0;">
              @foreach($material['cortes'] as $corte)
                <li>
                  <strong>{{ $corte['largo_corte'] / 10 }}
                    {{ $corte['ancho_corte'] ? 'x ' . ($corte['ancho_corte'] / 10) : '' }} cm</strong>
                  | Tipo: {{ ucfirst($corte['tipo_corte']) }} | Ref: {{ $corte['origen'] }}
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      @endforeach
    @endif

    {{-- Resumen de Pago --}}
    @php
      $aCuenta = $venta['precioTotal'] - $venta['saldo'];
    @endphp

    <table class="detalle-pago" style="margin-top: 30px;">
      <tr>
        <td><strong>Pagado (A cuenta):</strong></td>
        <td>{{ number_format($aCuenta / 100, 2) }}</td>
        <td class="right"><strong>Descuento:</strong></td>
        <td>{{ number_format($venta['descuento'] / 100, 2) }}</td>
      </tr>
      <tr>
        <td style="color: red;"><strong>Saldo Pendiente:</strong></td>
        <td style="color: red;">{{ number_format($venta['saldo'] / 100, 2) }}</td>
        <td class="no-border"></td>
        <td class="no-border"></td>
      </tr>
      <tr>
        <td class="no-border"></td>
        <td class="no-border"></td>
        <td class="right" style="font-size: 16px;"><strong>TOTAL VENTA:</strong></td>
        <td style="font-size: 16px;"><strong>{{ number_format($venta['precioTotal'] / 100, 2) }}</strong></td>
      </tr>
    </table>

    <div class="footer-line">
      COPIA ADMINISTRATIVA - PHOTOSTORE TARIJA
    </div>
  </div>
</body>

</html>