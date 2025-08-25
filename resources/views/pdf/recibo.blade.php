<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recibo de Pago</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      font-size: 14px;
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
    }

    .title {
      text-align: center;
      margin-top: 10px;
      font-weight: bold;
    }

    .info-empresa {
      font-size: 13px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table, th, td {
      border: 1px solid #000;
    }

    th, td {
      padding: 6px;
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

    .footer-line {
      border-top: 1px solid black;
      margin-top: 40px;
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
        <h2>Recibo de Pago</h2>
        <p><strong>Número de Recibo:</strong> {{ $venta['id'] }}</p>
      </div>
    </div>

    <table>
      <tr>
        <th>Fecha</th>
        <th>Entrega</th>
        <th>Cliente</th>
        <th>Teléfono</th>
        <th>Sucursal</th>
      </tr>
      <tr>
        <td>{{ \Carbon\Carbon::parse($venta['fecha'])->format('Y-m-d') }}</td>
        <td>{{ \Carbon\Carbon::parse($venta['fechaEntrega'])->format('Y-m-d') }}</td>
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
        <th>Precio Unitario</th>
        <th>Total</th>
      </tr>
      @foreach ($venta['detalle_venta_productos'] as $producto)
      <tr>
        <td>{{ $producto['id'] }}</td>
        <td>Producto ID {{ $producto['idProducto'] }}</td>
        <td>{{ $producto['cantidad'] }}</td>
        <td>{{ number_format($producto['precio'], 2) }}</td>
        <td>{{ number_format($producto['precio'] * $producto['cantidad'], 2) }}</td>
      </tr>
      @endforeach
    </table>
    @endif

    {{-- Productos personalizados --}}
    @if(!empty($venta['detalle_venta_personalizadas']))
    <h3>Productos Personalizados</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Descripción</th>
        <th>Dimensiones</th>
        <th>Materiales</th>
      </tr>
      @foreach ($venta['detalle_venta_personalizadas'] as $personalizado)
      <tr>
        <td>{{ $personalizado['id'] }}</td>
        <td>Marco personalizado</td>
        <td>{{ $personalizado['lado_a'] }} x {{ $personalizado['lado_b'] }} mm</td>
        <td>
          @if($personalizado['materia_prima_varilla'])
            Varilla: {{ $personalizado['materia_prima_varilla']['descripcion'] }}<br>
          @endif
          @if($personalizado['materia_prima_trupan'])
            Trupan: {{ $personalizado['materia_prima_trupan']['descripcion'] }}<br>
          @endif
          @if($personalizado['materia_prima_vidrio'])
            Vidrio: {{ $personalizado['materia_prima_vidrio']['descripcion'] }}<br>
          @endif
          @if($personalizado['materia_prima_contorno'])
            Contorno: {{ $personalizado['materia_prima_contorno']['descripcion'] }}
          @endif
        </td>
      </tr>
      @endforeach
    </table>
    @endif

    @php
      $subtotal = $venta['precioProducto'];
      $descuento = $venta['precioProducto'] - $venta['precioTotal'];
      $aCuenta = $venta['precioTotal'] - $venta['saldo'];
    @endphp

    <table class="detalle-pago">
      <tr>
        <td><strong>A cuenta</strong></td>
        <td>{{ number_format($aCuenta, 2) }}</td>
        <td class="right"><strong>Sub Total</strong></td>
        <td>{{ number_format($subtotal, 2) }}</td>
      </tr>
      <tr>
        <td><strong>Saldo</strong></td>
        <td>{{ number_format($venta['saldo'], 2) }}</td>
        <td class="right"><strong>Descuento</strong></td>
        <td>{{ number_format($descuento, 2) }}</td>
      </tr>
      <tr>
        <td class="no-border"></td>
        <td class="no-border"></td>
        <td class="right"><strong>Total</strong></td>
        <td>{{ number_format($venta['precioTotal'], 2) }}</td>
      </tr>
    </table>

    <div class="footer-line"></div>
  </div>
</body>
</html>
