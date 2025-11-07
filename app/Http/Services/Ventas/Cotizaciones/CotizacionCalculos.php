<?php

namespace App\Http\Services\Ventas\Cotizaciones;

use App\Ventas\Documento\Documento;

class CotizacionCalculos
{

    public function calcularMontos($montos,$lstCotizacion): object
    {
        //======= CALCULANDO MONTOS ========
        $monto_subtotal     =   0.0;
        $monto_embalaje     =   $montos->embalaje ?? 0;
        $monto_envio        =   $montos->envio ?? 0;
        $monto_total        =   0.0;
        $monto_igv          =   0.0;
        $monto_total_pagar  =   0.0;
        $monto_descuento    =   $montos->monto_descuento ?? 0;

        foreach ($lstCotizacion as $producto) {
            if (floatval($producto->porcentaje_descuento) == 0) {
                $monto_subtotal +=  ($producto->cantidad * $producto->precio_venta);
            } else {
                $monto_subtotal +=  ($producto->cantidad * $producto->precio_venta_nuevo);
            }
        }

        $monto_total_pagar      =   $monto_subtotal + $monto_embalaje + $monto_envio;
        $monto_total            =   $monto_total_pagar / 1.18;
        $monto_igv              =   $monto_total_pagar - $monto_total;
        $porcentaje_descuento   =   ($monto_descuento * 100) / ($monto_total_pagar);

        $montos =   (object)[
            'monto_subtotal'        =>  $monto_subtotal,
            'monto_embalaje'        =>  $monto_embalaje,
            'monto_envio'           =>  $monto_envio,
            'monto_total'           =>  $monto_total,
            'monto_igv'             =>  $monto_igv,
            'monto_total_pagar'     =>  $monto_total_pagar,
            'monto_descuento'       =>  $monto_descuento,
            'monto_total_pagar'     =>  $monto_total_pagar,
            'porcentaje_descuento'  =>  $porcentaje_descuento
        ];

        return $montos;
    }
}
