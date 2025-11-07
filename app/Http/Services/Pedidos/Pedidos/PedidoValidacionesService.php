<?php

namespace App\Http\Services\Pedidos\Pedidos;

use App\Mantenimiento\Tabla\Detalle;
use App\Models\Reservas\Reservas\Pedido;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\Ventas\CuentaCliente;
use App\Ventas\EnvioVenta;
use Exception;
use Illuminate\Support\Facades\DB;

class PedidoValidacionesService
{

    public function validacionStore(array $datos):array{
        if(isset($datos['cotizacion_id'])){
            $cotizacion =   Cotizacion::findOrFail($datos['cotizacion_id']);
            if($cotizacion->pedido_id){
                throw new Exception("LA COTIZACIÓN YA FUE CONVERTIDA EN RESERVA: RE-".$cotizacion->pedido_id);
            }
            if($cotizacion->estado != 'VIGENTE'){
                throw new Exception("EL ESTADO DE LA COTIZACIÓN ES: ".$cotizacion->estado);
            }
            $datos['cotizacion']    =   $cotizacion;
        }
        return $datos;
    }

    public function validacionUpdate(array $datos, int $id): array
    {
        $pedido         =   Pedido::findOrFail($id);

        /*if ($pedido->doc_venta_credito_estado_pago === 'PAGADO') {
            throw new Exception("LAS RESERVAS PAGADAS NO PUEDEN EDITARSE");
        }*/

        if($pedido->estado != 'PENDIENTE'){
            throw new Exception("NO SE PERMITE MODIFICAR PEDIDOS CON ESTADO: ".$pedido->estado);
        }

        $productos                  =   json_decode($datos['lstPedido']);

        if (count($productos) === 0) {
            throw new Exception("EL DETALLE DE LA RESERVA ESTÁ VACÍO");
        }

        $origen_venta                   =   Detalle::findOrFail($datos['origen_venta']);

        $datos['pedido']                =   $pedido;
        $datos['productos']             =   $productos;
        $datos['origen_venta_id']       =   $origen_venta->id;
        $datos['origen_venta_nombre']   =   $origen_venta->descripcion;

        return $datos;
    }

    public function validacionPedidoCuenta(float $total, int $venta_id)
    {
        $cuenta_cliente =   CuentaCliente::where('cotizacion_documento_id', $venta_id)->first();
        $monto_pagado   =   floatval($cuenta_cliente->monto) - floatval($cuenta_cliente->saldo);

        /*if ($cuenta_cliente->estado === 'PAGADO') {
            throw new Exception("NO SE PUEDE EDITAR LA RESERVA, YA SE ENCUENTRA PAGADA POR COMPLETO");
        }*/

        if (floatval($total) < $monto_pagado) {
            throw new Exception(
                "El nuevo total de la reserva no puede ser menor a lo que el cliente ya pagó (" .
                    number_format($monto_pagado, 2) . ")."
            );
        }
    }

    public function validacionDestroy(Pedido $pedido)
    {

        $envio_venta =  EnvioVenta::where('documento_id', $pedido->doc_venta_credito_id)->first();
        if ($envio_venta) {

            if ($envio_venta->estado === 'DESPACHADO') {
                throw new Exception("NO SE PUEDE ELIMINAR UNA RESERVA PRESENTE EN UN REPARTO");
            }

            $envio_venta->estado = 'ANULADO';
            $envio_venta->update();

            if ($envio_venta->estado === 'EMBALADO') {
                DB::table('paquetes_embalados_detalle')
                ->where('envio_venta_id', $envio_venta->id)
                ->delete();
            }
        }
    }

    public function validacionCambiarCliente(array $datos){
        if(!isset($datos['pedido_id'])){
            throw new Exception("FALTA EL PARÁMETRO ID DEL PEDIDO EN LA PETICIÓN");
        }
        $pedido =   Pedido::findOrFail($datos['pedido_id']);
        if($pedido->estado_despacho !== 'PENDIENTE' && $pedido->estado_despacho !== 'S/D'){
            throw new Exception("NO SE PERMITE MODIFICAR PEDIDOS CON ESTADO DE DESPACHO: ".$pedido->estado_despacho);
        }
    }
}
