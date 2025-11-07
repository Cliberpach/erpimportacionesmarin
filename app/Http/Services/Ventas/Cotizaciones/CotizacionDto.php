<?php

namespace App\Http\Services\Ventas\Cotizaciones;

use App\Almacenes\Almacen;
use App\Almacenes\Color;
use App\Almacenes\Producto;
use App\Almacenes\Talla;
use App\Mantenimiento\Tabla\Detalle;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\User;
use App\Ventas\Cliente;
use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Exception;

class CotizacionDto
{

    public function prepararStoreDto(array $datos): array
    {
        $dto = [];

        $almacen            =   Almacen::find($datos['almacen']);
        $registrador        =   User::find($datos['registrador_id']);
        $cliente            =   Cliente::findOrFail($datos['cliente']);
        $origen_venta       =   Detalle::findOrFail($datos['origen_venta']);

        $dto['cliente_nombre']      =   $cliente->nombre;
        $dto['empresa_id']          =   1;
        $dto['cliente_id']          =   $cliente->id;
        $dto['condicion_id']        =   $datos['condicion_id'];
        $dto['registrador_id']      =   $datos['registrador_id'];
        $dto['registrador_nombre']  =   $registrador->usuario;
        $dto['fecha_documento']     =   Carbon::now()->format('Y-m-d');
        $dto['fecha_atencion']      =   Carbon::now()->format('Y-m-d');
        $dto['sede_id']             =   $datos['sede_id'];
        $dto['almacen_id']          =   $almacen->id;
        $dto['almacen_nombre']      =   $almacen->descripcion;
        $dto['sub_total']           =   $datos['montos']->monto_subtotal;
        $dto['monto_embalaje']      =   $datos['montos']->monto_embalaje;
        $dto['monto_envio']         =   $datos['montos']->monto_envio;
        $dto['total_igv']           =   $datos['montos']->monto_igv;
        $dto['total']               =   $datos['montos']->monto_total;
        $dto['total_pagar']         =   $datos['montos']->monto_total_pagar;
        $dto['monto_descuento']     =   $datos['montos']->monto_descuento;
        $dto['porcentaje_descuento']    =   $datos['montos']->porcentaje_descuento;
        $dto['moneda']                  =   4;
        $dto['igv']                     =   floatval($datos['porcentaje_igv']);
        $dto['igv_check']               =   "1";
        $dto['origen_venta_id']         =   $origen_venta->id;
        $dto['origen_venta_nombre']     =   $origen_venta->descripcion;
        $dto['telefono']                =   $datos['telefono'];
        return $dto;
    }


    public function prepararDetalleDto(array $lstItems, Cotizacion $cotizacion): array
    {
        $_dto = [];

        $almacen = Almacen::findOrFail($cotizacion->almacen_id);

        foreach ($lstItems as $item) {

            $existe_producto    =   Producto::find($item->producto_id);
            $existe_color       =   Color::find($item->color_id);
            $existe_talla       =   Talla::find($item->talla_id);

            if (!$existe_producto) {
                throw new Exception("EL PRODUCTO NO EXISTE EN LA BD!!!");
            }

            if (!$existe_color) {
                throw new Exception("EL COLOR NO EXISTE EN LA BD!!!");
            }

            if (!$existe_talla) {
                throw new Exception("LA TALLA NO EXISTE EN LA BD!!!");
            }

            $dto                            =   [];
            $dto['cotizacion_id']           =   $cotizacion->id;
            $dto['almacen_id']              =   $almacen->id;
            $dto['producto_id']             =   $item->producto_id;
            $dto['color_id']                =   $item->color_id;
            $dto['talla_id']                =   $item->talla_id;
            $dto['almacen_nombre']          =   $almacen->descripcion;
            $dto['producto_nombre']         =   $existe_producto->nombre;
            $dto['color_nombre']            =   $existe_color->descripcion;
            $dto['talla_nombre']            =   $existe_talla->descripcion;
            $dto['cantidad']                =   $item->cantidad;
            $dto['precio_unitario']         =   $item->precio_venta;
            $dto['importe']                 =   floatval($item->precio_venta) * floatval($item->cantidad);
            $dto['precio_unitario_nuevo']   =   floatval($item->precio_venta_nuevo);
            $dto['porcentaje_descuento']    =   floatval($item->porcentaje_descuento);
            $dto['monto_descuento']         =   floatval($dto['importe']) * floatval($item->porcentaje_descuento) / 100;
            $dto['importe_nuevo']           =   floatval($item->precio_venta_nuevo) * floatval($item->cantidad);
            $dto['tipo']                    =   'PRODUCTO';
            $_dto[] =   $dto;
        }

        if ($cotizacion->monto_embalaje != 0 && $cotizacion->monto_embalaje) {
            $almacen_ficticio               =   Almacen::where('tipo', 'FICTICIO')->where('estado', 'ANULADO')->where('descripcion', 'ALMACEN')->first();
            $producto_embalaje              =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'EMBALAJE')->first();
            $color_ficticio                 =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                 =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $dto                            = [];
            $dto['cotizacion_id']           = $cotizacion->id;
            $dto['almacen_id']              = $almacen_ficticio->id;
            $dto['producto_id']             = $producto_embalaje->id;
            $dto['color_id']                = $color_ficticio->id;
            $dto['talla_id']                = $talla_ficticio->id;
            $dto['almacen_nombre']          = $almacen_ficticio->descripcion;
            $dto['producto_nombre']         = $producto_embalaje->nombre;
            $dto['color_nombre']            = $color_ficticio->descripcion;
            $dto['talla_nombre']            = $talla_ficticio->descripcion;
            $dto['cantidad']                = 1;
            $dto['precio_unitario']         = $cotizacion->monto_embalaje;
            $dto['importe']                 = $cotizacion->monto_embalaje;
            $dto['precio_unitario_nuevo']   = floatval($cotizacion->monto_embalaje);
            $dto['porcentaje_descuento']    = 0;
            $dto['importe_nuevo']           = $cotizacion->monto_embalaje;
            $dto['monto_descuento']         = 0;
            $dto['tipo']                    = 'SERVICIO';

            $_dto[] =   $dto;
        }

        if ($cotizacion->monto_envio != 0 && $cotizacion->monto_envio) {
            $almacen_ficticio                   =   Almacen::where('tipo', 'FICTICIO')->where('estado', 'ANULADO')->where('descripcion', 'ALMACEN')->first();
            $producto_embalaje                  =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'ENVIO')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $dto                            = [];
            $dto['cotizacion_id']           = $cotizacion->id;
            $dto['almacen_id']              = $almacen_ficticio->id;
            $dto['producto_id']             = $producto_embalaje->id;
            $dto['color_id']                = $color_ficticio->id;
            $dto['talla_id']                = $talla_ficticio->id;
            $dto['almacen_nombre']          = $almacen_ficticio->descripcion;
            $dto['producto_nombre']         = $producto_embalaje->nombre;
            $dto['color_nombre']            = $color_ficticio->descripcion;
            $dto['talla_nombre']            = $talla_ficticio->descripcion;
            $dto['cantidad']                = 1;
            $dto['precio_unitario']         = $cotizacion->monto_envio;
            $dto['importe']                 = $cotizacion->monto_envio;
            $dto['precio_unitario_nuevo']   = floatval($cotizacion->monto_envio);
            $dto['porcentaje_descuento']    = 0;
            $dto['importe_nuevo']           = $cotizacion->monto_envio;
            $dto['monto_descuento']         = 0;
            $dto['tipo']                    = 'SERVICIO';

            $_dto[] =   $dto;
        }

        return $_dto;
    }
}
