<?php

namespace App\Http\Services\Ventas\NotaCredito;

use App\Almacenes\Color;
use App\Almacenes\Kardex;
use App\Almacenes\Producto;
use App\Almacenes\Talla;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\Nota;
use App\Ventas\NotaDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaCreditoRepository
{

    public function registrarNotaElectronica(array $datos): Nota
    {
        $nota = Nota::create($datos);

        return $nota;
    }

    public function registrarDetalleNotaElectronica(array $datos, Nota $nota,Documento $documento)
    {
        $productotabla  =   $datos['lst_detalle'];

        foreach ($productotabla as $producto) {

            $detalle    =   DB::select(
                'SELECT
                                                        cdd.id
                                                        FROM cotizacion_documento_detalles AS cdd
                                                        WHERE
                                                        cdd.documento_id = ?
                                                        AND cdd.almacen_id = ?
                                                        AND cdd.producto_id = ?
                                                        AND cdd.color_id = ?
                                                        AND cdd.talla_id = ?',
                [
                    $nota->documento_id,
                    $nota->almacen_id,
                    $producto->producto_id,
                    $producto->color_id,
                    $producto->talla_id
                ]
            );

            $nota_detalle                    = new NotaDetalle();
            $nota_detalle->nota_id           = $nota->id;
            $nota_detalle->detalle_id        = $detalle[0]->id;
            $nota_detalle->codProducto       = $producto->codigo_producto;
            $nota_detalle->unidad            = 'NIU';
            $nota_detalle->descripcion       = $producto->modelo_nombre . '-' . $producto->producto_nombre . '-' . $producto->color_nombre . '-' . $producto->talla_nombre;
            $nota_detalle->cantidad          = $producto->cantidad_devolver;
            $nota_detalle->mtoBaseIgv        = ($producto->precio_unitario / (1 + ($datos['igv'] / 100))) * $producto->cantidad_devolver;
            $nota_detalle->porcentajeIgv     = 18;
            $nota_detalle->igv               = ($producto->precio_unitario - ($producto->precio_unitario / (1 + ($datos['igv'] / 100)))) * $producto->cantidad_devolver;
            $nota_detalle->tipAfeIgv         = 10;
            $nota_detalle->totalImpuestos    = ($producto->precio_unitario - ($producto->precio_unitario / (1 + ($datos['igv'] / 100)))) * $producto->cantidad_devolver;
            $nota_detalle->mtoValorVenta     = ($producto->precio_unitario / (1 + ($datos['igv'] / 100))) * $producto->cantidad_devolver;
            $nota_detalle->mtoValorUnitario  = $producto->precio_unitario / (1 + ($datos['igv'] / 100));
            $nota_detalle->mtoPrecioUnitario = $producto->precio_unitario;
            $nota_detalle->almacen_id        = $nota->almacen_id;
            $nota_detalle->almacen_nombre    = $nota->almacen_nombre;
            $nota_detalle->producto_id       = $producto->producto_id;
            $nota_detalle->color_id          = $producto->color_id;
            $nota_detalle->talla_id          = $producto->talla_id;
            $nota_detalle->save();


            $_producto  =  Producto::findOrFail($producto->producto_id);

            //========= 01:FACTURA 03:BOLETA =======
            //======= PREGUNTAR SI EL DOC DE VENTA ESTÁ ASOCIADO A UN PEDIDO =====
            if ($documento->pedido_id && $_producto->tipo == 'PRODUCTO') {
                $this->operarItemsEnPedidos($documento,$producto,$nota);
            }

            //======== SI EL DOC DE VENTA NO ESTÁ ASOCIADO A UN PEDIDO ========
            if (!$documento->pedido_id && $_producto->tipo == 'PRODUCTO') {

                //===== AUMENTAR EL STOCK LOGICO Y FISICO ====
                DB::table('producto_color_tallas')
                    ->where('almacen_id', $documento->almacen_id)
                    ->where('producto_id', $producto->producto_id)
                    ->where('color_id', $producto->color_id)
                    ->where('talla_id', $producto->talla_id)
                    ->update([
                        'stock_logico'    => DB::raw('stock_logico + ' . $producto->cantidad_devolver),
                        'stock'           => DB::raw('stock + ' . $producto->cantidad_devolver)
                    ]);
            }

            if ($datos['cod_motivo'] == '01') {   //==== EN CASO DEVOLUCIÓN TOTAL ====
                $documento->sunat = '2';
                $documento->update();
            }

            //======== KARDEX =======
            if ($_producto->tipo == 'PRODUCTO') {
                $producto_color_talla   =   DB::table('producto_color_tallas')
                    ->where('almacen_id', $documento->almacen_id)
                    ->where('producto_id', $producto->producto_id)
                    ->where('color_id', $producto->color_id)
                    ->where('talla_id', $producto->talla_id)
                    ->first();


                $item_producto              =   Producto::find($producto->producto_id);
                $item_color                 =   Color::find($producto->color_id);
                $item_talla                 =   Talla::find($producto->talla_id);

                $kardex                     =   new Kardex();
                $kardex->sede_id            =   $nota->sede_id;
                $kardex->almacen_id         =   $nota->almacen_id;
                $kardex->producto_id        =   $producto->producto_id;
                $kardex->color_id           =   $producto->color_id;
                $kardex->talla_id           =   $producto->talla_id;
                $kardex->almacen_nombre     =   $nota->almacen_nombre;
                $kardex->producto_nombre    =   $item_producto->nombre;
                $kardex->color_nombre       =   $item_color->descripcion;
                $kardex->talla_nombre       =   $item_talla->descripcion;
                $kardex->cantidad           =   $producto->cantidad_devolver;
                $kardex->precio             =   $nota_detalle->mtoPrecioUnitario;
                $kardex->importe            =   $nota_detalle->mtoPrecioUnitario * $producto->cantidad_devolver;
                $kardex->accion             =   'INGRESO';
                $kardex->stock              =   $producto_color_talla->stock;
                $kardex->numero_doc         =   'NI-' . $nota->id;
                $kardex->documento_id       =   $nota->id;
                $kardex->registrador_id     =   $nota->user_id;
                $kardex->registrador_nombre =   Auth::user()->usuario;
                $kardex->fecha              =   $nota->fechaEmision;
                $kardex->descripcion        =   'DEVOLUCIÓN';
                $kardex->save();
            }


            $sumatoria           = NotaDetalle::where('detalle_id', $nota_detalle->detalle_id)->sum('cantidad');
            $detalle_venta       = Detalle::findOrFail($nota_detalle->detalle_id);
            if ($detalle_venta->cantidad == $sumatoria) {
                $detalle_venta->estado = 'ANULADO';
                $detalle_venta->update();
            }
        }
    }

    public function operarItemsEnPedidos(Documento $documento,$producto,Nota $nota)
    {
        //======== EN CASO SEA EL DOC VENTA DE LA FACTURACIÓN DE UN PEDIDO ========
        //========= DOCS DE VENTA DE TIPO FACTURACIÓN DE PEDIDO NO RESTAN STOCK !!=========
        if ($documento->tipo_doc_venta_pedido === "FACTURACION") {

            //======== OBTENER EL PRODUCTO DE LA NOTA ELECTRÓNICA EN EL DETALLE DEL PEDIDO ======
            $producto_en_pedido =   DB::select(
                'select
                                                pd.almacen_id,
                                                pd.producto_id,
                                                pd.color_id,
                                                pd.talla_id,
                                                pd.cantidad_atendida,
                                                pd.cantidad_pendiente
                                                from pedidos_detalles as pd
                                                where
                                                pd.pedido_id = ?
                                                AND pd.almacen_id = ?
                                                AND pd.producto_id = ?
                                                AND pd.color_id = ?
                                                AND pd.talla_id = ?',
                [
                    $documento->pedido_id,
                    $documento->almacen_id,
                    $producto->producto_id,
                    $producto->color_id,
                    $producto->talla_id
                ]
            );


            //====== COMPROBAR SI EL PRODUCTO ESTÁ PRESENTE EN EL DETALLE DEL PEDIDO =======
            if (count($producto_en_pedido) === 1) {

                $cantidad_reponer   =   0;

                //======= CASO I:  CANT DEVOLVER <= CANTIDAD PENDIENTE ======
                if ($producto->cantidad_devolver <= $producto_en_pedido[0]->cantidad_pendiente) {

                    //======== NO SE VA REPONER STOCK =======
                    $cantidad_reponer   =   0;

                    //======= LE BAJAMOS A LA CANTIDAD PENDIENTE =====
                    //====== INCREMENTAR LA CANTIDAD DEVUELTA Y LA CANTIDAD PENDIENTE DEVUELTA =======
                    DB::table('pedidos_detalles')
                        ->where('pedido_id', $documento->pedido_id)
                        ->where('almacen_id', $producto_en_pedido[0]->almacen_id)
                        ->where('producto_id', $producto_en_pedido[0]->producto_id)
                        ->where('color_id', $producto_en_pedido[0]->color_id)
                        ->where('talla_id', $producto_en_pedido[0]->talla_id)
                        ->update([
                            'cantidad_pendiente'                => DB::raw('cantidad_pendiente - ' . $producto->cantidad_devolver),
                            'cantidad_devuelta'                 => DB::raw('cantidad_devuelta + ' . $producto->cantidad_devolver),
                            'cantidad_pendiente_devuelta'       => DB::raw('cantidad_pendiente_devuelta + ' . $producto->cantidad_devolver),
                            'updated_at'                        => Carbon::now()
                        ]);
                }

                //======= CASO II: CANT DEVOLVER > CANTIDAD PENDIENTE ========
                if ($producto->cantidad_devolver >  $producto_en_pedido[0]->cantidad_pendiente) {

                    //====== BAJAMOS LA CANTIDAD PENDIENTE A 0 =====
                    $nueva_cantidad_pendiente   =   0;

                    //====== OBTENEMOS LO QUE FALTÓ BAJARLE A LA CANTIDAD PENDIENTE =====
                    $cantidad_falta_disminuir   =   $producto->cantidad_devolver - $producto_en_pedido[0]->cantidad_pendiente;

                    //======= FIJAMOS LA CANTIDAD A REPONER DE STOCK ======
                    $cantidad_reponer           =   $cantidad_falta_disminuir;


                    //======= REPONEMOS STOCK ======
                    DB::table('producto_color_tallas')
                        ->where('almacen_id', $producto_en_pedido[0]->almacen_id)
                        ->where('producto_id', $producto_en_pedido[0]->producto_id)
                        ->where('color_id', $producto_en_pedido[0]->color_id)
                        ->where('talla_id', $producto_en_pedido[0]->talla_id)
                        ->update([
                            'stock_logico'  => DB::raw('stock_logico + ' . $cantidad_reponer),
                            'stock'         => DB::raw('stock + ' . $cantidad_reponer)
                        ]);

                    //======== ACTUALIZAMOS DETALLE DEL PEDIDO ======
                    DB::table('pedidos_detalles')
                        ->where('pedido_id', $documento->pedido_id)
                        ->where('almacen_id', $producto_en_pedido[0]->almacen_id)
                        ->where('producto_id', $producto_en_pedido[0]->producto_id)
                        ->where('color_id', $producto_en_pedido[0]->color_id)
                        ->where('talla_id', $producto_en_pedido[0]->talla_id)
                        ->update([
                            'cantidad_repuesta'                 =>  DB::raw('cantidad_repuesta + ' . $cantidad_falta_disminuir),
                            'cantidad_atendida_devuelta'        =>  DB::raw('cantidad_atendida_devuelta + ' . $cantidad_falta_disminuir),
                            'cantidad_devuelta'                 =>  DB::raw('cantidad_devuelta + ' . $producto->cantidad_devolver),
                            'cantidad_pendiente_devuelta'       =>  DB::raw('cantidad_pendiente_devuelta + ' . $producto_en_pedido[0]->cantidad_pendiente),
                            'cantidad_pendiente'                =>  $nueva_cantidad_pendiente,
                            'updated_at'                        =>  Carbon::now()
                        ]);
                }
            }
        }


        //======== EN CASO SEA EL DOC VENTA DE LA ATENCIÓN DE UN PEDIDO =======
        if ($documento->tipo_doc_venta_pedido === "ATENCION") {

            //========== TRABAJAR CON PURA CANTIDAD ATENDIDA ======
            //======= DEBIDO A QUE EL PEDIDO PUEDE MODIFICARSE EN CASO SE REQUIERA BAJAR LAS CANTIDADES PENDIENTES ======

            //======== OBTENER EL PRODUCTO DE LA NOTA ELECTRÓNICA EN EL DETALLE DEL PEDIDO ======
            $producto_en_pedido =   DB::select(
                'select
                                                pd.almacen_id,
                                                pd.producto_id,
                                                pd.color_id,
                                                pd.talla_id,
                                                pd.cantidad_atendida,
                                                pd.cantidad_pendiente
                                                from pedidos_detalles as pd
                                                where
                                                pd.pedido_id = ?
                                                AND pd.almacen_id = ?
                                                AND pd.producto_id = ?
                                                AND pd.color_id = ?
                                                AND pd.talla_id = ?',
                [
                    $documento->pedido_id,
                    $documento->almacen_id,
                    $producto->producto_id,
                    $producto->color_id,
                    $producto->talla_id
                ]
            );


            //====== COMPROBAR SI EL PRODUCTO ESTÁ PRESENTE EN EL DETALLE DEL PEDIDO =======
            if (count($producto_en_pedido) === 1) {

                //======= REPONEMOS STOCK ======
                DB::table('producto_color_tallas')
                    ->where('almacen_id', $producto_en_pedido[0]->almacen_id)
                    ->where('producto_id', $producto_en_pedido[0]->producto_id)
                    ->where('color_id', $producto_en_pedido[0]->color_id)
                    ->where('talla_id', $producto_en_pedido[0]->talla_id)
                    ->update([
                        'stock_logico' => DB::raw('stock_logico + ' . $producto->cantidad_devolver),
                        'stock' => DB::raw('stock + ' . $producto->cantidad_devolver)
                    ]);

                //========== ACTUALIZANDO DETALLE DEL PEDIDO =======
                DB::table('pedidos_detalles')
                    ->where('pedido_id', $documento->pedido_id)
                    ->where('almacen_id', $producto_en_pedido[0]->almacen_id)
                    ->where('producto_id', $producto_en_pedido[0]->producto_id)
                    ->where('color_id', $producto_en_pedido[0]->color_id)
                    ->where('talla_id', $producto_en_pedido[0]->talla_id)
                    ->update([
                        'cantidad_repuesta'                 =>  DB::raw('cantidad_repuesta + ' . $producto->cantidad_devolver),
                        'cantidad_atendida_devuelta'        =>  DB::raw('cantidad_atendida_devuelta + ' . $producto->cantidad_devolver),
                        'cantidad_devuelta'                 =>  DB::raw('cantidad_devuelta + ' . $producto->cantidad_devolver),
                        'updated_at'                        =>  Carbon::now()
                    ]);
            }
        }

        //=========== GUARDAR EL PEDIDO ID EN LA NOTA ELECTRÓNICA PARA FÁCIL ACCESO ======
        $nota->pedido_id    =   $documento->pedido_id;
        $nota->update();
    }
}
