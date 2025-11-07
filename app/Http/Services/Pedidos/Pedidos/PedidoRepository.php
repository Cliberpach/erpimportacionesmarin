<?php

namespace App\Http\Services\Pedidos\Pedidos;

use App\Almacenes\Almacen;
use App\Almacenes\Color;
use App\Almacenes\Modelo;
use App\Almacenes\Producto;
use App\Almacenes\Talla;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Tabla\Detalle as TablaDetalle;
use App\Models\Reservas\Reservas\Pedido;
use App\Models\Reservas\Reservas\PedidoDetalle as ReservasPedidoDetalle;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\User;
use App\Ventas\Cliente;
use App\Ventas\CuentaCliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\PedidoDetalle;
use Illuminate\Support\Facades\DB;

class PedidoRepository
{
    public function insertarPedido(array $datos, Empresa $empresa, Cliente $cliente, object $montos): Pedido
    {
        $pedido                      =   new Pedido();
        $pedido->cliente_id          =   $datos['cliente'];


        $pedido->cliente_nombre     =   $cliente->nombre;
        $pedido->cliente_telefono   =   $cliente->telefono_movil;
        //==========================================//

        $pedido->empresa_id         =  1;

        $pedido->empresa_nombre     =   $empresa->razon_social;
        //==========================================//

        $pedido->condicion_id       =   $datos['condicion_id'];
        $pedido->user_id            =   $datos['registrador_id'];

        //======== OBTENIENDO EL NOMBRE COMPLETO DEL USUARIO ===========
        $pedido->user_nombre        =   User::find($datos['registrador_id'])->usuario;

        //=============================================================
        $pedido->moneda                 =   1;
        $pedido->fecha_registro         =   now()->toDateString();
        $pedido->fecha_propuesta        =   $datos['fecha_propuesta'];

        $pedido->monto_embalaje         =   $montos->monto_embalaje;
        $pedido->monto_envio            =   $montos->monto_envio;
        $pedido->sub_total              =   $montos->monto_subtotal;
        $pedido->total_igv              =   $montos->monto_igv;
        $pedido->total                  =   $montos->monto_total;
        $pedido->total_pagar            =   $montos->monto_total_pagar;
        $pedido->monto_descuento        =   $montos->monto_descuento;
        $pedido->porcentaje_descuento   =   $montos->porcentaje_descuento;

        $pedido->sede_id        =   $datos['sede_id'];
        $pedido->almacen_id     =   $datos['almacen'];
        $pedido->almacen_nombre =   Almacen::findOrFail($pedido->almacen_id)->descripcion;
        $pedido->telefono       =   $datos['telefono'];

        //======== ORIGEN VENTA ==========
        $origen_venta   =   TablaDetalle::findOrFail($datos['origen_venta']);
        $pedido->origen_venta_id        =   $origen_venta->id;
        $pedido->origen_venta_nombre    =   $origen_venta->descripcion;
        $pedido->save();

        return $pedido;
    }

