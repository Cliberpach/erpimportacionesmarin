<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Etiqueta Adhesiva</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jersey+25&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bevan:ital@0;1&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto Condensed', sans-serif;
            margin: 0;
            padding: 0;
        }

        div {
            box-sizing: border-box;
        }


        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 0;
            vertical-align: top;
        }

        .etiqueta {
            border: 0.1pt dashed black;
            border-radius: 4pt;
            width: 90%;
            margin: 0 auto;
            padding: 2pt;
            text-align: center;
        }

        .img_cod_barras {
            max-width: 100%;
            height: 20pt;
            margin-top: 1pt;
            display: block;
        }

        .empresa_logo {
            height: 30pt;
            max-width: 100pt;
            object-fit: contain;
            margin: 0;
            padding: 0;

        }

        .talla_nombre {
            margin: 0;
            padding: 0;
            font-family: "Righteous", sans-serif;
            font-size: 65pt;
            display: inline-block;
            line-height: 40pt;
            vertical-align: middle;
        }

        .producto {
            font-family: "Bevan", serif;
            font-size: 15pt;
            line-height: 10pt;
            vertical-align: middle;
        }

        .color {
            font-family: "Bevan", serif;
            font-size: 17pt;
            line-height: 10pt;
            vertical-align: middle;
        }

        /* Tamaño de etiqueta aproximado para impresión */
        @page {
            size: 76mm 50mm;
            margin: 5pt 1pt 0pt 1pt;
        }
    </style>
</head>

<body>
    @php
        $cantidad = $producto->cantidad;
        if ($cantidad > 100) {
            $cantidad = 50;
        }
    @endphp

    @for ($i = 0; $i < $cantidad; $i++)
        <table style="margin-bottom:2pt;">
            <tr>
                <td>
                    <div class="etiqueta">
                        <img src="{{ base_path() . '/storage/app/' . $producto->ruta_cod_barras }}"
                            class="img_cod_barras" alt="Código de Barras">
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td style="width: 50%;">
                    <div>
                        <div style="text-align:center;height: 30pt;">
                            @if ($empresa->ruta_logo)
                                <img src="{{ base_path() . '/storage/app/' . $empresa->ruta_logo }}"
                                    class="empresa_logo">
                            @else
                                <img src="{{ public_path() . '/img/default.jpg' }}" class="empresa_logo">
                            @endif
                        </div>
                        <div style="height: 75pt; text-align: center;">
                            <span class="talla_nombre">{{ $producto->talla_nombre }}</span>
                        </div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div>
                        <div style="border:1pt solid transparent;overflow: hidden;height: 60pt;text-align: center;">
                            <span class="producto"
                                style="vertical-align:middle;">{{ $producto->producto_nombre }}</span>
                        </div>
                        <div style="border:1pt solid transparent;overflow: hidden;height: 45pt;text-align: center;">
                            <span class="color">{{ $producto->color_nombre }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    @endfor

</body>

</html>
