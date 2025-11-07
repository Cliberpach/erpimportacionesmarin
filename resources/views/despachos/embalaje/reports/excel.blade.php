<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORTE EMBALAJE</title>
</head>

<body>
    <div>
        <table>
            <tr>
                <td style="width: 220px; font-weight: bold;">EMPRESA</td>
                <td style="font-size: 12px;">{{ $empresa->razon_social }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">RUC</td>
                <td style="font-size: 10px;">{{ $empresa->ruc }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">DIRECCIÓN</td>
                <td style="font-size: 10px;">{{ $empresa->direccion }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">TELÉFONO</td>
                <td style="font-size: 10px;">{{ $empresa->telefono }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">EMAIL</td>
                <td style="font-size: 10px;">{{ $empresa->correo }}</td>
            </tr>
        </table>

        <div class="header-title">
            REPORTE EMBALAJE
        </div>

        <!-- Información adicional -->
        <table class="info-table">
            <tr>
                <td style="width:160px;"><strong>USUARIO IMPRESIÓN:</strong></td>
                <td>{{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <td style="width:160px;"><strong>FECHA IMPRESIÓN:</strong></td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>FECHA INICIO:</strong></td>
                <td>{{ $filters->get('fecha_inicio') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>FECHA FIN:</strong></td>
                <td>{{ $filters->get('fecha_fin') }}</td>
            </tr>
            <tr>
                <td class="label">MODO:</td>
                <td>{{ $filters->get('filtroModo') }}</td>
            </tr>
            <tr>
                <td class="label">CLIENTE:</td>
                <td>{{ $filters->get('cliente') }}</td>
            </tr>
        </table>

        <!-- Tabla del reporte -->
        <table>
            <thead>
                <tr>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        CLIENTE</th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        DOC</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        PRODUCTO</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        COLOR</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        TALLA</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        CANT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reporte as $item)
                    <tr>
                        <td>{{ $item->cliente_nombre }}</td>
                        <td>{{ $item->serie . '-' . $item->correlativo }}</td>
                        <td>{{ $item->nombre_producto }}</td>
                        <td>{{ $item->nombre_color }}</td>
                        <td>{{ $item->nombre_talla }}</td>
                        <td style="text-align: right;">{{ number_format($item->cantidad, 2) }}</td>
                        {{-- <td>{{ $item->estado_despacho }}</td> --}}
                        {{-- <td>{{ $item->estado_item }}</td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ now()->year }} {{ $empresa->razon_social }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>

</html>
