<?php

namespace App\Http\Services\Despachos\Embalaje;

use Exception;

class EmbalajeValidaciones
{
    public function validacionEmbalaje(array $data)
    {
        $similares     = $data['similares'];
        $empresa_envio = $data['empresa_envio'];

        if ($empresa_envio->boleta_obligatorio == 1) {
            $ventas_no_validas = collect($similares)->filter(function ($sim) {
                return $sim->tipo_venta_id == 129 && $sim->convert_en_serie == null;
            });

            if ($ventas_no_validas->isNotEmpty()) {
                throw new Exception("NO SE PUEDE EMBALAR, EXISTEN VENTAS SIN COMPROBANTE.");
            }
        }

        if ($empresa_envio->guia_obligatorio == 1) {
            $ventas_sin_guia = collect($similares)->filter(function ($sim) {
                return $sim->guia_id == null;
            });

            if ($ventas_sin_guia->isNotEmpty()) {
                throw new Exception("NO SE PUEDE EMBALAR, EXISTEN VENTAS SIN GUÍA GENERADA.");
            }

            $guias = collect($similares)->pluck('guia_id')->unique();

            if ($guias->count() > 1) {
                throw new Exception("NO SE PUEDE EMBALAR, TODAS LAS VENTAS DEBEN PERTENECER A LA MISMA GUÍA.");
            }
        }
    }

    public function validacionGuiaStore(array $datos)
    {
        $ventas =   $datos['similares'];
        $ventasInvalidas = $ventas->filter(function ($venta) {
            return $venta->tipo_venta_id == 129 && is_null($venta->convert_en_id);
        });

        if ($ventasInvalidas->isNotEmpty()) {
            $docs = $ventasInvalidas->pluck('documento_nro')->implode(', ');
            throw new Exception("Las siguientes ventas deben estar convertidas antes de generar la guía: {$docs}");
        }

        $ventasConGuia = $ventas->filter(function ($venta) {
            return !is_null($venta->guia_id);
        });

        if ($ventasConGuia->isNotEmpty()) {
            $docs = $ventasConGuia->pluck('documento_nro')->implode(', ');
            throw new Exception("Ya existe una guía para las siguientes ventas: {$docs}");
        }
    }
}
