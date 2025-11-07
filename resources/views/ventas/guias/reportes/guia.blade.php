<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$guia->serie.'-'.$guia->correlativo}}</title>
    <link rel="icon" href="{{ public_path() . '/img/siscom.ico' }}" />
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: black;
        }

        .cabecera {
            width: 100%;
            position: relative;
            height: 120px;
            max-height: 200px;
        }

        .logo {
            width: 30%;
            position: absolute;
            left: 0%;
        }

        .logo .logo-img {
            position: relative;
            width: 95%;
            margin-right: 5%;
            height: 90px;
        }

        .img-fluid {
            width: 100%;
            height: 100%;
        }

        .empresa {
            width: 40%;
            position: absolute;
            left: 30%;
        }

        .empresa .empresa-info {
            position: relative;
            width: 100%;
        }

        .nombre-empresa {
            font-size: 16px;
        }

        .ruc-empresa {
            font-size: 15px;
        }

        .direccion-empresa {
            font-size: 12px;
        }

        .text-info-empresa {
            font-size: 12px;
        }

        .comprobante {
            width: 30%;
            position: absolute;
            left: 70%;
        }

        .comprobante .comprobante-info {
            position: relative;
            width: 100%;
            display: flex;
            align-content: center;
            align-items: center;
            text-align: center;
        }

        .numero-documento {
            margin: 1px;
            padding-top: 20px;
            padding-bottom: 20px;
            border: 2px solid #005295;
            font-size: 14px;
        }

        .nombre-documento {
            margin-top: 5px;
            margin-bottom: 5px;
            margin-left: 0px;
            margin-right: 0px;
            width: 100%;
            background-color: rgb(122, 186, 255);
        }

        .destinatario {
            width: 100%;
            position: relative;
        }

        .tbl {
            width: 100%;
            font-size: 12px;
        }

        .tbl thead tr th {
            border: 0.03cm solid #5f5f5f;
            text-align: left;
        }

        .tbl td {
            border: 0.03cm solid #5f5f5f;
            text-align: left;
        }

        .envio {
            width: 100%;
            position: relative;
        }

        .transporte {
            width: 100%;
            position: relative;
        }

        .detalles {
            width: 100%;
            position: relative;
        }

        .tbl-detalles {
            width: 100%;
            font-size: 12px;
        }

        .tbl-detalles thead {
            border: 0.03cm solid #5f5f5f;
            text-align: left;
        }

        .tbl-detalles tbody {
            border: 0.03cm solid #5f5f5f;
            text-align: left;
        }

        /*---------------------------------------------*/

        .m-0 {
            margin: 0;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .p-0 {
            padding: 0;
        }

        .text-danger {
            color: #c90404;
        }
    </style>
</head>

<body>
    <div class="cabecera">
        <div class="logo">
            <div class="logo-img">
                <img src="{{ base_path() . '/storage/app/' . $empresa->ruta_logo }}" class="img-fluid">
            </div>
        </div>
        <div class="empresa">
            <div class="empresa-info">
                <p class="m-0 p-0 text-uppercase nombre-empresa">
                    {{ DB::table('empresas')->count() == 0 ? 'SISCOM ' : DB::table('empresas')->first()->razon_social }}
                </p>
                <p class="m-0 p-0 text-uppercase ruc-empresa">RUC
                    {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->ruc }}</p>
                <p class="m-0 p-0 text-uppercase ruc-empresa"> {{ $empresa->direccion_fiscal }}</p>
                <p class="m-0 p-0 text-uppercase direccion-empresa">{{ $sede->nombre }}</p>
                <p class="m-0 p-0 text-uppercase direccion-empresa">{{ $sede->direccion }}</p>
                <p class="m-0 p-0 text-info-empresa">Teléfono: {{ $sede->telefono }}</p>
            </div>
        </div>
        <div class="comprobante">
            <div class="comprobante-info">
                <div class="numero-documento">
                    <p class="m-0 p-0 text-uppercase ruc-empresa">RUC
                        {{ DB::table('empresas')->count() == 0 ? '- ' : DB::table('empresas')->first()->ruc }}</p>
                    <div class="nombre-documento">
                        <p class="m-0 p-0 text-uppercase">GUÍA DE REMISIÓN REMITENTE <small>ELECTRÓNICA</small></p>
                    </div>
                    <p class="m-0 p-0 text-uppercase {{ $guia->serie ? '' : 'text-danger' }}">
                        {{ $guia->serie ? $guia->serie . '-' . $guia->correlativo : 'No enviado a sunat' }}</p>
                </div>
            </div>
        </div>
    </div><br>

    <div style="text-align: left; margin: 15px 0;">
        <table cellpadding="3" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
            <thead>
                <tr>
                    <th style="background-color: #f4f4f4; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 2px solid #ddd; border-left: 1px solid #ddd; border-right: 1px solid #ddd;"
                        colspan="2">DATOS ENVÍO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">Motivo Traslado:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $guia->motivo_traslado_nombre }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">Modalidad Traslado:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $guia->modalidad_traslado_nombre }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">Fecha Traslado:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $guia->fecha_traslado }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">N° Bultos:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $guia->cantidad_productos }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">Punto partida:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $partida->nombre }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $partida->direccion }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $partida->distrito_id }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">
                            {{ $partida->departamento_nombre . '-' . $partida->provincia_nombre . '-' . $partida->distrito_nombre }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <div style="text-align: left; margin: 15px 0;">
        <table cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
            <thead>
                <tr>
                    <th style="background-color: #f4f4f4; padding: 6px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 2px solid #ddd; border-left: 1px solid #ddd; border-right: 1px solid #ddd;"
                        colspan="2">DESTINATARIO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">NOMBRE:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $destinatario->nombre }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">DIRECCIÓN:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $destinatario->direccion }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;">UBIGEO:</div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">{{ $destinatario->distrito_id }}</div>
                    </td>
                </tr>
                <tr>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 11px; color: #333;"></div>
                    </td>
                    <td
                        style="padding: 4px 12px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 11px; color: #555;">
                            {{ $destinatario->departamento_nombre . '-' . $destinatario->provincia_nombre . '-' . $destinatario->distrito_nombre }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="text-align: left;">
        <table cellpadding="3" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
            <thead>
                <tr>
                    <th style="background-color: #f4f4f4; padding: 5px; text-align: left; font-size: 10px; font-weight: bold; border-bottom: 1px solid #ddd; border-left: 1px solid #ddd; border-right: 1px solid #ddd;"
                        colspan="2">TRANSPORTE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td
                        style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-weight: bold; font-size: 10px; color: #333;">Vehículos categoría M1 o L:</div>
                    </td>
                    <td
                        style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                        <div style="font-size: 10px; color: #555;">{{ $guia->categoria_M1L ? 'SI' : 'NO' }}</div>
                    </td>
                </tr>

                @if ($guia->vehiculo_id)
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">PLACA DEL VEHÍCULO:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">{{ $guia->vehiculo_placa }}</div>
                        </td>
                    </tr>
                @endif

                @if ($guia->conductor_id)
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">CONDUCTOR:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">
                                {{ $guia->conductor_nombres.' '.$guia->conductor_apellidos }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">
                                {{ $guia->conductor_tipo_doc_codigo == '1' ? 'DNI':'RUC' }}:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">{{ $guia->conductor_nro_doc }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">LICENCIA:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">{{ $guia->conductor_licencia }}</div>
                        </td>
                    </tr>
                @endif

                @if ($guia->transportista_id)
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">TRANSPORTISTA:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">{{ $guia->transportista_rzn_social }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">
                                {{ $guia->transportista_tipo_doc_codigo == '1' ? 'DNI':'RUC' }}:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">{{ $guia->transportista_num_doc }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-weight: bold; font-size: 10px; color: #333;">REGISTRO MTC:</div>
                        </td>
                        <td
                            style="padding: 3px 10px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; vertical-align: top;">
                            <div style="font-size: 10px; color: #555;">{{ $guia->transportista_nro_mtc }}</div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <br>
    <div class="detalles">
        <table class="tbl-detalles" cellpadding="2" cellspacing="0">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>CÓDIGO</th>
                    <th>DESCRIPCION</th>
                    <th>UNI.</th>
                    <th>CANT.</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < count($guia->detalles); $i++)
                    <tr>
                        <td style="text-align: center">
                            <p class="m-0 p-0">{{ $i + 1 }}</p>
                        </td>
                        <td style="text-align: center">{{ $guia->detalles[$i]->codigo_producto }}</td>
                        <td>{{ $guia->detalles[$i]->nombre_producto . '-' . $guia->detalles[$i]->nombre_color . '-' . $guia->detalles[$i]->nombre_talla }}
                        </td>
                        <td style="text-align: center">{{ $guia->detalles[$i]->unidad }}</td>
                        <td style="text-align: center">{{ $guia->detalles[$i]->cantidad }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <br>
    <div class="comprobante-aux" style="border: 1px solid #ddd; border-radius: 4px;">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th style="font-size: 0.7rem; color:#555; text-align: left; padding: 2px;">
                        COMPROBANTES AFECTADOS
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-size: 0.65rem; color:#333; text-align: left; padding: 2px;">
                        @forelse ($comprobantes_afectados as $index => $comprobante)
                            {{ $comprobante->serie . '-' . $comprobante->correlativo }}
                            @if ($index < count($comprobantes_afectados) - 1)
                                <span class="text-muted"> | </span>
                            @endif
                        @empty
                            <span class="text-muted">Sin comprobantes vinculados</span>
                        @endforelse
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
    <div class="qr" style="text-align: center;">
        @if ($guia->ruta_qr)
            <img style="height: 140px; object-fit: contain;" src="{{ public_path($guia->ruta_qr) }}">
        @endif
        <div style="margin-top: 6px;">
            @if ($guia->url_pdf)
                <a href="{{ $guia->url_pdf }}"
                    style="font-size: 8px; color: black; text-decoration: underline; font-weight: bold;">
                    {{ $guia->url_pdf }}
                </a>
            @endif
        </div>
    </div>
</body>

</html>
