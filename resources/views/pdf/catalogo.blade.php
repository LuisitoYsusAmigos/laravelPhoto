@php
  $colorPrimario = $colorPrimario ?? '#c7191d';
  $colorSecundario = $colorSecundario ?? '#050505';
  $sizeImage = isset($sizeImage) && $sizeImage > 0 ? (float)$sizeImage : 1.0;
  $mostrarNombreCategorias = $mostrarNombreCategorias ?? false;

  // Medidas de la imagen y tarjeta
  $imgHeightMm = round(26 * $sizeImage, 2);
  if ($imgHeightMm > 32) {
      $imgHeightMm = 32;
  }
  $detailsHeightMm = max(2.0, round(47 - 6 - 8.5 - $imgHeightMm, 2));
@endphp
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

    html, body {
      margin: 0;
      padding: 0;
      background: #dfe1e5;
      color: {{ $colorSecundario }};
      font-family: Arial, Helvetica, sans-serif;
    }

    .catalog-page {
      width: 210mm;
      margin: 0 auto;
      padding: 0;
      background: #f1f2f5;
      page-break-after: always;
    }

    .catalog-page:last-child {
      page-break-after: avoid;
    }

    .catalog-header {
      height: 38mm;
      margin: 0 10mm 2.5mm;
      background: {{ $colorPrimario }};
      border-bottom-right-radius: 6mm;
      position: relative;
      overflow: hidden;
    }

    .brand-block {
      position: absolute;
      top: 0;
      left: 0;
      width: 44mm;
      height: 32mm;
      padding: 4mm 2mm;
      color: #ffffff;
      text-align: center;
      background: {{ $colorSecundario }};
      border-bottom-right-radius: 5mm;
    }

    .brand-symbol {
      display: block;
      margin: 0 auto 1mm;
      font-size: 20pt;
      line-height: 1;
      font-weight: 900;
    }

    .brand-name {
      font-size: 7pt;
      line-height: 1.15;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .header-title {
      position: absolute;
      top: 4mm;
      left: 48mm;
      right: 6mm;
      color: #ffffff;
      line-height: 1.05;
    }

    .header-title .small {
      display: block;
      font-size: 11pt;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 1mm;
      font-weight: 700;
    }

    .header-title .large {
      display: block;
      font-size: 19pt;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .header-ribbon {
      position: absolute;
      left: 0;
      top: 32mm;
      width: 140mm;
      height: 6mm;
      line-height: 6mm;
      padding: 0 4mm;
      color: #ffffff;
      background: {{ $colorSecundario }};
      border-top-right-radius: 3mm;
      font-size: 7.5pt;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
    }

    .products-table {
      width: 190mm;
      margin: 0 10mm;
      border-collapse: separate;
      border-spacing: 2.5mm;
      table-layout: fixed;
    }

    .products-table tr {
      page-break-inside: avoid;
      page-break-after: avoid;
    }

    .products-table td {
      width: 33.33%;
      height: 48mm;
      vertical-align: top;
      background: #ffffff;
      padding: 0;
      border: 1px solid #e0e2e6;
    }

    .product-card {
      width: 100%;
      height: 48mm;
      overflow: hidden;
      background: #ffffff;
      position: relative;
    }

    .product-heading {
      height: 6mm;
      line-height: 6mm;
      padding: 0 2.5mm;
      color: #ffffff;
      background: {{ $colorSecundario }};
      font-size: 7.5pt;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .product-details {
      height: {{ $detailsHeightMm }}mm;
      line-height: 3.2mm;
      padding: 1mm 2.5mm 0;
      font-size: 6.5pt;
      color: #333333;
      overflow: hidden;
    }

    .product-image {
      height: {{ $imgHeightMm }}mm;
      line-height: {{ $imgHeightMm }}mm;
      text-align: center;
      overflow: hidden;
    }

    .product-image img {
      max-height: {{ $imgHeightMm }}mm;
      max-width: {{ min(98, round(90 * $sizeImage, 1)) }}%;
      height: auto;
      object-fit: contain;
      display: inline-block;
      vertical-align: middle;
    }

    .no-image {
      display: inline-block;
      vertical-align: middle;
      line-height: {{ $imgHeightMm }}mm;
      font-size: 7.5pt;
      color: #9a9a9a;
    }

    .product-price-bar {
      height: 8.5mm;
      line-height: 8.5mm;
      padding-left: 2.5mm;
      position: relative;
    }

    .product-price {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13pt;
      line-height: 8.5mm;
      font-weight: 800;
      color: {{ $colorSecundario }};
    }

    .product-price .currency {
      font-size: 8.5pt;
      font-weight: 600;
      margin-right: 1mm;
    }

    .corner-accent {
      position: absolute;
      right: 0;
      bottom: 0;
      width: 0;
      height: 0;
      border-style: solid;
      border-width: 0 0 8.5mm 8.5mm;
      border-color: transparent transparent {{ $colorPrimario }} transparent;
    }

    .empty-card {
      background: #fafafa;
      border: 1px dashed #d5d8dc;
    }

    .catalog-footer {
      width: 190mm;
      margin: 2mm 10mm 0;
      height: 12mm;
      background: {{ $colorSecundario }};
      position: relative;
    }

    .footer-red {
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 165mm;
      background: {{ $colorPrimario }};
      border-top-right-radius: 6mm;
      padding: 0 6mm;
      color: #ffffff;
      font-size: 7.5pt;
      line-height: 12mm;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .footer-globe {
      font-size: 11pt;
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
<body>@php
    $chunks = $productos->chunk(9);
  @endphp@if($chunks->isEmpty())<main class="catalog-page">
      <header class="catalog-header">
        <div class="brand-block">
          <span class="brand-symbol">{{ mb_strtoupper(mb_substr($sucursal->nombre_sucursal ?? 'S', 0, 1)) }}</span>
          <div class="brand-name">{{ $sucursal->nombre_sucursal ?? 'Catálogo' }}</div>
        </div>
        <div class="header-title">
          <span class="small">
            @if(!empty($mostrarNombreCategorias) && !empty($categoriaTexto))
              {{ $categoriaTexto }}
            @elseif(!empty($categoria))
              {{ $categoria->nombre }}
            @else
              Catálogo de
            @endif
          </span>
          <span class="large">
            @if(!empty($mostrarNombreCategorias) && !empty($subCategoriaTexto))
              {{ $subCategoriaTexto }}
            @elseif(!empty($subCategoria))
              {{ $subCategoria->nombre }}
            @else
              Productos
            @endif
          </span>
        </div>
        <div class="header-ribbon">
          No se encontraron productos disponibles
        </div>
      </header>
      <div style="text-align: center; margin-top: 40mm; font-size: 13pt; color: #777;">
        No hay productos visibles para los filtros seleccionados.
      </div>
      <footer class="catalog-footer">
        <div class="footer-red">
          <span class="footer-globe">&#9678;</span>
          <span>
            {{ $sucursal->nombre_sucursal ?? '' }}
            @if(!empty($sucursal->direccion)) + {{ $sucursal->direccion }} @endif
            @if(!empty($sucursal->contactos)) + cel : {{ $sucursal->contactos }} @endif
            &bull; Catálogo Digital
          </span>
        </div>
      </footer>
    </main>
  @else
    @foreach($chunks as $chunkIndex => $chunk)
      <main class="catalog-page">
      <header class="catalog-header">
        <div class="brand-block">
          <span class="brand-symbol">{{ mb_strtoupper(mb_substr($sucursal->nombre_sucursal ?? 'S', 0, 1)) }}</span>
          <div class="brand-name">{{ $sucursal->nombre_sucursal ?? 'Catálogo' }}</div>
        </div>

        <div class="header-title">
          <span class="small">
            @if(!empty($mostrarNombreCategorias) && !empty($categoriaTexto))
              {{ $categoriaTexto }}
            @elseif(!empty($categoria))
              {{ $categoria->nombre }}
            @else
              Catálogo de
            @endif
          </span>
          <span class="large">
            @if(!empty($mostrarNombreCategorias) && !empty($subCategoriaTexto))
              {{ $subCategoriaTexto }}
            @elseif(!empty($subCategoria))
              {{ $subCategoria->nombre }}
            @else
              Productos
            @endif
          </span>
        </div>

        <div class="header-ribbon">
          @if(!empty($mostrarNombreCategorias))
            @if(!empty($subCategoriaTexto) && $subCategoriaTexto !== 'Todas las subcategorías')
              Subcategorías: {{ $subCategoriaTexto }}
            @elseif(!empty($categoriaTexto) && $categoriaTexto !== 'Todas las categorías')
              Categorías: {{ $categoriaTexto }}
            @else
              Productos disponibles en nuestra tienda
            @endif
          @else
            @if(!empty($subCategoria))
              Subcategoría: {{ $subCategoria->nombre }}
            @elseif(!empty($categoria))
              Categoría: {{ $categoria->nombre }}
            @else
              Productos disponibles en nuestra tienda
            @endif
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
                      <span class="no-image">Sin imagen</span>
                    @endif
                  </div>
                  <div class="product-price-bar">
                    <div class="product-price">
                      <span class="currency">Bs.</span>{{ number_format($producto->precioVenta / 100, 2) }}
                    </div>
                    <div class="corner-accent"></div>
                  </div>
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
          <span>
            {{ $sucursal->nombre_sucursal ?? '' }}
            @if(!empty($sucursal->direccion)) + {{ $sucursal->direccion }} @endif
            @if(!empty($sucursal->contactos)) + cel : {{ $sucursal->contactos }} @endif
            &bull; Catálogo Digital
          </span>
        </div>
      </footer>
    </main>
    @endforeach
  @endif
</body>
</html>
