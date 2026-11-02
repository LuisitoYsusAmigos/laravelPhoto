<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Catálogo de Productos - Photostore</title>
  <style>
    * {
      box-sizing: border-box;
    }

    @page {
      size: A4 portrait;
      margin: 0;
    }

    body {
      margin: 0;
      padding: 0;
      background: #dfe1e5;
      color: #050505;
      font-family: Arial, Helvetica, sans-serif;
    }

    .catalog-page {
      position: relative;
      width: 210mm;
      min-height: 285mm;
      margin: 0 auto;
      padding: 0 0 16mm 0;
      overflow: hidden;
      background: #f1f2f5;
      page-break-after: always;
      break-after: page;
    }

    .catalog-page:last-child {
      page-break-after: avoid;
      break-after: avoid;
    }

    .catalog-header {
      position: relative;
      height: 52mm;
      margin: 0 10mm 4mm;
      background: #c7191d;
      border-bottom-right-radius: 10mm;
    }

    .brand-block {
      position: absolute;
      z-index: 2;
      top: 0;
      left: -10mm;
      width: 48mm;
      height: 40mm;
      padding: 6mm 4mm 4mm;
      color: #ffffff;
      text-align: center;
      background: #050505;
      clip-path: polygon(0 0, 78% 0, 100% 50%, 78% 100%, 0 100%);
    }

    .brand-symbol {
      display: block;
      margin: 0 auto 1mm;
      font-size: 26pt;
      line-height: 1;
      font-weight: 900;
      transform: skew(-12deg);
    }

    .brand-name {
      font-size: 8pt;
      line-height: 1.15;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .header-title {
      position: absolute;
      top: 12mm;
      left: 42mm;
      right: 8mm;
      color: #ffffff;
      font-weight: 700;
      line-height: 1;
    }

    .header-title .small {
      display: block;
      font-size: 18pt;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .header-title .large {
      display: block;
      font-size: 26pt;
      text-transform: uppercase;
    }

    .header-decoration {
      position: absolute;
      top: 2mm;
      right: 4mm;
      color: rgba(255, 255, 255, 0.4);
      font-size: 28pt;
      transform: rotate(-20deg);
    }

    .header-ribbon {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 130mm;
      height: 8mm;
      padding: 1.5mm 4mm;
      color: #ffffff;
      background: #050505;
      clip-path: polygon(0 0, 100% 0, 96% 100%, 0 100%);
      font-size: 9pt;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .products-table {
      width: 190mm;
      margin: 0 10mm;
      border-collapse: separate;
      border-spacing: 3.5mm;
      table-layout: fixed;
    }

    .products-table td {
      width: 33.33%;
      height: 57mm;
      max-height: 57mm;
      vertical-align: top;
      background: #ffffff;
      padding: 0;
      position: relative;
    }

    .product-card {
      position: relative;
      width: 100%;
      height: 57mm;
      padding: 0 3mm 2mm;
      overflow: hidden;
      background: #ffffff;
    }

    .product-heading {
      position: relative;
      width: calc(100% + 3mm);
      height: 7.5mm;
      margin-left: -3mm;
      padding: 1.2mm 3mm;
      color: #ffffff;
      background: #050505;
      clip-path: polygon(0 0, 100% 0, 96% 100%, 0 100%);
      font-size: 8.5pt;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .product-details {
      position: relative;
      z-index: 1;
      margin-top: 1.5mm;
      font-size: 7.5pt;
      line-height: 1.2;
      color: #444;
      height: 7.5mm;
      overflow: hidden;
    }

    .product-image {
      position: absolute;
      left: 3mm;
      right: 3mm;
      top: 17mm;
      bottom: 9mm;
      text-align: center;
      overflow: hidden;
    }

    .product-image img {
      max-width: 100%;
      max-height: 27mm;
      height: auto;
      object-fit: contain;
      display: inline-block;
      vertical-align: middle;
    }

    .product-price {
      position: absolute;
      z-index: 2;
      left: 3mm;
      bottom: 1mm;
      font-size: 16pt;
      line-height: 1;
      font-weight: 800;
      font-style: italic;
      color: #050505;
    }

    .product-price .currency {
      margin-right: 1mm;
      font-size: 10pt;
      font-style: normal;
      vertical-align: 2px;
    }

    .corner-accent {
      position: absolute;
      right: 0;
      bottom: 0;
      width: 10mm;
      height: 10mm;
      background: #c7191d;
      clip-path: polygon(100% 0, 100% 100%, 0 100%);
    }

    .empty-card {
      background: #fafafa;
      border: 1px dashed #e0e0e0;
    }

    .catalog-footer {
      position: absolute;
      right: 0;
      bottom: 0;
      left: 0;
      height: 15mm;
      background: #050505;
    }

    .footer-red {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 145mm;
      height: 15mm;
      padding-left: 10mm;
      color: #ffffff;
      background: #c7191d;
      border-top-right-radius: 10mm;
      font-size: 9.5pt;
      line-height: 15mm;
    }

    .footer-globe {
      font-size: 13pt;
      margin-right: 2mm;
      vertical-align: middle;
    }

    @media screen {
      body {
        padding: 24px;
      }
      .catalog-page {
        margin: 0 auto 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
      }
    }
  </style>
</head>
<body>

  @php
    $chunks = $productos->chunk(9);
  @endphp

  @if($chunks->isEmpty())
    <main class="catalog-page">
      <header class="catalog-header">
        <div class="brand-block">
          <span class="brand-symbol">P</span>
          <div class="brand-name">Photostore<br>Tarija</div>
        </div>
        <div class="header-title">
          <span class="small">Catálogo de</span>
          <span class="large">Productos</span>
        </div>
        <div class="header-ribbon">
          No se encontraron productos disponibles
        </div>
      </header>
      <div style="text-align: center; margin-top: 50mm; font-size: 14pt; color: #777;">
        No hay productos visibles para los filtros seleccionados.
      </div>
      <footer class="catalog-footer">
        <div class="footer-red">
          <span class="footer-globe">&#9678;</span>
          <span>Photostore - Catálogo de productos</span>
        </div>
      </footer>
    </main>
  @else
    @foreach($chunks as $chunkIndex => $chunk)
      <main class="catalog-page">
        <header class="catalog-header">
          <div class="brand-block">
            <span class="brand-symbol">P</span>
            <div class="brand-name">Photostore<br>Tarija</div>
          </div>

          <div class="header-title">
            <span class="small">
              @if(!empty($categoria))
                {{ $categoria->nombre }}
              @else
                Catálogo de
              @endif
            </span>
            <span class="large">
              @if(!empty($subCategoria))
                {{ $subCategoria->nombre }}
              @else
                Productos
              @endif
            </span>
          </div>

          <div class="header-decoration" aria-hidden="true">&#10022;</div>

          <div class="header-ribbon">
            @if(!empty($subCategoria))
              Subcategoría: {{ $subCategoria->nombre }}
            @elseif(!empty($categoria))
              Categoría: {{ $categoria->nombre }}
            @else
              Productos disponibles en nuestra tienda
            @endif
            &bull; Pág. {{ $chunkIndex + 1 }} / {{ $chunks->count() }}
          </div>
        </header>

        @php
          $rows = $chunk->chunk(3);
        @endphp

        <table class="products-table" aria-label="Productos del catálogo">
          @foreach($rows as $row)
            <tr>
              @foreach($row as $producto)
                @php
                  $imgPath = null;
                  if (!empty($producto->imagen)) {
                      $localPath = public_path($producto->imagen);
                      if (file_exists($localPath)) {
                          $imgPath = !empty($esPdf) ? $localPath : asset($producto->imagen);
                      }
                  }
                  $titulo = !empty($producto->codigo) ? $producto->codigo : 'ART-' . str_pad($producto->id, 3, '0', STR_PAD_LEFT);
                @endphp
                <td>
                  <article class="product-card">
                    <div class="product-heading" title="{{ $titulo }}">{{ $titulo }}</div>
                    <div class="product-details" title="{{ $producto->descripcion }}">{{ $producto->descripcion }}</div>
                    <div class="product-image">
                      @if($imgPath)
                        <img src="{{ $imgPath }}" alt="{{ $producto->descripcion }}">
                      @else
                        <span style="color: #9a9a9a; font-size: 8pt; line-height: 25mm;">Sin imagen</span>
                      @endif
                    </div>
                    <div class="product-price">
                      <span class="currency">Bs.</span>{{ number_format($producto->precioVenta / 100, 2) }}
                    </div>
                    <div class="corner-accent"></div>
                  </article>
                </td>
              @endforeach

              {{-- Rellenar celdas vacías si la última fila tiene menos de 3 productos --}}
              @for($i = $row->count(); $i < 3; $i++)
                <td>
                  <article class="product-card empty-card"></article>
                </td>
              @endfor
            </tr>
          @endforeach

          {{-- Rellenar filas vacías si la página tiene menos de 3 filas --}}
          @for($r = $rows->count(); $r < 3; $r++)
            <tr>
              <td><article class="product-card empty-card"></article></td>
              <td><article class="product-card empty-card"></article></td>
              <td><article class="product-card empty-card"></article></td>
            </tr>
          @endfor
        </table>

        <footer class="catalog-footer">
          <div class="footer-red">
            <span class="footer-globe">&#9678;</span>
            <span>Photostore Tarija &bull; Catálogo Digital</span>
          </div>
        </footer>
      </main>
    @endforeach
  @endif

</body>
</html>
