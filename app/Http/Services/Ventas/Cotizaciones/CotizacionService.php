<?php

namespace App\Http\Services\Ventas\Cotizaciones;

use App\Almacenes\Talla;
use App\Http\Controllers\UtilidadesController;
use App\Http\Services\Pedidos\Pedidos\PedidoService;
use App\Http\Services\Ventas\Ventas\VentaService;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Sedes\Sede;
use App\Models\Reservas\Reservas\Pedido;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\Models\Ventas\Cotizaciones\CotizacionDetalle;
use App\Ventas\Cliente;
use App\Ventas\Documento\Documento;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class CotizacionService
{
    private CotizacionCalculos $s_calculos;
    private CotizacionDto $s_dto;
    private CotizacionRepository $s_repository;
    private CotizacionValidaciones $s_validaciones;
    private VentaService $s_venta;
    private PedidoService $s_pedido;

    public function __construct()
    {
        $this->s_calculos   =   new CotizacionCalculos();
        $this->s_dto        =   new CotizacionDto();
        $this->s_repository =   new CotizacionRepository();
        $this->s_validaciones   =   new CotizacionValidaciones();
        $this->s_venta          =   new VentaService();
        $this->s_pedido         =   new PedidoService();
    }

    public function store(array $datos): Cotizacion
    {
        $lstCotizacion      =   json_decode($datos['lstCotizacion']);
        $this->s_validaciones->validacionDetalle($lstCotizacion);

        $montos_cotizacion  =   json_decode($datos['montos_cotizacion']);

        $montos             =   $this->s_calculos->calcularMontos($montos_cotizacion, $lstCotizacion);
        $datos['montos']    =   $montos;

        $dto                =   $this->s_dto->prepararStoreDto($datos);
        $cotizacion         =   $this->s_repository->insertarCotizacion($dto);

        $dto_detalle        =   $this->s_dto->prepararDetalleDto($lstCotizacion, $cotizacion);

        $this->s_repository->insertarCotizacionDetalle($dto_detalle);

        return $cotizacion;
    }

    public function update(array $datos, int $id): Cotizacion
    {
        $lstCotizacion              =   json_decode($datos['lstCotizacion']);
        $montos_cotizacion          =   json_decode($datos['montos_cotizacion']);
        $datos['montos_cotizacion'] =   $montos_cotizacion;

        //======= CALCULANDO MONTOS ========
        $montos             =   $this->s_calculos->calcularMontos($montos_cotizacion, $lstCotizacion);
        $datos['montos']    =   $montos;

        //====== ACTUALIZAR ======
        $dto        =   $this->s_dto->prepararStoreDto($datos);
        $cotizacion =   $this->s_repository->actualizarCotizacion($id, $dto);

        //======== ELIMINAR DETALLE ANTERIOR ======
        $this->s_repository->eliminarDetalleCotizacion($id);
        $dto_detalle    =   $this->s_dto->prepararDetalleDto($lstCotizacion, $cotizacion);
        $this->s_repository->insertarCotizacionDetalle($dto_detalle);

        return $cotizacion;
    }

    public function getDatosConvertirAVenta($id):View
    {

        $this->s_validaciones->validacionConvertirAVentaCreate($id);

        //COLECCION DE ERRORES
        $errores    =   collect();
        $devolucion =   false;
        $tallas     =   Talla::all();
        $cotizacion =   Cotizacion::findOrFail($id);
        $detalles   =   CotizacionDetalle::where('cotizacion_id', $id)->where('tipo', 'PRODUCTO')
            ->with('producto', 'color', 'talla')->get();

        //================ VALIDANDO STOCKS_LOGICOS Y CANTIDADES SOLICITADAS =====================
        $validaciones = $this->s_validaciones->validacionStockCantidad($detalles);

        //========= OBTENER LOS DETALLES CON STOCK INSUFICIENTE ============
        $detallesWithStockInsuficiente = array_filter($validaciones, function ($validacion) {
            return $validacion->getTipo() == 'STOCK LOGICO INSUFICIENTE';
        });

        //========= OBTENER LOS DETALLES CON STOCK LÓGICO VÁLIDO O SUFICIENTE ============
        $detallesWithStockValido = array_filter($validaciones, function ($validacion) {
            return $validacion->getTipo() == 'STOCK LOGICO VÁLIDO';
        });

        //========= OBTENER LOS DETALLES QUE NO EXISTEN EN PRODUCTO COLOR TALLAS ============
        $detallesNotExists = array_filter($validaciones, function ($validacion) {
            return $validacion->getTipo() == 'NO EXISTE EL PRODUCTO COLOR TALLA';
        });

        $cantidadErrores =  count($detallesWithStockInsuficiente) + count($detallesNotExists);

        //============= SEPARAR STOCK_LOGICO CUANDO NO HAY ERRORES ===========
        if ($cantidadErrores == 0) {
            foreach ($validaciones as $itemValidado) {
                DB::table('producto_color_tallas')
                    ->where('almacen_id', $itemValidado->getAlmacenId())
                    ->where('producto_id', $itemValidado->getProductoId())
                    ->where('color_id', $itemValidado->getColorId())
                    ->where('talla_id', $itemValidado->getTallaId())
                    ->update([
                        'stock_logico' => DB::raw('stock_logico - ' . $itemValidado->getCantidadSolicitada())
                    ]);
            }
        }

        $detalleValidado = [];
        //======= CONVIRTIENDO A UN FORMATO QUE JSON PUEDA COMPRENDER =======
        foreach ($validaciones as $itemValidado) {
            $detalleArray = $itemValidado->toArray();

            $detalleValidado[] = $detalleArray;
        }

        $tipos_pago_envio   =   UtilidadesController::getTiposPagoEnvio();
        $tipos_envio        =   UtilidadesController::getTiposEnvio();
        $tipos_documento    =   UtilidadesController::getTiposDocumento();
        $condiciones        =   Condicion::where('estado','ACTIVO')->get();
        $cliente            =   Cliente::findOrFail($cotizacion->cliente_id);
        $tipos_venta        =   tipos_venta()->whereIn('parametro', ['F', 'B', 'N']);
        $sede               =   Sede::find($cotizacion->sede_id);

        return view('ventas.documentos.cotizacion_a_docventa.index', [
            'cotizacion'    =>  $cotizacion,
            // 'clientes'      =>  $clientes,
            'condiciones'   =>  $condiciones,
            'errores'       =>  $errores,
            // 'fecha_hoy'     =>  $fecha_hoy,
            // 'fullaccess'    =>  $fullaccess,
            // 'dolar'         =>  $dolar,
            'detalle'       =>  $detalleValidado,
            'tallas'        =>  $tallas,
            'cantidadErrores'   =>  $cantidadErrores,
            'departamentos'     =>  departamentos(),
            'tipos_envio'       =>  $tipos_envio,
            'tipos_pago_envio'  =>  $tipos_pago_envio,
            'tipos_documento'   =>  $tipos_documento,
            'cliente'           =>  $cliente,
            'tipos_venta'       =>  $tipos_venta,
            'tipo_clientes'     =>  tipo_clientes(),
            'sede'              =>  $sede
            // 'origenes_ventas'   =>  $origenes_ventas,
        ]);

    }

    public function convertirADocVenta(array $datos):Documento{
        $this->s_validaciones->validacionConvertirADocVenta($datos);
        $venta  =   $this->s_venta->storeVentaFromCotizacion($datos);
        $this->s_repository->enlazarCotizacionAVenta($datos['cotizacion_id'],$venta);
        return $venta;
    }

    public function convertirAPedido(array $datos):Pedido{

        $this->s_validaciones->validacionConvertirAPedido($datos);
        $this->s_pedido->storeFromCotizacion($datos);

        $pedido =   $this->s_pedido->storeFromCotizacion($datos);
        $this->s_repository->enlazarCotizacionAPedido($datos['cotizacion_id'],$pedido->id);

        //=======  CREAR TICKET =========
        

        return $pedido;
    }
}