    public function insertarDetallePedido(array $lstPedido, array $datos, Pedido $pedido)
    {
        foreach ($lstPedido as $producto) {

            $producto_bd    =   Producto::findOrFail($producto->producto_id);
            $modelo         =   Modelo::findOrFail($producto_bd->modelo_id);

            foreach ($producto->tallas as  $talla) {
                //===== CALCULANDO MONTOS PARA EL DETALLE =====
                $importe        =   floatval($talla->cantidad) * floatval($producto->precio_venta);
                $importe_nuevo  =   floatval($talla->cantidad) * floatval($producto->precio_venta_nuevo);


                $detalle                        = new PedidoDetalle();
                $detalle->almacen_id            = $datos['almacen'];
                $detalle->pedido_id             = $pedido->id;
                $detalle->producto_id           = $producto->producto_id;
                $detalle->color_id              = $producto->color_id;
                $detalle->talla_id              = $talla->talla_id;
                $detalle->producto_codigo       = $producto->producto_codigo;
                $detalle->producto_nombre       = $producto->producto_nombre;
                $detalle->color_nombre          = $producto->color_nombre;
                $detalle->talla_nombre          = $talla->talla_nombre;
                $detalle->modelo_nombre         = $modelo->descripcion;
                $detalle->cantidad              = $talla->cantidad;
                $detalle->cantidad_atendida     = 0;
                $detalle->cantidad_pendiente    = $talla->cantidad;
                $detalle->precio_unitario       = $producto->precio_venta;
                $detalle->importe               = $importe;
                $detalle->porcentaje_descuento  = floatval($producto->porcentaje_descuento);
                $detalle->precio_unitario_nuevo = floatval($producto->precio_venta_nuevo);
                $detalle->importe_nuevo         = $importe_nuevo;
                $detalle->monto_descuento       = floatval($importe) * floatval($producto->porcentaje_descuento) / 100;
                $detalle->save();
            }
        }

        if ($pedido->monto_embalaje != 0 && $pedido->monto_embalaje) {
            $producto_embalaje                  =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'EMBALAJE')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $modelo_ficticio                    =   Modelo::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $detalle                        = new PedidoDetalle();
            $detalle->almacen_id            = $pedido->almacen_id;
            $detalle->pedido_id             = $pedido->id;
            $detalle->producto_id           = $producto_embalaje->id;
            $detalle->color_id              = $color_ficticio->id;
            $detalle->talla_id              = $talla_ficticio->id;
            $detalle->producto_codigo       = 'EMBALAJE';
            $detalle->producto_nombre       = $producto_embalaje->nombre;;
            $detalle->color_nombre          = $color_ficticio->descripcion;
            $detalle->talla_nombre          = $talla_ficticio->descripcion;
            $detalle->modelo_nombre         = $modelo_ficticio->descripcion;
            $detalle->cantidad              = 1;
            $detalle->cantidad_atendida     = 0;
            $detalle->cantidad_pendiente    = 1;
            $detalle->precio_unitario       = $pedido->monto_embalaje;
            $detalle->importe               = $pedido->monto_embalaje;
            $detalle->porcentaje_descuento  = 0;
            $detalle->precio_unitario_nuevo = $pedido->monto_embalaje;
            $detalle->importe_nuevo         = $pedido->monto_embalaje;
            $detalle->monto_descuento       = 0;
            $detalle->tipo                  = 'SERVICIO';
            $detalle->estado                = 'ACTIVO';
            $detalle->save();
        }

