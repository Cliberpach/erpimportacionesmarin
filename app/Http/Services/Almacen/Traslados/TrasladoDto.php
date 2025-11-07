<?php

namespace App\Http\Services\Almacen\Traslados;

use App\Almacenes\Almacen;
use App\Almacenes\Producto;
use App\Mantenimiento\Sedes\Sede;
use App\Models\Almacenes\Traslados\Traslado;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use Illuminate\Support\Collection;

class TrasladoDto
{

    public function getTrasladoDtoFromVenta(Documento $venta): array
    {
        $dto    =   [];

        $sede_destino_id    =   $venta->sede_id;

        $envio_venta    =   EnvioVenta::where('documento_id',$venta->id)->where('estado','ACTIVO')->first();
        if($envio_venta && $envio_venta->tipo_envio === 'RECOJO EN TIENDA'){
            $sede_destino_id    =   2;
        }

        $sede_destino       =   Sede::findOrFail($sede_destino_id);
        $almacen_origen_id  =   $venta->almacen_id;
        $almacen_destino    =   Almacen::where('sede_id', $sede_destino_id)->where('tipo_almacen', 'PRINCIPAL')->first();
        $almacen_origen     =   Almacen::findOrFail($almacen_origen_id);



        $dto['almacen_origen_id']       =   $almacen_origen_id;
        $dto['almacen_destino_id']      =   $almacen_destino->id;
        $dto['observacion']             =   'ENVIAR A ' . $sede_destino->nombre . ', MOTIVO VENTA: ' . $venta->serie . '-' . $venta->correlativo;
        $dto['sede_origen_id']          =   $almacen_origen->sede_id;
        $dto['sede_destino_id']         =   $sede_destino_id;
        $dto['fecha_traslado']          =   null;
        $dto['registrador_id']          =   $venta->user_id;
        $dto['registrador_nombre']      =   $venta->registrador_nombre;
        $dto['venta_id']                =   $venta->id;
        $dto['venta_serie']             =   $venta->serie.'-'.$venta->correlativo;
        $dto['envio_venta_id']          =   $envio_venta?$envio_venta->id:null;

        return $dto;
    }

    public function getTrasladoDetalleDtoFromVenta(Traslado $traslado,Documento $venta):array{
        $dto_detalles   =   [];
        $detalle_venta  =   Detalle::where('documento_id',$venta->id)->get();

        foreach ($detalle_venta as $item) {
            $d_item =   [];

            $d_item['traslado_id']      =   $traslado->id;
            $d_item['almacen_id']       =   $venta->almacen_id;
            $d_item['producto_id']      =   $item->producto_id;
            $d_item['color_id']         =   $item->color_id;
            $d_item['talla_id']         =   $item->talla_id;
            $d_item['almacen_nombre']   =   $venta->almacen_nombre;
            $d_item['producto_nombre']  =   $item->nombre_producto;
            $d_item['color_nombre']     =   $item->nombre_color;
            $d_item['talla_nombre']     =   $item->nombre_talla;
            $d_item['cantidad']         =   $item->cantidad;
            $d_item['tipo']             =   $item->tipo;

            $dto_detalles[] =   $d_item;
        }

        return $dto_detalles;
    }
}
