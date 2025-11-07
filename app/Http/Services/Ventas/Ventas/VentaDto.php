<?php

namespace App\Http\Services\Ventas\Ventas;

use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VentaDto
{
    public function getDtoStoreFromCotizacion($datos): array
    {

        $dto    =   [];
        $dto['fecha_documento']     =   Carbon::now()->toDateString();
        $dto['fecha_atencion']      =   Carbon::now()->toDateString();

        //======== CONDICIÓN ========
        $condicion  =   $datos['condicion'];
        $dto['condicion_id']            = $condicion->id;
        if ($condicion->id != 1) {
            $nro_dias                       = $condicion->dias;
            $dto['fecha_vencimiento']       = Carbon::now()->addDays($nro_dias)->toDateString();
        } else {
            $dto['fecha_vencimiento']   = Carbon::now()->toDateString();
        }

        //======== EMPRESA ========
        $empresa                            =   $datos['empresa'];
        $dto['ruc_empresa']                 =   $empresa->ruc;
        $dto['empresa']                     =   $empresa->razon_social;
        $dto['direccion_fiscal_empresa']    =   $empresa->direccion_fiscal;
        $dto['empresa_id']                  =   $empresa->id;

        //========= CLIENTE =======
        $cliente                           = $datos['cliente'];
        $dto['tipo_documento_cliente']     = $cliente->tipo_documento;
        $dto['documento_cliente']          = $cliente->documento;
        $dto['direccion_cliente']          = $cliente->direccion;
        $dto['cliente']                    = $cliente->nombre;
        $dto['cliente_id']                 = $cliente->id;

        //======== TIPO VENTA ======
        $tipo_venta                     = $datos['tipo_venta'];
        $dto['tipo_venta_id']           = $tipo_venta->id;   //boleta,factura,nota_venta
        $dto['tipo_venta_nombre']       = $tipo_venta->descripcion;

        //======== OBSERVACION Y USUARIO =======
        $dto['observacion']             = $datos['observacion'];
        $dto['user_id']                 = Auth::user()->id;
        $dto['registrador_nombre']      = Auth::user()->usuario;

        //========= MONTOS Y MONEDA ========
        $cotizacion =   $datos['cotizacion'];
        $montos     =   $datos['montos'];
        $dto['sub_total']               =   $cotizacion->sub_total;
        $dto['monto_embalaje']          =   $cotizacion->monto_embalaje;
        $dto['monto_envio']             =   $cotizacion->monto_envio;
        $dto['total']                   =   $cotizacion->total;
        $dto['total_igv']               =   $cotizacion->total_igv;
        $dto['total_pagar']             =   $cotizacion->total_pagar;
        $dto['igv']                     =   $cotizacion->igv;
        $dto['monto_descuento']         =   $cotizacion->monto_descuento;
        $dto['porcentaje_descuento']    =   $cotizacion->porcentaje_descuento;
        $dto['mto_oper_gravadas_sunat'] =   $montos->mtoOperGravadasSunat;
        $dto['mto_igv_sunat']           =   $montos->mtoIgvSunat;
        $dto['total_impuestos_sunat']   =   $montos->totalImpuestosSunat;
        $dto['valor_venta_sunat']       =   $montos->valorVentaSunat;
        $dto['sub_total_sunat']         =   $montos->subTotalSunat;
        $dto['mto_imp_venta_sunat']     =   $montos->mtoImpVentaSunat;
        $dto['moneda']                  = 1;

        //======= SERIE Y CORRELATIVO ======
        $datos_correlativo      =   $datos['datos_correlativo'];
        $dto['serie']           =   $datos_correlativo->serie;
        $dto['correlativo']     =   $datos_correlativo->correlativo;

        $dto['legenda']             = $datos['legenda'];
        $dto['sede_id']             = $cotizacion->sede_id;
        $dto['almacen_id']          = $cotizacion->almacen_id;
        $dto['almacen_nombre']      = $cotizacion->almacen_nombre;
        $dto['cotizacion_venta']    = $cotizacion->id;
        $dto['estado_pago']         = 'PENDIENTE';

        //======== CAJA =======
        $caja_movimiento            =   $datos['caja_movimiento'];
        $dto['caja_id']             =   $caja_movimiento->caja_id;
        $dto['caja_movimiento_id '] =   $caja_movimiento->movimiento_id;
        $dto['caja_nombre ']        =   $caja_movimiento->caja_nombre;

        return $dto;
    }

    public function getDtoDetalleFromCotizacion($item,Documento $documento):array
    {
        $dto    =   [];

        $dto['documento_id']            =   $documento->id;
        $dto['almacen_id']              =   $item->almacen_id;
        $dto['producto_id']             =   $item->producto_id;
        $dto['color_id']                =   $item->color_id;
        $dto['talla_id']                =   $item->talla_id;
        $dto['almacen_nombre']          =   $item->almacen_nombre;
        $dto['codigo_producto']         =   $item->producto_codigo;
        $dto['nombre_producto']         =   $item->producto_nombre;
        $dto['nombre_color']            =   $item->color_nombre;
        $dto['nombre_talla']            =   $item->talla_nombre;
        $dto['nombre_modelo']           =   $item->modelo_nombre;
        $dto['cantidad']                =   floatval($item->cantidad);
        $dto['precio_unitario']         =   floatval($item->precio_unitario);
        $dto['importe']                 =   $item->importe;
        $dto['precio_unitario_nuevo']   =   floatval($item->precio_unitario_nuevo);
        $dto['porcentaje_descuento']    =   floatval($item->porcentaje_descuento);
        $dto['monto_descuento']         =   $item->monto_descuento;
        $dto['importe_nuevo']           =   $item->importe_nuevo;
        $dto['cantidad_sin_cambio']     =   (int) $item->cantidad;

        return $dto;
    }
}
