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
    <span style="text-transform: uppercase;font-size:11px">VENTAS CONTADO</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center border-right">#</th>
                    <th class="text-center border-right">CLIENTE</th>
                    <th class="text-center border-right">DEV</th>
                    <th class="text-center border-right">ORIGEN</th>
                    {{-- <th class="text-center border-right">COBRAR</th> --}}
                    <th class="text-center border-right">MONTO</th>
                    @php
                        $cont = 0;
                        while ($cont < count(tipos_pago())) {
                            if ($cont == count(tipos_pago()) - 1) {
                                echo '<th class="text-center">' . tipos_pago()[$cont]->descripcion . '</th>';
                            } else {
                                echo '<th class="text-center border-right">' .
                                    tipos_pago()[$cont]->descripcion .
                                    '</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleMovimientoVentas as $ventas)
                    @if (
                        $ventas->documento->condicion_id == 1 &&
                            $ventas->documento->estado_pago == 'PAGADA' &&
                            ifNoConvertido($ventas->documento->id))
                        <tr>
                            <td class="text-center border-right">
                                {{ $ventas->documento->serie . '-' . $ventas->documento->correlativo }}
                            </td>
                            <td class="text-center border-right">
                                {{ $ventas->documento->clienteEntidad->nombre }}
                            </td>
                            <td class="text-center border-right">
                                @if (count($ventas->documento->notas) > 0)
                                    <div class="cont-check">
                                        <span class="checkmark">
                                            <div class="checkmark_stem"></div>
                                            <div class="checkmark_kick"></div>
                                        </span>
                                    </div>
                                @elseif($ventas->documento->estado === 'ANULADO')
                                    ANULADO
                                @endif
                            </td>
                            <td class="text-center border-right">
                                {{ $ventas->documento->origen_venta_nombre }}
                            </td>
                            {{-- <td class="text-center border-right">
                                {{ $ventas->cobrar }}
                            </td> --}}
                            <td class="text-center border-right">
                                {{ $ventas->documento->total_pagar }}
                            </td>
                            @foreach (tipos_pago() as $tipo)
                                @php
                                    $isPago = $tipo->id == $ventas->documento->tipo_pago_id;
                                    $valor = $isPago
                                        ? $ventas->documento->importe
                                        : ($tipo->id == 1
                                            ? $ventas->documento->efectivo
                                            : 0.0);
                                @endphp
                                <td class="text-center border-right">{{ $valor }}</td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="5" class="text-center border-right border-top">TOTAL</td>
                    @foreach (tipos_pago() as $tipo_pago)
                        <td class="text-center border-right border-top">
                            {{ number_format(cuadreMovimientoCajaIngresosVentaResum($movimiento, $tipo_pago->id), 2) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    {{-- <span style="text-transform: uppercase;font-size:11px">VENTAS CRÉDITO</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center border-right">#</th>
                    <th class="text-center border-right">CLIENTE</th>
                    <th class="text-center border-right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ventas_credito as $venta)
                    <tr>
                        <td class="text-center border-right">{{ $venta->serie . '-' . $venta->correlativo }}</td>
                        <td class="text-center border-right">{{ $venta->cliente }}</td>
                        <td class="text-center border-right">{{ $venta->total_pagar }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="2" class="text-right border-right">TOTAL</td>
                    <td class="text-center border-right">{{ number_format($total_ventas_credito, 2) }}</td>
                </tr>
            </tfoot>
        </table>

    </div> --}}
    {{-- <br> --}}
    <span style="text-transform: uppercase;font-size:11px">COBRANZA CLIENTES</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center border-right">NUMERO</th>
                    <th class="text-center border-right">CLIENTE</th>
                    <th class="text-center border-right">MONTO</th>
                    @php
                        $cont = 0;
                        while ($cont < count(tipos_pago())) {
                            if ($cont == count(tipos_pago()) - 1) {
                                echo '<th class="text-center">' . tipos_pago()[$cont]->descripcion . '</th>';
                            } else {
                                echo '<th class="text-center border-right">' .
                                    tipos_pago()[$cont]->descripcion .
                                    '</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleCuentaCliente as $cuentaCliente)
                    <tr>
                        <td class="text-center border-right">
                            {{ $cuentaCliente->cuenta_cliente->documento->serie . '-' . $cuentaCliente->cuenta_cliente->documento->correlativo }}
                        </td>
                        <td class="text-center border-right">
                            {{ $cuentaCliente->cuenta_cliente->documento->clienteEntidad->nombre }}
                        </td>
                        <td class="text-center border-right">
                            {{ $cuentaCliente->monto }}
                        </td>
                        @foreach (tipos_pago() as $tipo)
                            @if ($tipo->id == 1)
                                <td class="text-center border-right">
                                    {{ $cuentaCliente->efectivo }}
                                </td>
                            @else
                                <td class="text-center border-right">
                                    {{ $tipo->id == $cuentaCliente->tipo_pago_id ? $cuentaCliente->importe : '0.00' }}
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                <tr class="tfoot-total">
                    <td colspan="3" class="text-center border-right border-top">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                        <td class="text-center border-right border-top">
                            {{ number_format(cuadreMovimientoCajaIngresosCobranzaResum($movimiento, $tipo->id), 2) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <span style="text-transform: uppercase;font-size:11px">EGRESOS POR CAJA</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center border-right">ID RECIBO</th>
                    <th class="text-center border-right">DESCRIPCION</th>
                    <th class="text-center border-right">MONTO</th>
                    @php
                        $cont = 0;
                        while ($cont < count(tipos_pago())) {
                            if ($cont == count(tipos_pago()) - 1) {
                                echo '<th class="text-center">' . tipos_pago()[$cont]->descripcion . '</th>';
                            } else {
                                echo '<th class="text-center border-right">' .
                                    tipos_pago()[$cont]->descripcion .
                                    '</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleMoviemientoEgresos as $detalleEgreso)
                    @if ($detalleEgreso->egreso->estado == 'ACTIVO')
                        <tr>
                            <td class="text-center border-right">{{ $detalleEgreso->egreso->documento }}</td>
                            <td class="text-center border-right">{{ $detalleEgreso->egreso->descripcion }}</td>
                            <td class="text-center border-right">{{ $detalleEgreso->egreso->monto }}</td>
                            @foreach (tipos_pago() as $tipo)
                                @if ($tipo->id == 1)
                                    <td class="text-center border-right">
                                        {{ $detalleEgreso->egreso->efectivo }}
                                    </td>
                                @else
                                    <td class="text-center border-right">
                                        {{ $tipo->id == $detalleEgreso->egreso->tipo_pago_id ? $detalleEgreso->egreso->importe : '0.00' }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @endforeach
                <tr class="tfoot-total">
                    <td colspan="3" class="text-center border-right border-top">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                        <td class="text-center border-right border-top">
                            {{ number_format(cuadreMovimientoCajaEgresosEgresoResum($movimiento, $tipo->id), 2) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    {{-- <span style="text-transform: uppercase;font-size:11px">RECIBOS DE CAJA</span>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center border-right">ID RECIBO</th>
                    <th class="text-center border-right">DESCRIPCION</th>
                    <th class="text-center border-right">MONTO</th>
                    @php
                        $cont = 0;
                        while ($cont < count(tipos_pago())) {
                            if ($cont == count(tipos_pago()) - 1) {
                                echo '<th class="text-center">' . tipos_pago()[$cont]->descripcion . '</th>';
                            } else {
                                echo '<th class="text-center border-right">' .
                                    tipos_pago()[$cont]->descripcion .
                                    '</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($recibos as $recibo)
                    @if ($recibo->estado == 'ACTIVO')
                        <tr>
                            <td class="text-center border-right">{{ 'RC-' . $recibo->id }}</td>
                            <td class="text-center border-right">
                                {{ $recibo->cliente_nombre . '-' . $recibo->estado_servicio }}</td>
                            <td class="text-center border-right">{{ $recibo->monto }}</td>
                            @foreach (tipos_pago() as $tipo)
                                <td class="text-center border-right">
                                    {{ $tipo->descripcion == $recibo->metodo_pago ? $recibo->monto : '0.00' }}
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
                <tr class="tfoot-total">
                    <td colspan="3" class="text-center border-right border-top">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                        <td class="text-center border-right border-top">
                            {{ number_format(calcularTotalesRecibosCaja($movimiento, $tipo->descripcion), 2) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <br> --}}
    {{-- <span style="text-transform: uppercase;font-size:11px">PAGOS PROVEEDORES</span>
    <br>
    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th class="text-center border-right">TIPO DOC</th>
                    <th class="text-center border-right">NUMERO</th>
                    <th class="text-center border-right">CLIENTE</th>
                    <th class="text-center border-right">MONTO</th>
                    @php
                        $cont = 0;
                        while ($cont < count(tipos_pago())) {
                            if ($cont == count(tipos_pago()) - 1) {
                                echo '<th class="text-center">' . tipos_pago()[$cont]->descripcion . '</th>';
                            } else {
                                echo '<th class="text-center border-right">' .
                                    tipos_pago()[$cont]->descripcion .
                                    '</th>';
                            }
                            $cont++;
                        }
                    @endphp
                </tr>
            </thead>
            <tbody>
                @foreach ($movimiento->detalleCuentaProveedor as $detalleProveedor)
                    <tr>
                        <td class="text-center border-right">
                            {{ $detalleProveedor->cuenta_proveedor->documento->tipo_compra }}
                        </td>
                        <td class="text-center border-right">
                            {{ $detalleProveedor->cuenta_proveedor->documento->serie_tipo . ' - ' . $detalleProveedor->cuenta_proveedor->documento->numero_tipo }}
                        </td>
                        <td class="text-center border-right">
                            {{ $detalleProveedor->cuenta_proveedor->documento->proveedor->descripcion }}
                        </td>
                        <td class="text-center border-right">
                            {{ $detalleProveedor->efectivo + $detalleProveedor->importe }}
                        </td>
                        @foreach (tipos_pago() as $tipo)
                            <td class="text-center border-right">
                                @if ($tipo->id == 1)
                                    {{ $tipo->id == $detalleProveedor->tipo_pago_id ? $detalleProveedor->efectivo : $detalleProveedor->efectivo }}
                                @else
                                    {{ $tipo->id == $detalleProveedor->tipo_pago_id ? $detalleProveedor->importe : '0.00' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                <tr class="tfoot-total">
                    <td colspan="4" class="text-center border-right border-top">TOTAL</td>
                    @foreach (tipos_pago() as $tipo)
                        <td class="text-center border-right border-top">
                            {{ number_format(cuadreMovimientoCajaEgresosPagoResum($movimiento, $tipo->id), 2) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <br> --}}
    <div class="info-total-qr">
        {{-- <table class="tbl-qr" cellpadding="2" cellspacing="0">
            <tr>
                <td>
                    <table class="tbl-total text-uppercase" cellpadding="2" cellspacing="0">
                        <thead class="tbl-header-green">
                            <tr>
                                <th colspan="2">DETALLES EFECTIVO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="cell-left">VENTAS</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoCajaIngresosVentaResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-left">DEVOLUCIONES</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoDevolucionesResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            <tr class="bg-aqua">
                                <td class="cell-left">VENTAS EFECTIVA</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoCajaIngresosVentaResum($movimiento, 1) - cuadreMovimientoDevolucionesResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            <tr>
                                <td class="cell-left">COBRANZA</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoCajaIngresosCobranzaResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-left">PAGOS</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoCajaEgresosPagoResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-left">EGRESOS</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoCajaEgresosEgresoResum($movimiento, 1) - cuadreMovimientoDevolucionesResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            <tr class="bg-yellow">
                                <td class="cell-left">EFECTIVO</td>
                                <td class="cell-right">
                                    {{ number_format(cuadreMovimientoCajaIngresosVentaResum($movimiento, 1) + cuadreMovimientoCajaIngresosCobranzaResum($movimiento, 1) - cuadreMovimientoCajaEgresosEgresoResum($movimiento, 1) - cuadreMovimientoCajaEgresosPagoResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                            <tr class="bg-yellow">
                                <td class="cell-left">SALDO ANTERIOR</td>
                                <td class="cell-right">
                                    {{ number_format($movimiento->monto_inicial, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            <tr class="bg-yellow">
                                <td class="cell-left">SALDO CAJA DEL DÍA</td>
                                <td class="cell-right">
                                    {{ number_format($movimiento->monto_inicial + cuadreMovimientoCajaIngresosVentaResum($movimiento, 1) + cuadreMovimientoCajaIngresosCobranzaResum($movimiento, 1) - cuadreMovimientoCajaEgresosEgresoResum($movimiento, 1) - cuadreMovimientoCajaEgresosPagoResum($movimiento, 1), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table> --}}
        <br>

        <table class="tbl-ventas-cierre" cellpadding="2" cellspacing="0">
            <tr>
                <td>
                    <table class="tbl-total text-uppercase" cellpadding="2" cellspacing="0">
                        <thead class="tbl-header-blue">
                            <tr>
                                <th colspan="2">VENTAS + COBRANZAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (tipos_pago() as $tipo)
                                @if ($tipo->id > 1)
                                    <tr>
                                        <td class="cell-left">
                                            <p class="m-0 p-0">{{ $tipo->descripcion }}</p>
                                        </td>
                                        <td class="cell-right">
                                            <p class="p-0 m-0">
                                                {{ number_format(
                                                    cuadreMovimientoCajaIngresosVentaResum($movimiento, $tipo->id) +
                                                        cuadreMovimientoCajaIngresosCobranzaResum($movimiento, $tipo->id),
                                                    2,
                                                    '.',
                                                    ',',
                                                ) }}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="cell-left">
                                            <p class="m-0 p-0">{{ $tipo->descripcion }} DEVOLUCIONES</p>
                                        </td>
                                        <td class="cell-right">
                                            <p class="p-0 m-0">
                                                {{ number_format(cuadreMovimientoDevolucionesResum($movimiento, $tipo->id), 2) }}
                                            </p>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            {{-- <tr class="bg-blue-light">
                                <td class="cell-left">
                                    <p class="p-0 m-0">TOTAL VENTA ELECTRONICO</p>
                                </td>
                                <td class="cell-right">
                                    <p class="p-0 m-0">
                                        {{ number_format(cuadreMovimientoCajaIngresosVentaElectronico($movimiento), 2) }}
                                    </p>
                                </td>
                            </tr>
 --}}
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>

                            <tr class="bg-blue-light">
                                <td class="cell-left">
                                    <p class="p-0 m-0">TOTAL VENTA DEL DIA</p>
                                </td>
                                <td class="cell-right">
                                    <p class="p-0 m-0">
                                        {{ number_format(
                                            cuadreMovimientoCajaIngresosVenta($movimiento) +
                                                //cuadreMovimientoCajaIngresosRecibo($movimiento)
                                                - cuadreMovimientoDevoluciones($movimiento)
                                                + cuadreMovimientoCajaIngresosCobranzaResum($movimiento, null),
                                            2,
                                            '.',
                                            ',',
                                        ) }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <br>
                    {{-- <span class="title-section">Trabajadores de ventas presentes</span>

                    <div class="cuerpo">
                        <table class="tbl-detalles text-uppercase" cellpadding="8" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="border-blue">Codigo</th>
                                    <th class="border-blue">Nombres</th>
                                    <th class="border-blue">Fecha Entrada</th>
                                    <th class="border-blue">Fecha Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($usuarios) == 0)
                                    <tr>
                                        <td colspan="4" class="text-center border-blue">Sin Usuarios Ventas</td>
                                    </tr>
                                @else
                                    @foreach ($usuarios as $u)
                                        <tr>
                                            <td class="text-center border-blue">{{ $u->id }}</td>
                                            <td class="text-center border-blue">{{ $u->usuario }}</td>
                                            <td class="text-center border-blue">{{ $u->fecha_entrada }}</td>
                                            <td class="text-center border-blue">{{ $u->fecha_salida }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div> --}}
                    {{-- <br> --}}
                </td>
            </tr>
        </table>

        <br>
    </div>
</body>

</html>
