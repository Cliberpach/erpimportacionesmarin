<?php

namespace App\Http\Services\Ventas\Guias;

use App\Mantenimiento\Empresa\Empresa;
use Exception;
use Illuminate\Support\Facades\DB;

class GuiaCorrelativo
{
    public function comprobanteActivo($sede_id, $tipo_comprobante)
    {
        $existe =   DB::select(
            'SELECT
                    enf.*
                    FROM empresa_numeracion_facturaciones AS enf
                    WHERE
                    enf.empresa_id = 1
                    AND enf.sede_id = ?
                    AND enf.tipo_comprobante = ?',
            [$sede_id, $tipo_comprobante->id]
        );

        if (count($existe) === 0) {
            throw new Exception($tipo_comprobante->descripcion . ', NO ESTÁ ACTIVO EN LA EMPRESA!!!');
        }
    }

/*
{#1844 // app\Http\Controllers\Ventas\RegistroVentaController.php:174
  +"correlativo": 1
  +"serie": "B001"
}
*/
    public static function getCorrelativo($tipo_comprobante, $sede_id)
    {

        $correlativo        =   null;
        $serie              =   null;

        //======= CONTABILIZANDO SI HAY DOCUMENTOS DE VENTA EMITIDOS PARA EL TYPE SALE ======
        $guias  =    DB::select(
            'select
                    count(*) as cant
                    from guias_remision as gr
                    where
                    gr.sede_usa_guia = ? ',
            [
                $sede_id,
            ]
        )[0];


        $serializacion     =   DB::select(
            'select
                                    enf.*
                                    from empresa_numeracion_facturaciones as enf
                                    where
                                    enf.empresa_id = ?
                                    and enf.tipo_comprobante = ?
                                    and enf.sede_id = ?',
            [
                Empresa::find(1)->id,
                $tipo_comprobante->id,
                $sede_id
            ]
        )[0];


        //==== SI LA CANT ES 0 =====
        if ($guias->cant === 0) {

            //====== INICIAR DESDE EL STARTING NUMBER =======
            $correlativo        =   $serializacion->numero_iniciar;
            $serie              =   $serializacion->serie;
        } else {
            //======= EN CASO YA EXISTAN DOCUMENTOS DE VENTA DEL TYPE SALE ======
            $correlativo        =   $guias->cant  +   1;
            $serie              =   $serializacion->serie;
        }

        return (object)['correlativo' => $correlativo, 'serie' => $serie];
    }
}
