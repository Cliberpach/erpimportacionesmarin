<?php

namespace App\Http\Services\Pedidos\Pedidos;

use App\Almacenes\Color;
use App\Almacenes\Modelo;
use App\Almacenes\Producto;
use App\Almacenes\Talla;
use App\Mantenimiento\Empresa\Empresa;
use App\Models\Reservas\Reservas\Pedido;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\Ventas\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PedidoDto
{
    public function getDtoStoreFromCotizacion(array $datos): array
    {
        $cotizacion =   Cotizacion::findOrFail($datos['cotizacion_id']);
        //======= OBTENIENDO NOMBRE DEL CLIENTE =======
        $cliente    =   Cliente::findOrFail($cotizacion->cliente_id);
        $empresa    =   Empresa::findOrFail($cotizacion->empresa_id);
        $usuario    =   Auth::user();

        //======== CREAR PEDIDO =========
        $dto        =   [];

        $dto['cliente_id']              =   $cotizacion->cliente_id;
        $dto['cliente_nombre']          =   $cliente->nombre;
        $dto['empresa_id']              =   $cotizacion->empresa_id;
        $dto['empresa_nombre']          =   $empresa->razon_social;
        $dto['user_id']                 =   $cotizacion->registrador_id;
        $dto['user_nombre']             =   $usuario->usuario;
        $dto['condicion_id']            =   $cotizacion->condicion_id;
        $dto['moneda']                  =   $cotizacion->moneda;
        $dto['sub_total']               =   $cotizacion->sub_total;
        $dto['total']                   =   $cotizacion->total;
        $dto['total_igv']               =   $cotizacion->total_igv;
        $dto['total_pagar']             =   $cotizacion->total_pagar;
        $dto['monto_embalaje']          =   $cotizacion->monto_embalaje;
        $dto['monto_envio']             =   $cotizacion->monto_envio;
        $dto['porcentaje_descuento']    =   $cotizacion->porcentaje_descuento;
        $dto['monto_descuento']         =   $cotizacion->monto_descuento;
        $dto['fecha_registro']          =   Carbon::now()->format('Y-m-d');
        $dto['cotizacion_id']           =   $cotizacion->id;
        $dto['sede_id']                 =   $cotizacion->sede_id;
        $dto['almacen_id']              =   $cotizacion->almacen_id;

        return $dto;
    }

    public function getDtoDetalleFromCotizacion($detalle, Pedido $pedido): array
    {
        $dto    =   [];

        //=========== CREAR DETALLE DEL PEDIDO ========
        foreach ($detalle as $item) {

            $dto_item   =   [];

            $producto   =   Producto::findOrFail($item->producto_id);
            $color      =   Color::findOrFail($item->color_id);
            $talla      =   Talla::findOrFail($item->talla_id);
            $modelo     =   Modelo::findOrFail($producto->modelo_id);

            $dto_item['pedido_id']              = $pedido->id;
            $dto_item['almacen_id']             = $pedido->almacen_id;
            $dto_item['producto_id']            = $item->producto_id;
            $dto_item['color_id']               = $item->color_id;
            $dto_item['talla_id']               = $item->talla_id;

            $dto_item['producto_codigo']        = $producto->codigo;
            $dto_item['unidad']                 = 'NIU';
            $dto_item['producto_nombre']        = $producto->nombre;

            $dto_item['color_nombre']           = $color->descripcion;
            $dto_item['talla_nombre']           = $talla->descripcion;
            $dto_item['modelo_nombre']          = $modelo->descripcion;

            $dto_item['cantidad']               = $item->cantidad;
            $dto_item['precio_unitario']        = $item->precio_unitario;
            $dto_item['importe']                = $item->importe;
            $dto_item['porcentaje_descuento']   = $item->porcentaje_descuento;
            $dto_item['precio_unitario_nuevo']  = $item->precio_unitario_nuevo;
            $dto_item['importe_nuevo']          = $item->importe_nuevo;
            $dto_item['monto_descuento']        = $item->monto_descuento;
            $dto_item['cantidad_atendida']      = 0;
            $dto_item['cantidad_pendiente']     = $item->cantidad;
            $dto_item['tipo']                   = 'PRODUCTO';
            $dto_item['estado']                 = 'ACTIVO';

            $dto[]  =   $dto_item;
        }

        if ($pedido->monto_embalaje != 0 && $pedido->monto_embalaje) {
            $producto_embalaje                  =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'EMBALAJE')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $modelo_ficticio                    =   Modelo::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $dto_item['almacen_id']             = $pedido->almacen_id;
            $dto_item['pedido_id']              = $pedido->id;
            $dto_item['producto_id']            = $producto_embalaje->id;
            $dto_item['color_id']               = $color_ficticio->id;
            $dto_item['talla_id']               = $talla_ficticio->id;
            $dto_item['producto_codigo']        = 'EMBALAJE';
            $dto_item['producto_nombre']        = $producto_embalaje->nombre;
            $dto_item['color_nombre']           = $color_ficticio->descripcion;
            $dto_item['talla_nombre']           = $talla_ficticio->descripcion;
            $dto_item['modelo_nombre']          = $modelo_ficticio->descripcion;
            $dto_item['cantidad']               = 1;
            $dto_item['cantidad_atendida']      = 0;
            $dto_item['cantidad_pendiente']     = 1;
            $dto_item['precio_unitario']        = $pedido->monto_embalaje;
            $dto_item['importe']                = $pedido->monto_embalaje;
            $dto_item['porcentaje_descuento']   = 0;
            $dto_item['precio_unitario_nuevo']  = $pedido->monto_embalaje;
            $dto_item['importe_nuevo']          = $pedido->monto_embalaje;
            $dto_item['monto_descuento']        = 0;
            $dto_item['tipo']                   = 'SERVICIO';
            $dto_item['estado']                 = 'ACTIVO';

            $dto[]  =   $dto_item;
        }

        if ($pedido->monto_envio != 0 && $pedido->monto_envio) {
            $producto_envio                     =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'ENVIO')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $modelo_ficticio                    =   Modelo::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $dto_item['almacen_id']             = $pedido->almacen_id;
            $dto_item['pedido_id']              = $pedido->id;
            $dto_item['producto_id']            = $producto_envio->id;
            $dto_item['color_id']               = $color_ficticio->id;
            $dto_item['talla_id']               = $talla_ficticio->id;
            $dto_item['producto_codigo']        = 'ENVIO';
            $dto_item['producto_nombre']        = $producto_envio->nombre;
            $dto_item['color_nombre']           = $color_ficticio->descripcion;
            $dto_item['talla_nombre']           = $talla_ficticio->descripcion;
            $dto_item['modelo_nombre']          = $modelo_ficticio->descripcion;
            $dto_item['cantidad']               = 1;
            $dto_item['cantidad_atendida']      = 0;
            $dto_item['cantidad_pendiente']     = 1;
            $dto_item['precio_unitario']        = $pedido->monto_envio;
            $dto_item['importe']                = $pedido->monto_envio;
            $dto_item['porcentaje_descuento']   = 0;
            $dto_item['precio_unitario_nuevo']  = $pedido->monto_envio;
            $dto_item['importe_nuevo']          = $pedido->monto_envio;
            $dto_item['monto_descuento']        = 0;
            $dto_item['tipo']                   = 'SERVICIO';
            $dto_item['estado']                 = 'ACTIVO';

            $dto[]  =   $dto_item;
        }

        return $dto;
    }
}
