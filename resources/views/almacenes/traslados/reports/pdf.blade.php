<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if ($filters->has('codigo'))
        <title>{{"TRASLADO ".$filters->get('codigo')}}</title>
    @else
        <title>REPORTE TRASLADOS</title>
    @endif
    <link rel="icon" href="{{ asset('img/gas.ico') }}" type="image/x-icon">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            height: 100%;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .details p,
        .totals p {
            margin: 5px 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        td {
            padding: 6px;
            vertical-align: top;
        }

        .header-table td {
            border: none;
        }

        .info-table-custom {
            margin-top: 20px;
            width: 100%;
        }

        .info-table-custom td {
            font-size: 11px;
            border: 1px solid #d4f1ff;
        }

        .info-table-custom .label {
            font-weight: bold;
            background-color: #f5f5f5;
            font-size: 11px;
        }



        .tbl-report-sale {
            margin-top: 20px;
            width: 100%;
            border: 1px solid #ccc;
        }

        .tbl-report-sale th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 12px;
        }

        .tbl-report-sale td {
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 12px;
        }


        /*======== FOOTER ==========*/
        @page {
            margin: 30px 50px 90px 50px;
        }

        #footer {
            position: fixed;
            left: 0px;
            bottom: -180px;
            right: 0px;
            height: 130px;
            background-color: #e1e1e1;
            color: #3a5068;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 400;
            font-size: 11px;
            line-height: 1.5;
            text-align: center;
            box-shadow: inset 0 1px 3px rgb(58 80 104 / 0.1);
            border-top: 1px solid #c7d6e2;
        }
    </style>
</head>

<body>

    <div id="footer">
        <p>&copy; {{ now()->year }} {{ $empresa->razon_social }} - Todos los derechos reservados</p>
    </div>

    <div class="container">

        <!-- Encabezado con logo e información de la empresa -->
        <table class="header-table">
            <tr>
                <!-- Columna 1: Imagen -->
                <td style="width: 20%; text-align: left;">
                    <img src="{{ $empresa->logo_ruta }}" alt="Logo"
                        style="height: 100px; object-fit: contain; max-width: 120px;">
                </td>

                <!-- Columna 2: Información de la empresa -->
                <td style="width: 80%; text-align: left;">
                    <h2 style="margin: 0; font-size: 11px; color: #3a6ea5;">{{ $empresa->razon_social }}</h2>
                    <p style="margin: 0; font-size: 11px; color: #555;">RUC: {{ $empresa->ruc }}</p>
                    <p style="margin: 0; font-size: 11px; color: #555;">{{ $empresa->direccion }}</p>
                    <p style="margin: 0; font-size: 11px; color: #555;">Teléfono: {{ $empresa->telefono }}</p>
                    <p style="margin: 0; font-size: 11px; color: #555;">EMAIL: {{ $empresa->correo }}</p>
                </td>


            </tr>
        </table>

        <div style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">
            REPORTE TRASLADOS
        </div>

        <!-- Segunda tabla: Información adicional -->
        <table class="info-table-custom">
            <tr>
                <td class="label" style="width:30%;">USUARIO IMPRESIÓN:</td>
                <td>{{ Auth::user()->usuario }}</td>
            </tr>
            <tr>
                <td class="label">FECHA IMPRESIÓN:</td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
            @if ($filters->has('codigo'))
                <tr>
                    <td class="label">N°:</td>
                    <td>{{ $filters->get('codigo') }}</td>
                </tr>
                 <tr>
                    <td class="label">ALMACÉN ORIGEN:</td>
                    <td>{{ $filters->get('almacen_origen') }}</td>
                </tr>
                 <tr>
                    <td class="label">ALMACÉN DESTINO:</td>
                    <td>{{ $filters->get('almacen_destino') }}</td>
                </tr>
                 <tr>
                    <td class="label">SEDE ORIGEN:</td>
                    <td>{{ $filters->get('sede_origen') }}</td>
                </tr>
                 <tr>
                    <td class="label">SEDE DESTINO:</td>
                    <td>{{ $filters->get('sede_destino') }}</td>
                </tr>
            @endif
        </table>

        <!-- Tercera tabla: Reporte Kardex -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th scope="col">N°</th>
                    <th scope="col">ALM ORIGEN</th>
                    <th scope="col">ALM DESTINO</th>
                    <th scope="col">PRODUCTO</th>
                    <th scope="col">COLOR</th>
                    <th scope="col">TALLA</th>
                    <th scope="col">CANT</th>
                    <th scope="col">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reporte as $item)
                    <tr>
                        <td>{{ $item->simbolo }}</td>
                        <td>{{ $item->almacen_origen_nombre }}</td>
                        <td>{{ $item->almacen_destino_nombre }}</td>
                        <td>{{ $item->producto_nombre }}</td>
                        <td>{{ $item->color_nombre }}</td>
                        <td>{{ $item->talla_nombre }}</td>
                        <td style="text-align: right;">{{ number_format($item->cantidad) }}</td>
                        <td>{{ $item->estado }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
