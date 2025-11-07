<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $despacho->distrito . '-' . $despacho->cliente_nombre . '-' . $despacho->created_at }}</title>
    <link rel="stylesheet" href="styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cedarville+Cursive&family=Vast+Shadow&display=swap"
        rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: -30px 0 0 auto;
            text-align: center;
        }

        .container {
            page-break-after: always;
        }

        .container:last-child {
            page-break-after: auto;
        }

        .row {
            display: table;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .col {
            display: table-cell;
            vertical-align: middle;
            padding: 0 5px;
        }

        .col-left,
        .col-right {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
        }

        .col-left {
            text-align: left;
        }

        .col-right {
            text-align: right;
        }

        .col-logo {
            width: 20%;
            text-align: left;
        }

        .col-info {
            width: 60%;
            text-align: center;
        }

        .col-qr {
            width: 20%;
            text-align: right;
        }

        /*======== FILA ENCABEZADO =========*/
        .logo {
            max-width: 160px;
            height: 140px;
            object-fit: contain;
        }

        .company-name {
            font-family: "Roboto", sans-serif;
            font-weight: 700;
            font-style: normal;
            color: rgb(186, 7, 7);
            font-size: 22px;
            padding: 0;
            margin: 0;
            margin-top: 8px;
        }


        .phones {
            margin-top: 5px;
        }

        .phone-item {
            display: inline-block;
            margin: 0 5px;
            font-size: 14px;
        }

        .phone-item img {
            width: 14px;
            vertical-align: middle;
            margin-right: 3px;
        }

        .qr {
            width: 120px;
            height: 120px;
        }

        .barcode {
            display: block;
            margin: 5px auto 0 auto;
            max-width: 120px;
        }

        /*======= FIN FILA ENCABEZADO =======*/


        .destino-text {
            font-size: 55px;
            font-family: "Vast Shadow", serif;
            padding: 0;
            margin: 0;
            letter-spacing: -1px;
            line-height: 0.6;
        }


        .cuadro-content {
            width: 100%;
            vertical-align: middle;
            display: inline-block;
        }

        .nombre_destinatario {
            font-size: 40px;
            margin-top: 15px;
            margin-bottom: -20px;
        }

        .doc_destinatario {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .redes-text {
            font-size: 25px;
            font-family: "Cedarville Cursive", cursive;
            margin: 0;
            padding: 0;
        }

        .empresa-envio {
            font-size: 29px;
            color: red;
        }

        .sede-envio {
            font-size: 29px;
        }

        .observaciones {
            font-size: 26px;
        }
    </style>
</head>

<body>

    @for ($i = 0; $i < $nro_bultos; $i++)
        <div class="container">

            <div class="row" style="margin-bottom: 10px;">
                <!-- Columna izquierda: Logo -->
                <div class="col col-logo">
                    @if ($empresa->ruta_logo)
                        <img src="{{ base_path() . '/storage/app/' . $empresa->ruta_logo }}" class="logo">
                    @else
                        <img src="{{ public_path() . '/img/default.png' }}" class="logo">
                    @endif
                </div>

                <!-- Columna central: Razon social + celulares -->
                <div class="col col-info">
                    <p class="company-name">{{ $empresa->razon_social_abreviada }}</p>
                    <div class="phones">
                        <div class="phone-item">
                            <img src="{{ public_path() . '/img/whatsapp_blanco_negro.jpg' }}">
                            <span>{{ $empresa->celular }}</span>
                        </div>
                        <div class="phone-item">
                            <img src="{{ public_path() . '/img/whatsapp_blanco_negro.jpg' }}">
                            <span>{{ $empresa->celular_2 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: QR + Barcode debajo -->
                <div class="col col-qr">
                    <img src="{{ $despacho->qr_ruta }}" class="qr" />
                    <br>
                    <img src="{{ $despacho->barcode_ruta }}" class="barcode" />
                </div>

            </div>

            <div class="row">
                <div class="cuadro-content" style="border:1px black solid;">
                    <p class="nombre_destinatario">{{ $despacho->destinatario_nombre }}</p>
                    <p class="doc_destinatario">
                        {{ $despacho->destinatario_tipo_doc . ': ' . $despacho->destinatario_nro_doc }}
                        {{ $despacho->cliente_celular ? ' - ' . 'CEL: ' . $despacho->cliente_celular : '' }}</p>
                </div>
            </div>


            <div class="row" style="margin-bottom: 10px;">
                <p class="destino-text" style="vertical-align: middle;">{{ $despacho->distrito }}</p>
            </div>

            <div style="display: table; width: 100%; border-collapse: collapse;">
                <div style="display: table-cell; width: 70%; vertical-align: top; padding: 2px;text-align: left;">
                    @if ($obs_rotulo)
                        <div class="row" style="margin-bottom:0px;">
                            <p style="margin:0;padding:0;font-weight: bold;" class="observaciones">
                                <span>OBSERVACIÓN: </span>
                                {{ $obs_rotulo }}
                            </p>
                        </div>
                    @endif
                    @if ($despacho->entrega_domicilio === 'SI')
                        <div class="row" style="margin-bottom:0;">
                            <p class="empresa-envio" style="margin:0;padding:0;font-size:22px;">
                                <span style="font-weight: bold;">ENVÍO DOMIC: </span>
                                {{ $despacho->direccion_entrega }}
                            </p>
                        </div>
                    @endif
                    <div class="row">
                        <p style="margin:0;padding:0;" class="empresa-envio">
                            {{ $despacho->empresa_envio_nombre . ' - ' . $despacho->tipo_pago_envio }}</p>
                    </div>
                </div>
                <div style="display: table-cell; width: 30%; vertical-align: top; padding: 2px; text-align: right;">
                    <p style="margin: 0; padding: 0;">
                        @foreach ($detalle_paquete as $item)
                            <p style="font-size: 10px;margin:0;padding:0;">
                                {{ $item->producto_nombre .'-'.$item->color_nombre.'-'.$item->talla_nombre.'-'.$item->cantidad }}
                            </p>
                        @endforeach
                    </p>
                </div>
            </div>






            {{-- <div class="row">
                <p style="margin:0;padding:0;" class="sede-envio">{{ $despacho->sede_envio_nombre }}</p>
            </div> --}}

        </div>
    @endfor
</body>

</html>
