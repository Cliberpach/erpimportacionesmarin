<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORDEN PRODUCCION {{ $datos->orden_produccion->id }}</title>
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
            ORDEN PRODUCCION {{ $datos->orden_produccion->id }}
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
                <td style="width:160px;"><strong>FECHA PROPUESTA:</strong></td>
                <td>{{ $datos->orden_produccion->fecha_propuesta_atencion }}</td>
            </tr>
        </table>

        <!-- Tabla del reporte -->
        <table>
            <thead>
                <tr>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        CATEGORIA</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        MODELO</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        PRODUCTO</th> 
                    <th width="30"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        COLOR</th>
                    @foreach ($datos->tallasBD as $tallaBD)
                        <th width="15"
                            style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                            {{ $tallaBD->descripcion }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($lstProgramacionProduccion as $producto)
                    @foreach ($producto->colores as $color)
                        <tr>
                            <td>{{ $producto->categoria->nombre }}</td>
                            <td>{{ $producto->modelo->nombre }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $color->nombre }}</td>

                            @foreach ($datos->tallasBD as $tallaBD)
                                @php
                                    $tallas = $color->tallas;

                                    $tallasFiltradas = array_filter($tallas, function ($talla) use ($tallaBD) {
                                        return $talla->id == $tallaBD->id;
                                    });

                                    $cantidad_pendiente = !empty($tallasFiltradas)
                                        ? reset($tallasFiltradas)->cantidad_pendiente
                                        : '-';
                                @endphp
                                <td>{{ $cantidad_pendiente }}</td>
                            @endforeach
                        </tr>
                    @endforeach
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