        if ($pedido->monto_envio != 0 && $pedido->monto_envio) {
            $producto_envio                     =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'ENVIO')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $modelo_ficticio                    =   Modelo::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $detalle                        = new PedidoDetalle();
            $detalle->almacen_id            = $pedido->almacen_id;
            $detalle->pedido_id             = $pedido->id;
            $detalle->producto_id           = $producto_envio->id;
            $detalle->color_id              = $color_ficticio->id;
            $detalle->talla_id              = $talla_ficticio->id;
            $detalle->producto_codigo       = 'ENVIO';
            $detalle->producto_nombre       = $producto_envio->nombre;;
            $detalle->color_nombre          = $color_ficticio->descripcion;
            $detalle->talla_nombre          = $talla_ficticio->descripcion;
            $detalle->modelo_nombre         = $modelo_ficticio->descripcion;
            $detalle->cantidad              = 1;
            $detalle->cantidad_atendida     = 0;
            $detalle->cantidad_pendiente    = 1;
            $detalle->precio_unitario       = $pedido->monto_envio;
            $detalle->importe               = $pedido->monto_envio;
            $detalle->porcentaje_descuento  = 0;
            $detalle->precio_unitario_nuevo = $pedido->monto_envio;
            $detalle->importe_nuevo         = $pedido->monto_envio;
            $detalle->monto_descuento       = 0;
            $detalle->tipo                  = 'SERVICIO';
            $detalle->estado                = 'ACTIVO';
            $detalle->save();
        }
    }

    public function insertarItemPedidoDetalle(object $item)
    {
        $producto_bd    =   Producto::findOrFail($item->producto_id);
        $modelo         =   Modelo::findOrFail($producto_bd->modelo_id);

        //===== CALCULANDO MONTOS PARA EL DETALLE =====
        $importe        =   floatval($item->cantidad) * floatval($item->precio_venta);
        $importe_nuevo  =   floatval($item->cantidad) * floatval($item->precio_venta_nuevo);

        $detalle                        = new PedidoDetalle();
        $detalle->almacen_id            = $item->almacen_id;
        $detalle->pedido_id             = $item->pedido_id;
        $detalle->producto_id           = $item->producto_id;
        $detalle->color_id              = $item->color_id;
        $detalle->talla_id              = $item->talla_id;
        $detalle->producto_codigo       = $item->producto_codigo;
        $detalle->producto_nombre       = $item->producto_nombre;
        $detalle->color_nombre          = $item->color_nombre;
        $detalle->talla_nombre          = $item->talla_nombre;
        $detalle->modelo_nombre         = $modelo->descripcion;
        $detalle->cantidad              = $item->cantidad;
        $detalle->cantidad_atendida     = 0;
        $detalle->cantidad_pendiente    = $item->cantidad;
        $detalle->precio_unitario       = $item->precio_venta;
        $detalle->importe               = $importe;
        $detalle->porcentaje_descuento  = floatval($item->porcentaje_descuento);
        $detalle->precio_unitario_nuevo = floatval($item->precio_venta_nuevo);
        $detalle->importe_nuevo         = $importe_nuevo;
        $detalle->monto_descuento       = floatval($importe) * floatval($item->porcentaje_descuento) / 100;
        $detalle->save();
    }

    public function insertarItemVentaDetalle(object $item, int $venta_id)
    {
        $producto_bd    =   Producto::findOrFail($item->producto_id);
        $modelo         =   Modelo::findOrFail($producto_bd->modelo_id);

        $importe                =   floatval($item->cantidad) * floatval($item->precio_venta);
        $precio_unitario        =   $item->porcentaje_descuento == 0 ? $item->precio_venta : $item->precio_venta_nuevo;

        $almacen    =   Almacen::findOrFail($item->almacen_id);

        $detalle                            =   new Detalle();
        $detalle->documento_id              =   $venta_id;
        $detalle->almacen_id                =   $item->almacen_id;
        $detalle->producto_id               =   $item->producto_id;
        $detalle->color_id                  =   $item->color_id;
        $detalle->talla_id                  =   $item->talla_id;
        $detalle->almacen_nombre            =   $almacen->descripcion;
        $detalle->codigo_producto           =   $item->producto_codigo;
        $detalle->nombre_producto           =   $item->producto_nombre;
        $detalle->nombre_color              =   $item->color_nombre;
        $detalle->nombre_talla              =   $item->talla_nombre;
        $detalle->nombre_modelo             =   $modelo->descripcion;
        $detalle->cantidad                  =   floatval($item->cantidad);
        $detalle->precio_unitario           =   floatval($item->precio_venta);
        $detalle->importe                   =   $importe;
        $detalle->precio_unitario_nuevo     =   floatval($precio_unitario);
        $detalle->porcentaje_descuento      =   floatval($item->porcentaje_descuento);
        $detalle->monto_descuento           =   floatval($importe) * floatval($item->porcentaje_descuento) / 100;
        $detalle->importe_nuevo             =   floatval($precio_unitario) * floatval($item->cantidad);
        $detalle->cantidad_sin_cambio       =   (int) $item->cantidad;
        $detalle->save();
    }

    public function actualizarItemPedidoDetalle(object $item)
    {
        //===== CALCULANDO MONTOS PARA EL DETALLE =====
        $importe        =   floatval($item->cantidad_nueva) * floatval($item->precio_venta);
        $importe_nuevo  =   floatval($item->cantidad_nueva) * floatval($item->precio_venta_nuevo);

        DB::table('pedidos_detalles')
            ->where('pedido_id', $item->pedido_id)
            ->where('almacen_id', $item->almacen_id)
            ->where('producto_id', $item->producto_id)
            ->where('color_id', $item->color_id)
            ->where('talla_id', $item->talla_id)
            ->update([
                'precio_unitario'               => $item->precio_venta,
                'importe'                       => $importe,
                'porcentaje_descuento'          => floatval($item->porcentaje_descuento),
                'precio_unitario_nuevo'         => floatval($item->precio_venta_nuevo),
                'importe_nuevo'                 => $importe_nuevo,
                'monto_descuento'               => floatval($importe) * floatval($item->porcentaje_descuento) / 100,
                'cantidad'                      => $item->cantidad_nueva,
                'updated_at'                    => now()
            ]);
    }

    public function actualizarItemVentaDetalle(object $item, int $venta_id)
    {
        //===== CALCULANDO MONTOS PARA EL DETALLE =====
        $importe        =   floatval($item->cantidad_nueva) * floatval($item->precio_venta);
        $importe_nuevo  =   floatval($item->cantidad_nueva) * floatval($item->precio_venta_nuevo);

        DB::table('cotizacion_documento_detalles')
            ->where('documento_id', $venta_id)
            ->where('almacen_id', $item->almacen_id)
            ->where('producto_id', $item->producto_id)
            ->where('color_id', $item->color_id)
            ->where('talla_id', $item->talla_id)
            ->update([
                'precio_unitario'               => $item->precio_venta,
                'importe'                       => $importe,
                'precio_unitario_nuevo'         => floatval($item->precio_venta_nuevo),
                'porcentaje_descuento'          => floatval($item->porcentaje_descuento),
                'monto_descuento'               => floatval($importe) * floatval($item->porcentaje_descuento) / 100,
                'importe_nuevo'                 => $importe_nuevo,
                'cantidad'      =>  $item->cantidad_nueva,
                'updated_at'    =>  now()
            ]);
    }

    public function operarServiciosPedidoDetalle(Pedido $pedido)
    {

        DB::table('pedidos_detalles')
            ->where('pedido_id', $pedido->id)
            ->where('tipo', 'SERVICIO')
            ->delete();

        DB::table('cotizacion_documento_detalles')
            ->where('documento_id', $pedido->doc_venta_credito_id)
            ->where('tipo', 'SERVICIO')
            ->delete();

        $almacen    =   Almacen::findOrFail($pedido->almacen_id);

        if ($pedido->monto_embalaje != 0 && $pedido->monto_embalaje) {
            $producto_embalaje                  =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'EMBALAJE')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $modelo_ficticio                    =   Modelo::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $detalle                        = new PedidoDetalle();
            $detalle->almacen_id            = $pedido->almacen_id;
            $detalle->pedido_id             = $pedido->id;
            $detalle->producto_id           = $producto_embalaje->id;
            $detalle->color_id              = $color_ficticio->id;
            $detalle->talla_id              = $talla_ficticio->id;
            $detalle->producto_codigo       = 'EMBALAJE';
            $detalle->producto_nombre       = $producto_embalaje->nombre;;
            $detalle->color_nombre          = $color_ficticio->descripcion;
            $detalle->talla_nombre          = $talla_ficticio->descripcion;
            $detalle->modelo_nombre         = $modelo_ficticio->descripcion;
            $detalle->cantidad              = 1;
            $detalle->cantidad_atendida     = 0;
            $detalle->cantidad_pendiente    = 1;
            $detalle->precio_unitario       = $pedido->monto_embalaje;
            $detalle->importe               = $pedido->monto_embalaje;
            $detalle->porcentaje_descuento  = 0;
            $detalle->precio_unitario_nuevo = $pedido->monto_embalaje;
            $detalle->importe_nuevo         = $pedido->monto_embalaje;
            $detalle->monto_descuento       = 0;
            $detalle->tipo                  = 'SERVICIO';
            $detalle->estado                = 'ACTIVO';
            $detalle->save();

            $detalle                            =   new Detalle();
            $detalle->documento_id              =   $pedido->doc_venta_credito_id;
            $detalle->almacen_id                =   $pedido->almacen_id;
            $detalle->producto_id               =   $producto_embalaje->id;
            $detalle->color_id                  =   $color_ficticio->id;
            $detalle->talla_id                  =   $talla_ficticio->id;
            $detalle->almacen_nombre            =   $almacen->descripcion;
            $detalle->codigo_producto           =   'EMBALAJE';
            $detalle->nombre_producto           =   $producto_embalaje->nombre;
            $detalle->nombre_color              =   $color_ficticio->descripcion;
            $detalle->nombre_talla              =   $talla_ficticio->descripcion;
            $detalle->nombre_modelo             =   $modelo_ficticio->descripcion;
            $detalle->cantidad                  =   1;
            $detalle->precio_unitario           =   $pedido->monto_embalaje;
            $detalle->importe                   =   $pedido->monto_embalaje;
            $detalle->precio_unitario_nuevo     =   $pedido->monto_embalaje;
            $detalle->porcentaje_descuento      =   0;
            $detalle->monto_descuento           =   0;
            $detalle->importe_nuevo             =   $pedido->monto_embalaje;
            $detalle->cantidad_sin_cambio       =   1;
            $detalle->tipo                      =   'SERVICIO';
            $detalle->save();
        }

        if ($pedido->monto_envio != 0 && $pedido->monto_envio) {
            $producto_envio                     =   Producto::where('tipo', 'FICTICIO')->where('nombre', 'ENVIO')->first();
            $color_ficticio                     =   Color::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $talla_ficticio                     =   Talla::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();
            $modelo_ficticio                    =   Modelo::where('tipo', 'FICTICIO')->where('descripcion', 'SERVICIO')->first();

            $detalle                        = new PedidoDetalle();
            $detalle->almacen_id            = $pedido->almacen_id;
            $detalle->pedido_id             = $pedido->id;
            $detalle->producto_id           = $producto_envio->id;
            $detalle->color_id              = $color_ficticio->id;
            $detalle->talla_id              = $talla_ficticio->id;
            $detalle->producto_codigo       = 'ENVIO';
            $detalle->producto_nombre       = $producto_envio->nombre;;
            $detalle->color_nombre          = $color_ficticio->descripcion;
            $detalle->talla_nombre          = $talla_ficticio->descripcion;
            $detalle->modelo_nombre         = $modelo_ficticio->descripcion;
            $detalle->cantidad              = 1;
            $detalle->cantidad_atendida     = 0;
            $detalle->cantidad_pendiente    = 1;
            $detalle->precio_unitario       = $pedido->monto_envio;
            $detalle->importe               = $pedido->monto_envio;
            $detalle->porcentaje_descuento  = 0;
            $detalle->precio_unitario_nuevo = $pedido->monto_envio;
            $detalle->importe_nuevo         = $pedido->monto_envio;
            $detalle->monto_descuento       = 0;
            $detalle->tipo                  = 'SERVICIO';
            $detalle->estado                = 'ACTIVO';
            $detalle->save();

            $detalle                            =   new Detalle();
            $detalle->documento_id              =   $pedido->doc_venta_credito_id;
            $detalle->almacen_id                =   $pedido->almacen_id;
            $detalle->producto_id               =   $producto_envio->id;
            $detalle->color_id                  =   $color_ficticio->id;
            $detalle->talla_id                  =   $talla_ficticio->id;
            $detalle->almacen_nombre            =   $almacen->descripcion;
            $detalle->codigo_producto           =   'ENVIO';
            $detalle->nombre_producto           =   $producto_envio->nombre;
            $detalle->nombre_color              =   $color_ficticio->descripcion;
            $detalle->nombre_talla              =   $talla_ficticio->descripcion;
            $detalle->nombre_modelo             =   $modelo_ficticio->descripcion;
            $detalle->cantidad                  =   1;
            $detalle->precio_unitario           =   $pedido->monto_envio;
            $detalle->importe                   =   $pedido->monto_envio;
            $detalle->precio_unitario_nuevo     =   $pedido->monto_envio;
            $detalle->porcentaje_descuento      =   0;
            $detalle->monto_descuento           =   0;
            $detalle->importe_nuevo             =   $pedido->monto_envio;
            $detalle->cantidad_sin_cambio       =   1;
            $detalle->tipo                      =   'SERVICIO';
            $detalle->save();
        }
    }

    public function enlazarPedidoVentaCredito(Pedido $pedido, Documento $venta_credito)
    {
        $pedido->doc_venta_credito_id           =   $venta_credito->id;
        $pedido->doc_venta_credito_serie        =   $venta_credito->serie;
        $pedido->doc_venta_credito_correlativo  =   $venta_credito->correlativo;
        $pedido->doc_venta_credito_estado_pago  =   'PENDIENTE';
        $pedido->doc_venta_credito_monto_pagado =   0;
        $pedido->doc_venta_credito_saldo        =   $pedido->total_pagar;
        $pedido->save();
    }

    public function enlazarPedidoAdelanto(Pedido $pedido, CuentaCliente $cuenta)
    {
        $pedido->doc_venta_credito_estado_pago  =   $cuenta->estado;
        $pedido->doc_venta_credito_monto_pagado =   $cuenta->monto - $cuenta->saldo;
        $pedido->doc_venta_credito_saldo        =   $cuenta->saldo;
        $pedido->save();
    }

    public function actualizarPedido(array $datos, Pedido $pedido, object $montos)
    {

        $pedido->cliente_id             =   $datos['cliente'];
        $pedido->cliente_nombre         =   $datos['cliente_nombre'];
        $pedido->cliente_telefono       =   $datos['cliente_telefono'];

        $pedido->condicion_id           =   $datos['condicion_id'];

        $pedido->moneda                 =   1;

        $pedido->fecha_propuesta        =   $datos['fecha_propuesta'];

        $pedido->monto_embalaje         =   $montos->monto_embalaje;
        $pedido->monto_envio            =   $montos->monto_envio;
        $pedido->sub_total              =   $montos->monto_subtotal;
        $pedido->total_igv              =   $montos->monto_igv;
        $pedido->total                  =   $montos->monto_total;
        $pedido->total_pagar            =   $montos->monto_total_pagar;
        $pedido->monto_descuento        =   $montos->monto_descuento;
        $pedido->porcentaje_descuento   =   $montos->porcentaje_descuento;

        $pedido->telefono               =   $datos['telefono'];
        $pedido->origen_venta_id        =   $datos['origen_venta_id'];
        $pedido->origen_venta_nombre    =   $datos['origen_venta_nombre'];

        $pedido->save();
    }

    public function setEstadoPedido(int $id, string $estado)
    {
        $pedido         =   Pedido::findOrFail($id);
        $pedido->estado =   $estado;
        $pedido->save();
    }

    public function setEstadoPedidoDetalle(int $id, $estado)
    {
        DB::table('pedidos_detalles')
        ->where('pedido_id', $id)
        ->update([
            'estado' => $estado,
            'updated_at' => now()
        ]);
    }

    public function setClientePedido(array $datos):Pedido{
        $pedido_id  =   $datos['pedido_id'];
        $cliente_id =   $datos['cliente_cambio_id'];
        $cliente    =   Cliente::findOrFail($cliente_id);

        $pedido     =   Pedido::findOrFail($pedido_id);
        $pedido->cliente_id =   $cliente->id;
        $pedido->cliente_nombre =   $cliente->nombre;
        $pedido->save();

        $venta  =   Documento::findOrFail($pedido->doc_venta_credito_id);
        $venta->cliente_id  =   $cliente->id;
        $venta->cliente     =   $cliente->nombre;
        $venta->direccion_cliente   =   $cliente->direccion_cliente;
        $venta->documento_cliente   =   $cliente->documento;
        $venta->tipo_documento_cliente      =   $cliente->tipo_documento;
        $venta->save();

        $envio  =   EnvioVenta::where('documento_id',$venta->id)->first();
        if($envio){
            $envio->cliente_id  =   $cliente->id;
            $envio->cliente_nombre  =   $cliente->nombre;
            $envio->cliente_celular =   $cliente->telefono_movil;
            $envio->save();
        }

        return $pedido;
    }

    public function insertarPedidoDto(array $dto):Pedido{
        return  Pedido::create($dto);
    }

    public function insertarPedidoDetalleDto(array $dto){
        ReservasPedidoDetalle::insert($dto);
    }

    public function enlazarPedidoCotizacion(Pedido $pedido,Cotizacion $cotizacion){
        $pedido->cotizacion_id  =   $cotizacion->id;
        $pedido->save();

        $cotizacion->pedido_id  =   $pedido->id;
        $cotizacion->save();
    }
}
