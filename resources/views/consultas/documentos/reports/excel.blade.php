<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Ventas</title>
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
            REPORTE VENTAS
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
                <td>{{ $filtros->get('fecha_inicio') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>FECHA FIN:</strong></td>
                <td>{{ $filtros->get('fecha_fin') }}</td>
            </tr>
        </table>

        <!-- Tabla del reporte -->
        <table>
            <thead>
                <tr>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        DOC</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        CLIENTE</th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        RESERVA</th>
                    <th width="10"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        MONTO</th>
                    <th width="10"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        SALDO</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        REGISTRADOR</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        FECHA REGISTRO</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        ESTADO DESPACHO</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        EMBALADO POR</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        REPARTIDO POR</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        FECHA EMBALAJE</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        FECHA REPARTO</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($resultado as $item)
                    <tr>
                        <td>{{ $item->documento }}</td>
                        <td>{{ $item->cliente }}</td>
                        <td>{{ $item->pedido }}</td>
                        <td>{{ number_format($item->total_pagar, 2, '.', ',') }}</td>
                        <td>{{ number_format($item->saldo, 2, '.', ',') }}</td>
                        <td>{{ $item->registrador_nombre }}</td>
                        <td>{{ $item->fecha_registro }}</td>
                        <td>{{ $item->estado_despacho }}</td>
                        <td>{{ $item->usuario_embalaje }}</td>
                        <td>{{ $item->usuario_reparto }}</td>
                        <td>{{ $item->fecha_embalaje }}</td>
                        <td>{{ $item->fecha_reparto }}</td>

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
