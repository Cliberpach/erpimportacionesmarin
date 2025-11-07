<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte Caja Movimiento</title>
    <link rel="icon" href="{{ base_path() . '/img/siscom.ico' }}" />

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #2C3E50;
            background-color: #FFFFFF;
            font-size: 12px;
        }

        /*======== CABECERA =======*/
        .nombre-empresa {
            text-transform: uppercase;
            font-weight: bold;
            font-size: 14px;
            color: #2980B9;
            margin: 0;
            padding: 0;
        }

        .direccion-empresa {
            text-transform: uppercase;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .info-empresa {
            font-size: 11px;
            color: #2980B9;
            margin: 0;
            padding: 0;
        }

        /*======== TBL INFORMACIÓN =========== */
        .informacion {
            width: 100%;
            border: 1.5px solid #2980B9;
            margin-top: 15px;
            border-radius: 6px;
            padding: 5px;
            background-color: #f9fafe;
            /* fondo muy suave azul */
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .tbl-informacion {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* fija el ancho de las columnas */
        }

        .tbl-informacion td {
            padding: 6px 8px;
            /* reduce padding para no desbordar */
            vertical-align: middle;
            word-wrap: break-word;
            /* evita que texto largo salga del td */
        }

        .tbl-informacion td:first-child {
            font-weight: bold;
            text-transform: uppercase;
            color: #2980B9;
            width: 18%;
            /* ajustado para evitar desbordes */
        }

        .tbl-informacion td:nth-child(2) {
            text-align: center;
            font-weight: bold;
            width: 4%;
            /* ajustado */
            color: #2980B9;
        }

        .tbl-informacion td:nth-child(3) {
            color: #333;
            width: 78%;
            /* ajustado */
        }

        /*========= TABLA DETALLES =========*/
        .tbl-detalles {
            width: 100%;
            table-layout: fixed;
            /* evita que se expanda más del ancho */
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            /* más pequeño para PDF */
            background-color: #ffffff;
        }

        .tbl-detalles th {
            background-color: #2980B9;
            color: #ffffff;
            padding: 4px;
            text-align: center;
            border-right: 1px solid #2980B9;
            border-bottom: 2px solid #D6E6F2;
            /* borde horizontal bajo el header */
            word-wrap: break-word;
            font-size: 10px;
        }

        .tbl-detalles td {
            padding: 4px;
            vertical-align: middle;
            text-align: center;
            border-right: 1px solid #D6E6F2;
            border-top: 1px solid #D6E6F2;
            /* borde horizontal superior */
            word-wrap: break-word;
            font-size: 10px;
        }

        .tbl-detalles tr:last-child td {
            border-bottom: 2px solid #D6E6F2;
            /* borde inferior de la última fila */
        }

        .tbl-detalles tr:nth-child(even) td {
            background-color: #EAF2FA;
        }


        /*========== TABLAS QR ==========*/
        .tbl-qr,
        .tbl-total {
            width: 100%;
        }

        /* Encabezado azul */
        .tbl-header-green {
            background-color: #2980B9;
            /* Azul fuerte */
            color: white;
            text-align: center;
            padding: 5px;
        }

        .cell-left {
            text-align: left;
            padding: 5px;
        }

        .cell-right {
            text-align: right;
            padding: 5px;
        }

        /* Azul claro para resaltar filas */
        .bg-aqua {
            background-color: #AED6F1;
            /* Azul pastel */
        }

        /* Azul muy suave para totales/saldos */
        .bg-yellow {
            background-color: #D6EAF8;
            /* Azul muy claro */
        }

        /*============ TBL VENTAS CIERRE ======*/
        .tbl-ventas-cierre {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            /* más pequeño */
            color: #333;
        }

        /* Estilos para las tablas internas */
        .tbl-ventas-cierre table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        /* Encabezado de tablas con fondo azul opaco */
        .tbl-ventas-cierre .tbl-header-blue th {
            background-color: #2980B9;
            /* azul opaco */
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
            /* menos padding */
            border: 1px solid #1f618d;
            /* borde azul más oscuro */
            font-size: 12px;
            /* letra más pequeña */
        }

        /* Celdas izquierda y derecha de la tabla de totales */
        .tbl-ventas-cierre .cell-left {
            text-align: left;
            padding: 3px 6px;
            border: 1px solid #ccc;
            font-size: 12px;
        }

        .tbl-ventas-cierre .cell-right {
            text-align: right;
            padding: 3px 6px;
            border: 1px solid #ccc;
            font-size: 12px;
        }

        /* Filas resaltadas en azul claro */
        .tbl-ventas-cierre .bg-blue-light {
            background-color: #d6eaf8;
            /* azul suave */
            font-weight: bold;
            font-size: 12px;
        }

        /* Línea separadora horizontal */
        .tbl-ventas-cierre hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 4px 0;
        }

        /* Títulos de secciones */
        .tbl-ventas-cierre .title-section {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
            margin: 6px 0 4px;
            color: #2980B9;
            /* azul opaco */
        }

        /* Tabla de detalles de trabajadores */
        .tbl-ventas-cierre .tbl-detalles th {
            background-color: #2980B9;
            /* azul opaco */
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
            border: 1px solid #1f618d;
            font-size: 12px;
        }

        .tbl-ventas-cierre .tbl-detalles td {
            text-align: center;
            padding: 4px 6px;
            border: 1px solid #2980B9;
            font-size: 12px;
        }

        /* Resaltado si no hay usuarios */
        .tbl-ventas-cierre .tbl-detalles td.text-center {
            font-style: italic;
            color: #555;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <table class="cabecera" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:4px; padding-bottom:2px;">
        <tr>
            <!-- Logo -->
            <td style="width: 120px;">
                @if ($empresa->ruta_logo)
                    <img src="{{ base_path() . '/storage/app/' . $empresa->ruta_logo }}"
                        style="max-height:70px; width:auto;">
                @else
                    <img src="{{ public_path() . '/img/default.png' }}" style="max-height:70px; width:auto;">
                @endif
            </td>

            <!-- Información empresa -->
            <td style="vertical-align: top; padding-left:10px;">
                <p class="nombre-empresa">
                    {{ DB::table('empresas')->count() == 0 ? 'SISCOM ' : DB::table('empresas')->first()->razon_social }}
                </p>
                <p class="direccion-empresa">
                    {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->direccion_fiscal }}
                </p>
                <p class="info-empresa">Central telefónica:
                    {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->telefono }}
                </p>
                <p class="info-empresa">Email:
                    {{ DB::table('empresas')->count() == 0 ? '-' : DB::table('empresas')->first()->correo }}
                </p>
            </td>

            <!-- Espacio vacío a la derecha -->
            <td style="width: 50px;"></td>
        </tr>
    </table>
    <div class="informacion">
        <table class="tbl-informacion">
            <tbody>
                <tr>
                    <td>CAJA</td>
                    <td>:</td>
                    <td>{{ $movimiento->caja->nombre }}</td>
                </tr>
                <tr>
                    <td>Colaborador</td>
                    <td>:</td>
                    <td>{{ $colaborador->nombre }}</td>
                </tr>
                <tr>
                    <td>Turno</td>
                    <td>:</td>
                    <td>{{ 'MAÑANA' }}</td>
                </tr>
                <tr>
                    <td>Monto Inicial</td>
                    <td>:</td>
                    <td>{{ $movimiento->monto_inicial }}</td>
                </tr>
                <tr>
                    <td>Fecha</td>
                    <td>:</td>
                    <td>{{ date_format($movimiento->created_at, 'Y/m/d') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <h3 style="text-align: center; margin-bottom: 10px;">PRODUCTOS EN VENTAS CONTADO</h3>

    <table width="100%" border="1" cellspacing="0" cellpadding="5" style="margin-top:20px;">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th>CATEGORÍA</th>
                <th>PRODUCTO</th>
                <th>COLOR</th>
                @foreach ($tallas_bd as $tallaBD)
                    <th style="text-align: center;">{{ $tallaBD->descripcion }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($productos_contado->groupBy(['producto_id', 'color_id']) as $producto)
                @foreach ($producto as $colores)
                    <tr>
                        <td>{{ $colores->first()->categoria_nombre }}</td>
                        <td>{{ $colores->first()->nombre_producto }}</td>
                        <td>{{ $colores->first()->nombre_color }}</td>

                        @foreach ($tallas_bd as $tallaBD)
                            @php
                                $registro = $colores->firstWhere('talla_id', $tallaBD->id);
                                $cantidad = $registro ? intval($registro->cantidad) : '-';
                            @endphp
                            <td style="text-align: center;">{{ $cantidad }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>


    <br>
    <h3 style="text-align: center; margin-bottom: 10px;">PRODUCTOS EN RESERVAS PAGADAS</h3>

    <table width="100%" border="1" cellspacing="0" cellpadding="5" style="margin-top:20px;">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th>CATEGORÍA</th>
                <th>PRODUCTO</th>
                <th>COLOR</th>
                @foreach ($tallas_bd as $tallaBD)
                    <th style="text-align: center;">{{ $tallaBD->descripcion }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($productos_reservas->groupBy(['producto_id', 'color_id']) as $producto)
                @foreach ($producto as $colores)
                    <tr>
                        <td>{{ $colores->first()->categoria_nombre }}</td>
                        <td>{{ $colores->first()->producto_nombre }}</td>
                        <td>{{ $colores->first()->color_nombre }}</td>

                        @foreach ($tallas_bd as $tallaBD)
                            @php
                                $registro = $colores->firstWhere('talla_id', $tallaBD->id);
                                $cantidad = $registro ? intval($registro->cantidad) : '-';
                            @endphp
                            <td style="text-align: center;">{{ $cantidad }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
