<?php

namespace App\Http\Services\Ventas\NotaCredito;

use App\Mantenimiento\Empresa\Empresa;
use Illuminate\Support\Facades\DB;

class CorrelativoService
{

    public static function getCorrelativo($sede_id, $tipo_venta_id):object
    {

        $parametro          =   null;
        $tipDocAfectado     =   null;
        $correlativo        =   null;
        $serie              =   null;


        //===== 127:FACTURA | 128:BOLETA | 129:NOTA DE VENTA ======
        if ($tipo_venta_id == 127) {
            $tipDocAfectado =   '01';
            $parametro      =   "FF";   //====== NOTA CRÉDITO FACTURA =======
            $message        =   "NOTAS DE CRÉDITO DE FACTURAS ELECTRÓNICAS";
        }
        if ($tipo_venta_id == 128) {
            $tipDocAfectado =   '03';
            $parametro      =   "BB";   //======= NOTA CRÉDITO BOLETA =====
            $message        =   "NOTAS DE CRÉDITO DE BOLETAS ELECTRÓNICAS";
        }
        if ($tipo_venta_id == 129) {
            $tipDocAfectado =   '04';
            $parametro      =   "NN";   //===== NOTA DEVOLUCIÓN =======
            $message        =   "NOTAS DE DEVOLUCIÓN DE NOTAS DE VENTA";
        }

        //===== OBTENIENDO LA ÚLTIMA NOTA ELECTRÓNICA DE LA SEDE =======
        $ultima_nota =      DB::select(
            'SELECT
                            n.serie,
                            n.correlativo
                            FROM nota_electronica AS n
                            WHERE n.sede_id = ?
                            AND n.tipDocAfectado = ?
                            ORDER BY n.id DESC
                            LIMIT 1',
            [
                $sede_id,
                $tipDocAfectado
            ]
        );

        $tipo_comprobante   =   DB::select('SELECT
                                td.*
                                FROM tabladetalles AS td
                                WHERE
                                td.tabla_id = 21
                                AND td.parametro = ?', [$parametro])[0];

        $serializacion  =   DB::select(
                                'SELECT
                                    enf.*
                                    FROM empresa_numeracion_facturaciones AS enf
                                    WHERE
                                    enf.empresa_id = ?
                                    AND enf.tipo_comprobante = ?
                                    AND enf.sede_id = ?',
                                [
                                    Empresa::find(1)->id,
                                    $tipo_comprobante->id,
                                    $sede_id
                                ]
                            )[0];

        //======== EN CASO NO EXISTAN NOTAS GENERADAS =====
        if (count($ultima_nota) === 0) {

            $correlativo        =   $serializacion->numero_iniciar;
            $serie              =   $serializacion->serie;
        } else {

            //======= EN CASO YA EXISTAN NOTAS DE CREDITO DEL TYPE SALE ======
            $correlativo    =   $ultima_nota[0]->correlativo + 1;
            $serie          =   $ultima_nota[0]->serie;
        }


        return (object)[
            'tipo_comprobante'  =>  $tipo_comprobante,
            'serie'             =>  $serie,
            'correlativo'       =>  $correlativo
        ];
    }
}
