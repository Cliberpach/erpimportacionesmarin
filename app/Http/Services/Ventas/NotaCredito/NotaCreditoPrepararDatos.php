<?php

namespace App\Http\Services\Ventas\NotaCredito;

use App\Ventas\Documento\Documento;
use Illuminate\Support\Facades\Auth;

class NotaCreditoPrepararDatos
{

    public function prepararDatosStore(Documento $documento, array $datos, object $datos_correlativo)
    {
        $_datos  =   [
            'documento_id'      =>  $documento->id,
            'tipDocAfectado'    =>  $documento->tipoDocumento(),
            'numDocfectado'     =>  $documento->serie . '-' . $documento->correlativo,
            'codMotivo'         =>  $datos['cod_motivo'],
            'desMotivo'         =>  $datos['des_motivo'],
            'tipoDoc'           =>  $datos['tipo_nota'] === '0' ? '07' : '08',
            'fechaEmision'      =>  $datos['fecha_emision'],
            'ruc_empresa'       =>  $documento->ruc_empresa,
            'empresa'           =>  $documento->empresa,
            'direccion_fiscal_empresa'  =>  $documento->direccion_fiscal_empresa,
            'empresa_id'                =>  $documento->empresa_id,
            'cod_tipo_documento_cliente'    =>  $documento->tipoDocumentoCliente(),
            'tipo_documento_cliente'        =>  $documento->tipo_documento_cliente,
            'documento_cliente'             =>  $documento->documento_cliente,
            'direccion_cliente'             =>  $documento->direccion_cliente,
            'cliente'                       =>  $documento->cliente,
            'sunat'                         =>  '0',
            'tipo_nota'                     =>  $datos['tipo_nota'],
            'mtoOperGravadas'               =>  $datos['sub_total_nuevo'],
            'mtoIGV'                        =>  $datos['total_igv_nuevo'],
            'totalImpuestos'                =>  $datos['total_igv_nuevo'],
            'mtoImpVenta'                   =>  $datos['total_nuevo'],
            'value'                         =>  $datos['legenda'],
            'code'                          =>  '1000',
            'user_id'                       =>  Auth::user()->id,
            'serie'                         =>  $datos_correlativo->serie,
            'correlativo'                   =>  $datos_correlativo->correlativo,
            'sede_id'                       =>  Auth::user()->sede_id,
            'almacen_id'                    =>  $datos['almacen_id'],
            'almacen_nombre'                =>  $datos['almacen_nombre']
        ];

        return $_datos;
    }
}
