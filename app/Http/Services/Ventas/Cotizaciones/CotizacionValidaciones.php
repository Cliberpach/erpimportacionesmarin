<?php

namespace App\Http\Services\Ventas\Cotizaciones;

use App\Almacenes\Color;
use App\Almacenes\Producto;
use App\Almacenes\Talla;
use App\Classes\ValidatedDetail;
use App\Mantenimiento\Tabla\Detalle;
use App\Models\Reservas\Reservas\Pedido;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\Ventas\Cliente;
use App\Ventas\Documento\Documento;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CotizacionValidaciones
{

    public function validacionDetalle(array $lstCotizacion)
    {
        //======= REGISTRO DETALLE DE LA COTIZACIÓN =====
        foreach ($lstCotizacion as $item) {

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
        }
    }

    public function validacionConvertirAVentaCreate(int $id)
    {
        $documento = Documento::where('cotizacion_venta', $id)->where('estado', '!=', 'ANULADO')->first();
        if ($documento) {
            throw new Exception("'Esta cotizacion ya tiene un documento de venta generado.'");
        }
    }

    public function validacionStockCantidad($detalles)
    {
        $validaciones = [];
        //======== RECORRIENDO CADA PRODUCTO DEL DETALLE DE LA COTIZACIÓN ===========
        foreach ($detalles as $detalle) {

            //=========== OBTENIENDO STOCK LÓGICO DE UN PRODUCTO =============
            $productoExiste =   DB::select(
                'SELECT
                                stock_logico
                                from producto_color_tallas as pct
                                where
                                pct.almacen_id = ?
                                and pct.producto_id = ?
                                and pct.color_id = ?
                                and pct.talla_id = ?',
                [
                    $detalle->almacen_id,
                    $detalle->producto_id,
                    $detalle->color_id,
                    $detalle->talla_id
                ]
            );

            $item_producto_nombre   =   Producto::findOrFail($detalle->producto_id)->nombre;
            $item_color_nombre      =   Color::findOrFail($detalle->color_id)->descripcion;
            $item_talla_nombre      =   Talla::findOrFail($detalle->talla_id)->descripcion;

            //===== EN CASO EXISTA EL PRODUCTO COLOR TALLA =====
            if (count($productoExiste) > 0) {
                $stock_logico   =   $productoExiste[0]->stock_logico;
                if ($stock_logico < $detalle->cantidad) {
                    $registro                           = new ValidatedDetail();
                    $registro->setStockLogico($stock_logico);
                    $registro->setTipo('STOCK LOGICO INSUFICIENTE');
                } else {
                    $registro                           = new ValidatedDetail();
                    $registro->setStockLogico($stock_logico);
                    $registro->setTipo('STOCK LOGICO VÁLIDO');
                }
            } else {
                $registro                           = new ValidatedDetail();
                $registro->setStockLogico(null);
                $registro->setTipo('NO EXISTE EL PRODUCTO COLOR TALLA');
            }

            $registro->setAlmacenId($detalle->almacen_id);
            $registro->setProductoId($detalle->producto_id);
            $registro->setColorId($detalle->color_id);
            $registro->setTallaId($detalle->talla_id);
            $registro->setProductoNombre($item_producto_nombre);
            $registro->setColorNombre($item_color_nombre);
            $registro->setTallaNombre($item_talla_nombre);
            $registro->setCantidadSolicitada($detalle->cantidad);
            $registro->setPrecioUnitario($detalle->precio_unitario);
            $registro->setPrecioUnitarioNuevo($detalle->precio_unitario_nuevo);
            $registro->setPorcentajeDescuento($detalle->porcentaje_descuento);
            $validaciones[] =   $registro;
        }

        return $validaciones;
    }

    public function validacionConvertirADocVenta(array $datos)
    {
        //======= VALIDAR EXISTENCIA DEL PARÁMETRO COTIZACIÓN ID =======
        $cotizacion_id  =   $datos['cotizacion_id'];
        if (!$cotizacion_id) {
            throw new Exception("NO EXISTE COTIZACIÓN ID EN LA PETICIÓN!!!");
        }

        //======= VALIDAR EXISTENCIA DE COTIZACIÓN EN LA BD =======
        $cotizacion     =   Cotizacion::find($datos['cotizacion_id']);
        if (!$cotizacion) {
            throw new Exception("NO EXISTE LA COTIZACIÓN EN LA BD!!!");
        }

        //========== VALIDAR QUE LA COTIZACIÓN NO ESTÉ CONVERTIDA AÚN =========
        $documento  =   Documento::where('cotizacion_venta', $datos['cotizacion_id'])->first();
        if ($documento) {
            throw new Exception("LA COTIZACIÓN YA ESTÁ CONVERTIDA EN DOCUMENTO DE VENTA!!!");
        }

        //======== VALIDANDO QUE EL USUARIO QUE CREÓ LA COTIZACIÓN SEA EL MISMO QUE CONVIERTE ======
        if (Auth::user()->id != $cotizacion->registrador_id) {
            throw new Exception("SOLO EL USUARIO QUE CREÓ LA COTIZACIÓN PUEDE CONVERTIRLA A DOC VENTA!!!");
        }

        //======== VALIDAR TIPO COMPROBANTE CON TIPO DOCUMENTO DEL CLIENTE ==========
        $cliente            =   Cliente::findOrFail($datos['cliente']);
        $tipo_comprobante   =   Detalle::findOrFail($datos['tipo_comprobante']);

        if ($cliente->tipo_documento !== 'RUC' && $tipo_comprobante->id == 127) {
            throw new Exception("SE REQUIERE RUC PARA GENERAR FACTURA ELECTRÓNICA");
        }
        if ($cliente->tipo_documento !== 'DNI' && $tipo_comprobante->id == 128) {
            throw new Exception("SE REQUIERE DNI PARA GENERAR BOLETA ELECTRÓNICA");
        }
    }

    public function validacionConvertirAPedido(array $datos)
    {
        $cotizacion_id  =   $datos['cotizacion_id'];

        if (!$cotizacion_id) {
            throw new Exception("FALTA EL PARÁMETRO COTIZACIÓN ID EN LA PETICIÓN");
        }

        $cotizacion =   Cotizacion::findOrFail($cotizacion_id);
        if (!$cotizacion) {
            throw new Exception("LA COTIZACIÓN NO EXISTE");
        }
        if ($cotizacion->estado != 'VIGENTE') {
            throw new Exception("LA COTIZACIÓN NO ESTÁ VIGENTE");
        }

        if ($cotizacion->pedido_id) {
            throw new Exception("LA COTIZACIÓN YA FUE CONVERTIDA A RESERVA: ".'RE-'.$cotizacion->pedido_id);
        }
        if ($cotizacion->venta_id) {
            throw new Exception("LA COTIZACIÓN YA FUE CONVERTIDA A VENTA: ".$cotizacion->venta_serie.'-'.$cotizacion->venta_correlativo);
        }

    }
}
