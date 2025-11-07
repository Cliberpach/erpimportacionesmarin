<?php

namespace App\Http\Services\Pedidos\Pedidos;

use App\Almacenes\Almacen;
use App\Http\Controllers\Ventas\DocumentoController;
use App\Http\Requests\Ventas\DocVenta\DocVentaStoreRequest;
use App\Http\Services\Almacen\ProductoColorTalla\ProductoColorTallaService;
use App\Http\Services\Cuentas\Cliente\CuentaService;
use App\Http\Services\Ventas\Cotizaciones\CotizacionService;
use App\Http\Services\Ventas\Despacho\DespachoService;
use App\Http\Services\Ventas\Ventas\VentaService;
use App\Mantenimiento\Empresa\Empresa;
use App\Models\Reservas\Reservas\Pedido;
use App\Models\Ventas\Cotizaciones\CotizacionDetalle;
use App\Ventas\Cliente;
use App\Ventas\CuentaCliente;
use App\Ventas\DetalleCuentaCliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\PedidoDetalle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PedidoService
{
    private CalculosService $s_calculos;
    private PedidoRepository $s_repository;
    private VentaService $s_venta;
    private CuentaService $s_cuenta;
    private DespachoService $s_despacho;
    private ProductoColorTallaService $s_producto_color_talla;
    private PedidoValidacionesService $s_pedido_validaciones;
    private PedidoDto $s_pedido_dto;

    public function __construct()
    {
        $this->s_calculos   =   new CalculosService();
        $this->s_repository =   new PedidoRepository();
        $this->s_venta      =   new VentaService();
        $this->s_cuenta     =   new CuentaService();
        $this->s_despacho   =   new DespachoService();
        $this->s_producto_color_talla   =   new ProductoColorTallaService();
        $this->s_pedido_validaciones    =   new PedidoValidacionesService();
        $this->s_pedido_dto             =   new PedidoDto();
    }

    public function store(array $datos): array
    {
        $datos  =   $this->s_pedido_validaciones->validacionStore($datos);
        $lstPedido          =   json_decode($datos['lstPedido']);
        $amountsPedido      =   json_decode($datos['amountsPedido']);

        //======= MANEJANDO MONTOS ========
        $montos =   $this->s_calculos->calcularMontos($lstPedido, $amountsPedido);

        //======== BUSCANDO NOMBRE DEL CLIENTE =====//
        $cliente    =   Cliente::findOrFail($datos['cliente']);

        //======== BUSCANDO NOMBRE DE LA EMPRESA =====//
        $empresa    =   Empresa::findOrFail(1);

        //======== REGISTRANDO PEDIDO =========
        $pedido =   $this->s_repository->insertarPedido($datos, $empresa, $cliente, $montos);

        //========== GRABAR DETALLE DEL PEDIDO ========
        $this->s_repository->insertarDetallePedido($lstPedido, $datos, $pedido);

        //======= CREAR EL TICKET A CREDITO CON EL MONTO TOTAL DEL PEDIDO ======
        $venta_credito  =   $this->generarVentaCredito($pedido, $datos);

        //======= OPERAR STOCKS =========
        $this->s_venta->operarVentaReserva($venta_credito->id);

        //======= REGISTRAR ADELANTO =========
        //======== EFECTIVO =========
        if ($datos['metodo_pago_1'] == 1 && $datos['cuenta_1'] && $datos['monto_1'] && $datos['fecha_operacion_1']) {
            $this->registrarAdelanto($datos, $pedido, $venta_credito);
        }

        //========== ELECTRÓNICO ===========
        if ($datos['metodo_pago_1'] != 1 && $datos['monto_1'] && $datos['nro_operacion_1'] && $datos['fecha_operacion_1']) {
            $this->registrarAdelanto($datos, $pedido, $venta_credito);
        }

        //======== ENLAZAR PEDIDO CON COTIZACIÓN ========
        if(isset($datos['cotizacion_id'])){
            $this->s_repository->enlazarPedidoCotizacion($pedido,$datos['cotizacion']);
        }

        return ['pedido' => $pedido, 'venta_credito' => $venta_credito];
    }

    public function registrarAdelanto(array $datos, Pedido $pedido, Documento $venta_credito)
    {
        if (floatval($datos['monto_1']) > floatval($pedido->total_pagar)) {
            throw new Exception("EL MONTO DE ADELANTO DEBE SER MENOR O IGUAL AL TOTAL DEL PEDIDO");
        }

        if (floatval($datos['monto_1']) == 0) {
            throw new Exception("EL MONTO DE ADELANTO DEBE SER MAYOR A 0");
        }

        $cuenta =   CuentaCliente::where('cotizacion_documento_id', $venta_credito->id)->first();

        $datos_adelanto =   [
            'pago'              =>  'A CUENTA',
            'cantidad'          =>  $datos['monto_1'],
            'importe_venta'     =>  $datos['monto_1'],
            'efectivo_venta'    =>  0,
            'modo_pago'         =>  $datos['metodo_pago_1'],
            'cuenta'            =>  $datos['cuenta_1'],
            'nro_operacion'     =>  $datos['nro_operacion_1'],
            'observacion'       =>  'PAGO DE PEDIDO ' . $pedido->id,
            'fecha'             =>  $datos['fecha_operacion_1'],
            'imagen'            =>  isset($datos['img_pago_1']) ? $datos['img_pago_1'] : null,
            'modo_despacho'     =>  $datos['modo_despacho'] ?? null
        ];

        $this->s_cuenta->pagar($datos_adelanto, $cuenta->id);
        $cuenta->refresh();

        $this->s_repository->enlazarPedidoAdelanto($pedido, $cuenta);
    }

    public function generarVentaCredito(Pedido $pedido, array $datos): Documento
    {
        $detalle_pedido     = PedidoDetalle::where('pedido_id', $pedido->id)->where('tipo', 'PRODUCTO')->get();
        $detalle_formateado = $this->formatearArrayDetalleObjetos($detalle_pedido);
        $productos          = json_encode($detalle_formateado);

        $datos_venta    =   [
            'condicion_id'              =>  "2",
            'tipo_venta'                =>  "129",
            'tipo_pago_id'              =>  null,
            'efectivo'                  =>  0,
            'importe'                   =>  0,
            'empresa_id'                =>  1,
            'observacion'               =>  'GENERADO A PARTIR DEL PEDIDO ' . $pedido->id,
            'igv'                       =>  18,
            'igv_check'                 =>  true,
            'productos_tabla'           =>  $productos,
            "monto_sub_total"           =>  $pedido->sub_total,
            "monto_embalaje"            =>  $pedido->monto_embalaje,
            "monto_envio"               =>  $pedido->monto_envio,
            "monto_total_igv"           =>  $pedido->total_igv,
            "monto_descuento"           =>  $pedido->monto_descuento,
            "monto_total"               =>  $pedido->total,
            "monto_total_pagar"         =>  $pedido->total_pagar,
            "sede_id"                   =>  $pedido->sede_id,
            "almacenSeleccionado"       =>  $pedido->almacen_id,
            'cliente_id'                =>  $pedido->cliente_id,

            'pedido_id'                 =>  $pedido->id,
            'ticket_credito'            =>  'SI',
            'data_envio'                =>  $datos['data_envio'],
            'modo'                      =>  $datos['modo_despacho'],
            'origen_venta'              =>  $datos['origen_venta']
        ];

        $venta_credito  =   $this->s_venta->registrar($datos_venta);
        $this->s_repository->enlazarPedidoVentaCredito($pedido, $venta_credito);
        return $venta_credito;
    }

    public function facturar(array $datos): object
    {
        //====== RECIBIENDO PEDIDO ID =====
        $pedido_id      =   $datos['pedido_id'];
        $pedido         =   Pedido::findOrFail($pedido_id);

        if (!$pedido) {
            throw new Exception('NO SE ENCONTRÓ EL PEDIDO EN LA BASE DE DATOS');
        }
        if ($pedido->estado !== 'PENDIENTE') {
            throw new Exception("NO PUEDE FACTURARSE EL PEDIDO, SU ESTADO ES: " . $pedido->estado);
        }

        //===== OBTENIENDO EL TIPO DOC DEL CLIENTE =======
        $cliente    =   DB::select('SELECT
                        c.tipo_documento
                        FROM clientes AS c
                        WHERE c.id = ?', [$pedido->cliente_id]);

        if (count($cliente) === 0 || count($cliente) > 1) {
            throw new Exception('NO SE ENCONTRÓ EL CLIENTE EN LA BASE DE DATOS');
        }

        $cliente_tipo_documento =   $cliente[0]->tipo_documento;
        $tipo_venta             =   $datos['comprobante'];

        if (($cliente_tipo_documento !== "RUC" && $cliente_tipo_documento !== "DNI") && ($tipo_venta == 127 || $tipo_venta == 128)) {
            throw new Exception("SE REQUIERE QUE EL CLIENTE TENGA RUC O DNI PARA FACTURAR CON FACTURA O BOLETA ELECTRÓNICA");
        }
        if ($cliente_tipo_documento === "RUC" && $tipo_venta == 128) {
            throw new Exception("EL CLIENTE TIENE RUC, NO PUEDE FACTURARSE CON BOLETA ELECTRÓNICA");
        }
        if ($cliente_tipo_documento === "DNI" && $tipo_venta == 127) {
            throw new Exception("EL CLIENTE TIENE RUC, NO PUEDE FACTURARSE CON FACTURA ELECTRÓNICA");
        }

        //======= OBTENIENDO DETALLE DEL PEDIDO ===========
        $detalle_pedido     = PedidoDetalle::where('pedido_id', $pedido_id)->get();
        $detalle_formateado = $this->formatearArrayDetalleObjetos($detalle_pedido);
        $productos          = json_encode($detalle_formateado);

        //======= AGREGANDO DATOS AL REQUEST =====
        $additionalData = [
            'empresa'                   =>  $pedido->empresa_id,
            'tipo_venta'                =>  $tipo_venta,
            'condicion_id'              =>  $pedido->condicion_id,
            'fecha_vencimiento_campo'   =>  Carbon::now(),
            'cliente_id'                =>  $pedido->cliente_id,
            'igv'                       =>  "18",
            "igv_check"                 =>  "on",
            "efectivo"                  =>  "0",
            "importe"                   =>  "0",
            "empresa_id"                =>  $pedido->empresa_id,
            "monto_sub_total"           =>  $pedido->sub_total,
            "monto_embalaje"            =>  $pedido->monto_embalaje,
            "monto_envio"               =>  $pedido->monto_envio,
            "monto_total_igv"           =>  $pedido->total_igv,
            "monto_descuento"           =>  $pedido->monto_descuento,
            "monto_total"               =>  $pedido->total,
            "monto_total_pagar"         =>  $pedido->total_pagar,
            "data_envio"                =>  null,
            "facturar"                  =>  'SI',
            "productos_tabla"           =>  $productos,
            "sede_id"                   =>  $pedido->sede_id,
            "almacenSeleccionado"       =>  $pedido->almacen_id,
            "pedido_id"                 =>  $pedido->id,
        ];

        $request_base = Request::create(
            route('pedidos.pedido.facturar-store'),
            'POST',
            $additionalData
        );

        //======== GENERANDO DOC VENTA ======
        $docVentaRequest        =   DocVentaStoreRequest::createFrom($request_base);

        $documentoController    =   new DocumentoController();
        $res                    =   $documentoController->store($docVentaRequest);
        $jsonResponse           =   $res->getData();

        //====== MANEJO DE RESPUESTA =========
        $success_store_doc      =   $jsonResponse->success;

        //======= ERROR AL CREAR DOC FACTURACIÓN =======
        if (!$success_store_doc) {
            throw new Exception($jsonResponse->message);
        }

        $doc_venta  =   DB::select(
            'SELECT
                            cd.id,
                            cd.serie,
                            cd.correlativo,
                            cd.total_pagar
                            FROM cotizacion_documento AS cd
                            WHERE cd.id = ?',
            [$jsonResponse->documento_id]
        )[0];

        //======== ACTUALIZANDO PEDIDO =======
        $pedido->facturado                                  =   'SI';
        $pedido->documento_venta_facturacion_id             =   $jsonResponse->documento_id;
        $pedido->documento_venta_facturacion_serie          =   $doc_venta->serie;
        $pedido->documento_venta_facturacion_correlativo    =   $doc_venta->correlativo;
        $pedido->monto_facturado                            =   $doc_venta->total_pagar;
        $pedido->saldo_facturado                            =   $doc_venta->total_pagar;
        $pedido->save();

        return $doc_venta;
    }

    public static function formatearArrayDetalleObjetos($detalles)
    {
        $detalleFormateado = [];
        $productosProcesados = [];
        foreach ($detalles as $detalle) {
            $cod   =   $detalle->producto_id . '-' . $detalle->color_id;
            if (!in_array($cod, $productosProcesados)) {
                $producto = [];
                //======== obteniendo todas las detalle talla de ese producto_color =================
                $producto_color_tallas = $detalles->filter(function ($detalleFiltro) use ($detalle) {
                    return $detalleFiltro->producto_id == $detalle->producto_id && $detalleFiltro->color_id == $detalle->color_id;
                });

                $producto['producto_codigo']        =   $detalle->producto_codigo;
                $producto['producto_id']            =   $detalle->producto_id;
                $producto['color_id']               =   $detalle->color_id;
                $producto['producto_nombre']        =   $detalle->producto_nombre;
                $producto['color_nombre']           =   $detalle->color_nombre;
                $producto['modelo_nombre']          =   $detalle->modelo_nombre;
                $producto['precio_unitario']        =   $detalle->precio_unitario;
                $producto['porcentaje_descuento']   =   $detalle->porcentaje_descuento;
                $producto['precio_unitario_nuevo']  =   $detalle->precio_unitario_nuevo;
                $producto['monto_descuento']        =   $detalle->monto_descuento;
                $producto['precio_venta']           =   $detalle->precio_unitario;
                $producto['precio_venta_nuevo']     =   $detalle->precio_unitario_nuevo;

                $tallas             =   [];
                $subtotal           =   0.0;
                $subtotal_with_desc =   0.0;
                $cantidadTotal = 0;
                foreach ($producto_color_tallas as $producto_color_talla) {
                    $talla = [];
                    $talla['talla_id']              =   $producto_color_talla->talla_id;


                    $talla['cantidad']              =   (int)$producto_color_talla->cantidad;
                    $subtotal                       +=  $talla['cantidad'] * $producto['precio_unitario_nuevo'];
                    $cantidadTotal                  +=  $talla['cantidad'];


                    $talla['talla_nombre']          =   $producto_color_talla->talla_nombre;

                    array_push($tallas, (object)$talla);
                }

                $producto['tallas']                 =   $tallas;
                $producto['subtotal']               =   $subtotal;
                $producto['cantidad_total']         =   $cantidadTotal;
                array_push($detalleFormateado, (object)$producto);
                $productosProcesados[] = $detalle->producto_id . '-' . $detalle->color_id;
            }
        }
        return $detalleFormateado;
    }

    public function generarDocumentoVenta(array $datos): int
    {

        $pedido                 =   Pedido::findOrFail($datos['pedido_id']);

        $request = new Request($datos);
        $request->merge([
            'sede_id'               => $pedido->sede_id,
            'almacenSeleccionado'   => $pedido->almacen_id,
        ]);

        if ($pedido->facturado === 'SI') {
            $this->actualizarSaldoFacturado($pedido, $datos);
            $additionalData = [
                'facturado'             =>  'SI',
                'generar_recibo_caja'   =>  'NO',
                'user_id'               =>  $pedido->user_id,
                'cliente_id'            =>  $pedido->cliente_id,
                'pedido_nro'            =>  $pedido->pedido_nro,
            ];
            $request->merge($additionalData);
        }

        //======= ACTUALIZANDO CANTIDAD ATENDIDA EN PEDIDO DETALLES ======
        $this->actualizarCantAtendida($datos, $pedido);

        //======== CAMBIAR ESTADO DEL PEDIDO ==========
        $this->actualizarEstadoPedido($pedido);

        //========== GENERAR DOC VENTA ===========
        $res_doc_atencion   =   $this->generarDocAtencion($request);

        //========= GENERAR DOCUMENTO VENTA DE CONSUMO EN CASO EL PEDIDO SE COMPLETÓ DE ATENDER =======
        // if ($pedido->estado === "FINALIZADO" && $pedido->facturado === "SI") {
        //     $this->generarDocConsumo($pedido);
        // }

        return $res_doc_atencion->documento_id;
    }

    public function actualizarCantAtendida(array $datos, Pedido $pedido)
    {
        $productosJSON  = $datos['productos_tabla'];
        $productos      = json_decode($productosJSON);

        foreach ($productos as $producto) {
            foreach ($producto->tallas as $talla) {

                DB::table('pedidos_detalles')
                    ->where('pedido_id', $pedido->id)
                    ->where('almacen_id', $pedido->almacen_id)
                    ->where('producto_id', $producto->producto_id)
                    ->where('color_id', $producto->color_id)
                    ->where('talla_id', $talla->talla_id)
                    ->update([
                        'cantidad_pendiente'    => DB::raw('cantidad_pendiente  - ' . $talla->cantidad),
                        'cantidad_atendida'     => DB::raw('cantidad_atendida   + ' . $talla->cantidad)
                    ]);
            }
        }
    }

    public function actualizarEstadoPedido(Pedido $pedido)
    {
        //======= CAMBIANDO ESTADO DEL PEDIDO =====
        //===== CANTIDAD DE ITEMS QUE TIENE EL PEDIDO ======
        $cant_items_pendientes_pedido       =   PedidoDetalle::where('pedido_id', $pedido->id)
            ->where('cantidad_pendiente', '>', 0)
            ->count('*');
        $cant_items_atendidos_pedido        =   PedidoDetalle::where('pedido_id', $pedido->id)
            ->where('cantidad_atendida', '>', 0)
            ->count('*');


        if ($cant_items_pendientes_pedido === 0) {
            $pedido->estado      =   "FINALIZADO";
        }

        if ($cant_items_pendientes_pedido > 0 && $cant_items_atendidos_pedido > 0) {
            $pedido->estado      =   "ATENDIENDO";
        }

        if ($cant_items_atendidos_pedido === 0) {
            $pedido->estado      =   "PENDIENTE";
        }

        $pedido->save();
    }

    public function actualizarSaldoFacturado(Pedido $pedido, array $datos)
    {
        //===== SI EL SALDO FACTURADO ES MAYOR O IGUAL AL MONTO DE LA ATENCIÓN =====
        if ($pedido->saldo_facturado >= $datos['monto_total_pagar']) {
            //====== NO GENERAR RECIBOS DE CAJA =======
            //====== DISMINUIR SALDO FACTURADO ========
            $pedido->saldo_facturado -=  $datos['monto_total_pagar'];
        } else {
            //====== DISMINUIR SALDO FACTURADO ========
            $pedido->saldo_facturado =  0;
        }
    }

    public function generarDocConsumo(Pedido $pedido)
    {
        $doc_anticipo           =   Documento::findOrFail($pedido->documento_venta_facturacion_id);

        //========= DEFINIR TIPO VENTA PARA EL DOC CONSUMO =========
        $tipo_venta  = $doc_anticipo->tipo_venta_id;

        if ($doc_anticipo->es_anticipo == '1') {
            $docs_antenciones   =   Documento::where('pedido_id', $pedido->id)
                ->where('tipo_doc_venta_pedido', 'ATENCION')
                ->get();

            $monto_sub_total    =   0;
            $monto_total_igv    =   0;
            $monto_total        =   0;
            $monto_embalaje     =   0;
            $monto_envio        =   0;
            $monto_total_pagar  =   0;
            $monto_descuento    =   0;

            $productos_tabla    =   [];

            foreach ($docs_antenciones as $doc_atencion) {
                $monto_sub_total    +=  $doc_atencion->sub_total;
                $monto_total_igv    +=  $doc_atencion->total_igv;
                $monto_total        +=  $doc_atencion->total;
                $monto_embalaje     +=  $doc_atencion->monto_embalaje;
                $monto_envio        +=  $doc_atencion->monto_envio;
                $monto_total_pagar  +=  $doc_atencion->total_pagar;
                $monto_descuento    +=  $doc_atencion->monto_descuento;

                $detalles   =   Detalle::where('documento_id', $doc_atencion->id)->get();

                foreach ($detalles as $d_item) {
                    $item   =   (object)[
                        'producto_id'           =>  $d_item->producto_id,
                        'color_id'              =>  $d_item->color_id,
                        'talla_id'              =>  $d_item->talla_id,
                        'producto_nombre'       =>  $d_item->nombre_producto,
                        'color_nombre'          =>  $d_item->nombre_color,
                        'talla_nombre'          =>  $d_item->nombre_talla,
                        'cantidad'              =>  $d_item->cantidad,
                        'precio_venta'          =>  $d_item->precio_unitario,
                        'monto_descuento'       =>  $d_item->monto_descuento,
                        'porcentaje_descuento'  =>  $d_item->porcentaje_descuento,
                        'precio_venta_nuevo'    =>  $d_item->precio_unitario_nuevo,
                        'subtotal_nuevo'        =>  $d_item->importe_nuevo,
                        'subtotal'              =>  $d_item->importe,
                    ];
                    $productos_tabla[] = $item;
                }
            }

            $productos_tabla = $this->agruparProductosConTallas($productos_tabla);

            //=========== ACTUALIZAR SALDO DEL ANTICIPO ==========
            $saldo_anticipo         =   (float)$doc_anticipo->saldo_anticipo;
            $monto_consumido        =   0;

            if ($saldo_anticipo >= $monto_total_pagar) {
                $monto_consumido    =   $monto_total_pagar;
                $saldo_anticipo     -=  $monto_total_pagar;
            } else {
                $monto_consumido    =   $saldo_anticipo;
                $saldo_anticipo     =  0;
            }

            $doc_anticipo->saldo_anticipo   =   $saldo_anticipo;
            $doc_anticipo->update();

            $datos_consumo  =   [
                'monto_sub_total'           =>  $monto_sub_total,
                'monto_embalaje'            =>  $monto_embalaje,
                'monto_envio'               =>  $monto_envio,
                'monto_total_igv'           =>  $monto_total_igv,
                'monto_descuento'           =>  $monto_descuento,
                'monto_total'               =>  $monto_total,
                'monto_total_pagar'         =>  $monto_total_pagar,
                'productos_tabla'           =>  json_encode($productos_tabla),
                'sede_id'                   =>  $pedido->sede_id,
                'almacenSeleccionado'       =>  $pedido->almacen_id,
                'tipo_doc_venta_pedido'     =>  'CONSUMO',
                'modo'                      =>  'CONSUMO',
                'tipo_venta'                =>  $tipo_venta,
                'cliente_id'                =>  $pedido->cliente_id,
                'condicion_id'              =>  "1-CONTADO",
                'pedido_id'                 =>  $pedido->id,

                'anticipo_consumido_id'     =>  $doc_anticipo->id,
                'anticipo_monto_consumido'  =>  $monto_consumido,
                'doc_anticipo_serie'        =>  $doc_anticipo->serie,
                'doc_anticipo_correlativo'  =>  $doc_anticipo->correlativo
            ];

            $request_consumo        =   new Request($datos_consumo);
            $request_venta_consumo  =   DocVentaStoreRequest::createFrom($request_consumo);

            $documentoController    =   new DocumentoController();
            $res_consumo            =   $documentoController->store($request_venta_consumo);
            $res_json_consumo       =   $res_consumo->getData();
            if (!$res_json_consumo->success) {
                throw new Exception($res_json_consumo->message . ' en la línea ' . $res_json_consumo->line . ' del archivo ' . $res_json_consumo->file);
            }
        }
    }

    public function generarDocAtencion(Request $request)
    {
        $docVentaRequest        =   DocVentaStoreRequest::createFrom($request);
        $documentoController    =   new DocumentoController();
        $res                    =   $documentoController->store($docVentaRequest);
        $jsonResponse           =   $res->getData();

        if (!$jsonResponse->success) {
            throw new Exception($jsonResponse->message . ' en la línea ' . $jsonResponse->line . ' del archivo ' . $jsonResponse->file);
        }
        return $jsonResponse;
    }

    function agruparProductosConTallas(array $items)
    {
        $resultado = [];
        $indice = 0;

        // Usamos una clave temporal para agrupar internamente por producto_id y color_id
        $agrupados = [];

        foreach ($items as $item) {
            $clave = $item->producto_id . '_' . $item->color_id;

            if (!isset($agrupados[$clave])) {
                $agrupados[$clave] = [
                    'producto_id'           => $item->producto_id,
                    'color_id'              => $item->color_id,
                    'producto_nombre'       => $item->producto_nombre,
                    'color_nombre'          => $item->color_nombre,
                    'precio_venta'          => $item->precio_venta,
                    'monto_descuento'       => $item->monto_descuento,
                    'porcentaje_descuento'  => $item->porcentaje_descuento,
                    'precio_venta_nuevo'    => $item->precio_venta_nuevo,
                    'subtotal_nuevo'        => 0,
                    'tallas'                => [],
                    'subtotal'              => 0,
                ];
            }

            $agrupados[$clave]['tallas'][] = [
                'talla_id'      => $item->talla_id,
                'talla_nombre'  => $item->talla_nombre,
                'cantidad'      => $item->cantidad,
            ];

            $agrupados[$clave]['subtotal_nuevo'] += (float) $item->subtotal_nuevo;
            $agrupados[$clave]['subtotal']       += (float) $item->subtotal;
        }

        // Reindexamos numéricamente
        foreach ($agrupados as $itemAgrupado) {
            $resultado[$indice++] = $itemAgrupado;
        }

        return $resultado;
    }

    public function update_old(array $datos, int $id): Pedido
    {
        $pedido         =   Pedido::findOrFail($id);

        if ($pedido->estado === 'ATENDIENDO' && isset($datos['almacen'])) {
            throw new Exception("LOS PEDIDOS CON ESTADO ATENDIENDO NO PUEDEN CAMBIARSE DE ALMACÉN");
        }

        $productos      =   json_decode($datos['lstPedido']);
        $amountsPedido  =   json_decode($datos['amountsPedido']);


        $lstProductos   =   [];

        //======== REFORMATEANDO LST PRODUCTOS =======
        foreach ($productos as $producto) {
            foreach ($producto->tallas as $talla) {
                $producto   =   (object)[
                    'producto_id' => $producto->producto_id,
                    'color_id' => $producto->color_id,
                    'talla_id' => $talla->talla_id,
                    'producto_nombre' => $producto->producto_nombre,
                    'color_nombre' => $producto->color_nombre,
                    'talla_nombre' => $talla->talla_nombre,
                    'cantidad' => $talla->cantidad
                ];
                $lstProductos[] =   $producto;
            }
        }

        //======== VALIDAR LISTADO DE PRODUCTOS ========
        $requestValidacion = new Request([
            'lstProductos'  => json_encode($lstProductos),
            'pedido_id'     => $id
        ]);
        $resValidacion      =   $this->validarCantidadAtendida($requestValidacion);
        $resValidacionData  =   $resValidacion->getData();

        if (!$resValidacionData->success) {
            throw new Exception($resValidacionData->message);
        }

        //======= MANEJANDO MONTOS ========
        $montos =   $this->s_calculos->calcularMontos($productos, $amountsPedido);

        //======== ACTUALIZAR PEDIDO =========
        $pedido                 = Pedido::find($id);
        $pedido->cliente_id     = $datos['cliente'];


        //======== BUSCANDO NOMBRE DEL CLIENTE =====//
        $cliente    =   DB::select('SELECT
                            c.id,c.nombre,c.telefono_movil
                            from clientes as c
                            where c.id = ?', [$datos['cliente']]);

        $pedido->cliente_nombre     =   $cliente[0]->nombre;
        $pedido->cliente_telefono   =   $cliente[0]->telefono_movil;

        $pedido->condicion_id       =   $datos['condicion_id'];

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

        $pedido->update();


        //======= ELIMINANDO DETALLE ANTERIOR, SIEMPRE Y CUANDO NO SE HAYA ATENDIDO AÚN ========
        if (count($productos) > 0) {
            PedidoDetalle::where('pedido_id', $id)
                ->where('cantidad_atendida', 0)
                ->delete();
        }

        //========== GRABAR DETALLE DEL PEDIDO ========
        foreach ($productos as $producto) {
            foreach ($producto->tallas as  $talla) {

                //===== CALCULANDO MONTOS PARA EL DETALLE =====
                $importe        =   floatval($talla->cantidad) * floatval($producto->precio_venta);
                $precio_venta   =   $producto->porcentaje_descuento == 0 ? $producto->precio_venta : $producto->precio_venta_nuevo;

                //======= BUSCANDO SI EXISTE EL PRODUCTO EN EL DETALLE DEL PEDIDO =====
                $producto_existe    =   DB::select(
                    'SELECT pd.producto_id,pd.color_id,pd.talla_id,pd.cantidad_atendida
                                            FROM pedidos_detalles AS pd
                                            WHERE pd.pedido_id = ? AND
                                            pd.producto_id = ? AND
                                            pd.color_id = ? AND
                                            pd.talla_id = ?',
                    [
                        $id,
                        $producto->producto_id,
                        $producto->color_id,
                        $talla->talla_id
                    ]
                );


                //========== EN CASO EL PRODUCTO YA EXISTA EN EL DETALLE =======
                if (count($producto_existe) === 1) {

                    //====== PREGUNTANDO SI TIENE CANTIDAD ATENDIDA ======
                    if ($producto_existe[0]->cantidad_atendida > 0) {

                        //======= LA NUEVA CANTIDAD DEBE SER MAYOR O IGUAL A LA CANTIDAD ATENDIDA =======
                        if ($talla->cantidad >= $producto_existe[0]->cantidad_atendida) {

                            //============ ACTUALIZAR PRODUCTO EN LA BD ========
                            DB::table('pedidos_detalles')
                                ->where('pedido_id', $id)
                                ->where('producto_id', $producto->producto_id)
                                ->where('color_id', $producto->color_id)
                                ->where('talla_id', $talla->talla_id)
                                ->update([
                                    'cantidad'                  => $talla->cantidad,
                                    'cantidad_pendiente'        => $talla->cantidad - $producto_existe[0]->cantidad_atendida,
                                    'precio_unitario'           => $producto->precio_venta,
                                    'importe'                   => $importe,
                                    'porcentaje_descuento'      => floatval($producto->porcentaje_descuento),
                                    'precio_unitario_nuevo'     => floatval($precio_venta),
                                    'importe_nuevo'             => floatval($precio_venta) * floatval($talla->cantidad),
                                    'monto_descuento'           => floatval($importe) * floatval($producto->porcentaje_descuento) / 100,
                                ]);
                        } else {
                            throw new Exception($producto->producto_nombre . '-' . $producto->color_nombre . '-' . $talla->talla_nombre .
                                ', LA CANTIDAD NUEVA (' . $talla->cantidad . ') DEBE SER MAYOR O IGUAL A LA CANTIDAD ATENDIDA' . '(' . $producto_existe[0]->cantidad_atendida . ')');
                        }
                    }
                }

                //========== EN CASO EL PRODUCTO SEA NUEVO EN EL DETALLE ========
                if (count($producto_existe) === 0) {
                    $pedido_detalle                         =   new PedidoDetalle();
                    $pedido_detalle->almacen_id             =   $pedido->almacen_id;
                    $pedido_detalle->pedido_id              =   $pedido->id;
                    $pedido_detalle->producto_id            =   $producto->producto_id;
                    $pedido_detalle->color_id               =   $producto->color_id;
                    $pedido_detalle->talla_id               =   $talla->talla_id;
                    $pedido_detalle->producto_codigo        =   $producto->producto_codigo;
                    $pedido_detalle->producto_nombre        =   $producto->producto_nombre;
                    $pedido_detalle->color_nombre           =   $producto->color_nombre;
                    $pedido_detalle->talla_nombre           =   $talla->talla_nombre;
                    $pedido_detalle->modelo_nombre          =   $producto->modelo_nombre;
                    $pedido_detalle->cantidad               =   $talla->cantidad;
                    $pedido_detalle->cantidad_atendida      =   0;
                    $pedido_detalle->cantidad_pendiente     =   $talla->cantidad;
                    $pedido_detalle->precio_unitario        =   $producto->precio_venta;
                    $pedido_detalle->importe                =   $importe;
                    $pedido_detalle->porcentaje_descuento   =   floatval($producto->porcentaje_descuento);
                    $pedido_detalle->precio_unitario_nuevo  =   floatval($precio_venta);
                    $pedido_detalle->importe_nuevo          =   floatval($precio_venta) * floatval($talla->cantidad);
                    $pedido_detalle->monto_descuento        =   floatval($importe) * floatval($producto->porcentaje_descuento) / 100;
                    $pedido_detalle->save();
                }
            }
        }


        $venta          =   Documento::where('pedido_id', $id)->first();
        $data_envio     =   $datos['data_envio'];

        //===== SI LLEGARON DATOS DE ENVÍO ========
        if ($data_envio) {

            //======= ANALIZAR SI EL DOC YA TENÍA ENVIO ANTES ========
            $envio_previo = EnvioVenta::where('documento_id', $venta->id)
                ->whereNotIn('estado', ['EMBALADO', 'DESPACHADO', 'RESERVADO'])
                ->first();

            $data_envio_formateado                  =   (array)json_decode($data_envio);
            $data_envio_formateado['documento_id']  =   $venta->id;
            $data_envio_formateado['destinatario']  =   (array)$data_envio_formateado['destinatario'];

            if (!$envio_previo) {
                $this->s_despacho->store($data_envio_formateado);
            } else {
                $this->s_despacho->update($data_envio_formateado);
            }
        }

        return $pedido;
    }

    public function update(array $datos, int $id): Pedido
    {
        //========== VALIDACIÓN =========
        $datos      =   $this->s_pedido_validaciones->validacionUpdate($datos, $id);
        $productos  =   $datos['productos'];
        $pedido     =   $datos['pedido'];

        //======= MANEJAR MONTOS ========
        $amountsPedido              =   json_decode($datos['amountsPedido']);
        $montos                     =   $this->s_calculos->calcularMontos($productos, $amountsPedido);
        $this->s_pedido_validaciones->validacionPedidoCuenta($montos->monto_total_pagar, $pedido->doc_venta_credito_id);
        $this->operarPedidoCuenta($montos->monto_total_pagar, $pedido);

        //============ PREPARAR DATOS =========
        $cliente                            =   Cliente::findOrFail($datos['cliente']);
        $datos['cliente_nombre']            =   $cliente->nombre;
        $datos['cliente_telefono']          =   $cliente->telefono_movil;
        $datos['cliente_tipo_documento']    =   $cliente->tipo_documento;
        $datos['cliente_documento']         =   $cliente->documento;
        $datos['cliente_direccion']         =   $cliente->direccion;
        $detalle_previo                     =   PedidoDetalle::where('pedido_id', $id)->where('tipo', 'PRODUCTO')->get();

        //======== OPERAR DETALLE PEDIDO,DETALLE TICKET =========
        $detalle_analizado          =   $this->analizarDetallePedido($productos, $detalle_previo, $datos['almacen'], $id);
        $this->operarStocksDetalle($detalle_analizado, $pedido->doc_venta_credito_id);

        //========== ACTUALIZAR PEDIDO MAESTRO ========
        $this->s_repository->actualizarPedido($datos, $pedido, $montos);

        //========= ACTUALIZAR TICKET MAESTRO =========
        $venta_credito  =   $this->actualizarVentaMaestro($pedido, $datos);

        //========== OPERAR EMBALAJE ENVÍO =======
        $this->s_repository->operarServiciosPedidoDetalle($pedido);

        //========= OPERAR DESPACHO ==========
        $this->operarDespacho($datos, $pedido);

        //======= REGISTRAR ADELANTO =========
        $cuenta_cliente =   CuentaCliente::where('cotizacion_documento_id', $venta_credito->id)->first();
        $cant_pagos     =   DetalleCuentaCliente::where('cuenta_cliente_id', $cuenta_cliente->id)->count();
        if ($cant_pagos === 0) {

            //======== EFECTIVO =========
            if ($datos['metodo_pago_1'] == 1 && $datos['cuenta_1'] && $datos['monto_1'] && $datos['fecha_operacion_1']) {
                $this->registrarAdelanto($datos, $pedido, $venta_credito);
            }

            //========== ELECTRÓNICO ===========
            if ($datos['metodo_pago_1'] != 1 && $datos['monto_1'] && $datos['nro_operacion_1'] && $datos['fecha_operacion_1']) {
                $this->registrarAdelanto($datos, $pedido, $venta_credito);
            }
        }

        $this->operarEmbalaje($pedido);

        return $pedido;
    }

    public function operarEmbalaje(Pedido $pedido)
    {
        if ($pedido->doc_venta_credito_estado_pago === 'PAGADO') {

            //========== ANALIZAR SI EL DETALLE ESTÁ SEPARADO POR COMPLETO =========
            $cant_no_separados    =   PedidoDetalle::where('estado', '<>', 'SEPARADO')->where('tipo', '=', 'PRODUCTO')->where('pedido_id', $pedido->id)->count();

            if ($cant_no_separados == 0) {

                $envio_venta            =   EnvioVenta::where('documento_id', $pedido->doc_venta_credito_id)->first();

                if ($envio_venta) {
                    $envio_venta->estado    =   'PENDIENTE';
                    $envio_venta->modo      =   'VENTA';
                    $envio_venta->update();

                    $pedido->estado =   'TRANSITO';
                    $pedido->update();
                }
            }
        }
    }

    public function actualizarVentaMaestro(Pedido $pedido, array $datos): Documento
    {
        $mtoOperGravadasSunat   =   (($pedido->sub_total + $pedido->monto_embalaje + $pedido->monto_envio) / 1.18);
        $mtoIgvSunat            =   $mtoOperGravadasSunat * 0.18;
        $totalImpuestosSunat    =   $mtoIgvSunat;
        $valorVentaSunat        =   $mtoOperGravadasSunat;
        $subTotalSunat          =   $mtoOperGravadasSunat + $mtoIgvSunat;
        $mtoImpVentaSunat       =   $subTotalSunat;

        $almacen    =   Almacen::findOrFail($pedido->almacen_id);

        $venta                          =   Documento::findOrFail($pedido->doc_venta_credito_id);
        $venta->cliente_id              =   $pedido->cliente_id;
        $venta->cliente                 =   $datos['cliente_nombre'];
        $venta->tipo_documento_cliente  =   $datos['cliente_tipo_documento'];
        $venta->documento_cliente       =   $datos['cliente_documento'];
        $venta->direccion_cliente       =   $datos['cliente_direccion'];
        $venta->sub_total               =   $pedido->sub_total;
        $venta->monto_embalaje          =   $pedido->monto_embalaje;
        $venta->monto_envio             =   $pedido->monto_envio;
        $venta->total                   =   $pedido->total;
        $venta->total_igv               =   $pedido->total_igv;
        $venta->total_pagar             =   $pedido->total_pagar;
        $venta->mto_oper_gravadas_sunat =   $mtoOperGravadasSunat;
        $venta->mto_igv_sunat           =   $mtoIgvSunat;
        $venta->total_impuestos_sunat   =   $totalImpuestosSunat;
        $venta->valor_venta_sunat       =   $valorVentaSunat;
        $venta->sub_total_sunat         =   $subTotalSunat;
        $venta->mto_imp_venta_sunat     =   $mtoImpVentaSunat;
        $venta->monto_descuento         =   $pedido->monto_descuento;
        $venta->porcentaje_descuento    =   $pedido->porcentaje_descuento;
        $venta->almacen_id              =   $pedido->almacen_id;
        $venta->almacen_nombre          =   $almacen->descripcion;

        $venta->telefono                =   $datos['telefono'];
        $venta->origen_venta_id         =   $pedido->origen_venta_id;
        $venta->origen_venta_nombre     =   $pedido->origen_venta_nombre;
        $venta->update();

        return $venta;
    }

    public function operarDespacho($datos, Pedido $pedido)
    {
        $data_envio =   $datos['data_envio'];
        if ($data_envio) {
            $envio_previo   =   EnvioVenta::where('documento_id', $pedido->doc_venta_credito_id)->first();

            $data_envio_formateado                  =   (array)json_decode($data_envio);
            $data_envio_formateado['documento_id']  =   $pedido->doc_venta_credito_id;
            $data_envio_formateado['destinatario']  =   (array)$data_envio_formateado['destinatario'];
            $data_envio_formateado['modo']          =   'RESERVA';

            //======= SI TENÍA ENVIO PREVIO CON ESTADO PENDIENTE, ACTUALIZAR =========
            if ($envio_previo && $envio_previo->estado === 'PENDIENTE') {
                $this->s_despacho->update($data_envio_formateado);
            }

            //======== SI NO TENÍA ENVÍO PREVIO, REGISTRAR ========
            if (!$envio_previo) {
                $this->s_despacho->store($data_envio_formateado);
            }
        }
    }

    public function analizarDetallePedido(array $arrayProductos, $detalle_previo, int $almacen_id, int $pedido_id): object
    {
        $nuevos = collect($arrayProductos);

        $nuevosKeys = $nuevos->flatMap(function ($p) use ($almacen_id) {
            return collect($p->tallas)->map(function ($t) use ($p, $almacen_id) {
                return $almacen_id . '-' . $p->producto_id . '-' . $p->color_id . '-' . $t->talla_id;
            });
        });

        $previosKeys = $detalle_previo->map(fn($d) => $d->almacen_id . '-' . $d->producto_id . '-' . $d->color_id . '-' . $d->talla_id);

        // ---------------------------
        // NUEVOS
        // ---------------------------
        $productosNuevos = $nuevos->flatMap(function ($p) use ($previosKeys, $almacen_id, $pedido_id) {
            return collect($p->tallas)->filter(function ($t) use ($p, $previosKeys, $almacen_id) {
                $key =  $almacen_id . '-' . $p->producto_id . '-' . $p->color_id . '-' . $t->talla_id;
                return !$previosKeys->contains($key);
            })->map(function ($t) use ($p, $almacen_id, $pedido_id) {
                return (object)[
                    'pedido_id'           => $pedido_id,
                    'almacen_id'          => $almacen_id,
                    'producto_id'         => $p->producto_id,
                    'producto_nombre'     => $p->producto_nombre,
                    'producto_codigo'     => $p->producto_codigo,
                    'modelo_nombre'       => $p->modelo_nombre,
                    'color_id'            => $p->color_id,
                    'color_nombre'        => $p->color_nombre,
                    'precio_venta'        => (float) $p->precio_venta,
                    'precio_venta_nuevo'  => (float) $p->precio_venta_nuevo,
                    'subtotal'            => (float) $p->subtotal,
                    'subtotal_nuevo'      => (float) $p->subtotal_nuevo,
                    'monto_descuento'     => (float) $p->monto_descuento,
                    'porcentaje_descuento' => (float) $p->porcentaje_descuento,
                    'talla_id'            => $t->talla_id,
                    'talla_nombre'        => $t->talla_nombre,
                    'cantidad'            => (float) $t->cantidad,
                ];
            });
        })->values()->toArray();

        // ---------------------------
        // QUITADOS
        // ---------------------------
        $productosQuitados = $detalle_previo->filter(function ($d) use ($nuevosKeys) {
            $key = $d->almacen_id . '-' . $d->producto_id . '-' . $d->color_id . '-' . $d->talla_id;
            return !$nuevosKeys->contains($key);
        })->map(function ($d) use ($pedido_id) {
            return (object)[
                'pedido_id'         => $pedido_id,
                'almacen_id'        => $d->almacen_id,
                'producto_id'       => $d->producto_id,
                'color_id'          => $d->color_id,
                'talla_id'          => $d->talla_id,
                'producto_nombre'   => $d->producto_nombre,
                'color_nombre'      => $d->color_nombre,
                'talla_nombre'      => $d->talla_nombre,
                'cantidad'          => (float) $d->cantidad,
                'estado'            => $d->estado,
                'precio_unitario'   => (float) $d->precio_unitario,
                'importe'           => (float) $d->importe,
                'porcentaje_descuento'  => (float) $d->porcentaje_descuento,
                'precio_unitario_nuevo' => (float) $d->precio_unitario_nuevo,
                'importe_nuevo'         => (float) $d->importe_nuevo,
                'monto_descuento'       => (float) $d->monto_descuento,
            ];
        })->values()->toArray();

        // ---------------------------
        // MANTENIDOS
        // ---------------------------
        $productosMantenidos = $detalle_previo->map(function ($d) use ($nuevos, $pedido_id, $almacen_id) {
            $nuevo = $nuevos->first(function ($p) use ($d, $almacen_id) {
                return collect($p->tallas)->contains(function ($t) use ($p, $d, $almacen_id) {
                    return
                        $almacen_id == $d->almacen_id
                        && $p->producto_id == $d->producto_id
                        && $p->color_id == $d->color_id
                        && $t->talla_id == $d->talla_id;
                });
            });

            if ($nuevo) {
                $tallaNueva = collect($nuevo->tallas)->firstWhere('talla_id', $d->talla_id);
                return (object)[
                    'pedido_id'         => $pedido_id,
                    'almacen_id'        => $almacen_id,
                    'producto_id'       => $d->producto_id,
                    'color_id'          => $d->color_id,
                    'talla_id'          => $d->talla_id,
                    'producto_nombre'   => $d->producto_nombre,
                    'color_nombre'      => $d->color_nombre,
                    'talla_nombre'      => $d->talla_nombre,
                    'cantidad_anterior' => (float) $d->cantidad,
                    'cantidad_nueva'    => (float) $tallaNueva->cantidad,
                    'cantidad'          => (float)$tallaNueva->cantidad,
                    'estado'            => $d->estado,

                    'precio_venta'        => (float) $nuevo->precio_venta,
                    'precio_venta_nuevo'  => (float) $nuevo->precio_venta_nuevo,
                    'subtotal'            => (float) $nuevo->subtotal,
                    'subtotal_nuevo'      => (float) $nuevo->subtotal_nuevo,
                    'monto_descuento'     => (float) $nuevo->monto_descuento,
                    'porcentaje_descuento' => (float) $nuevo->porcentaje_descuento,

                ];
            }

            return null;
        })->filter()->values()->toArray();

        $res    =   (object)[
            'nuevos'     => $productosNuevos,
            'quitados'   => $productosQuitados,
            'mantenidos' => $productosMantenidos,
        ];

        return $res;
    }

    public function operarStocksDetalle($detalle_analizado, $venta_id)
    {
        $this->operarProductosEliminados($detalle_analizado->quitados, $venta_id);
        $this->operarProductosNuevos($detalle_analizado->nuevos, $venta_id);
        $this->operarProductosMantenidos($detalle_analizado->mantenidos, $venta_id);
    }

    public function operarProductosMantenidos($items, $venta_id)
    {
        foreach ($items as $item) {

            //======== DEVOLVER CANT ANT, RESTAR CANT NUEVA, ACTUALIZAR DETALLE ==========
            if ($item->estado === 'SEPARADO') {

                //========= LIBERAR CANT ANTERIOR ========
                $this->s_producto_color_talla->incrementarStocks(
                    $item->almacen_id,
                    $item->producto_id,
                    $item->color_id,
                    $item->talla_id,
                    $item->cantidad_anterior
                );

                //======== REANALIZAR STOCK ==========
                $_items = [$item];
                $lst_validado = $this->s_producto_color_talla->analizarStock($_items);
                $item_validado = $lst_validado[0];

                //======== SEPARAR NUEVA CANTIDAD ========
                if ($item_validado->valido) {

                    $this->s_producto_color_talla->decrementarStocks(
                        $item->almacen_id,
                        $item->producto_id,
                        $item->color_id,
                        $item->talla_id,
                        $item->cantidad_nueva
                    );
                } else {

                    DB::table('pedidos_detalles')
                        ->where('pedido_id', $item->pedido_id)
                        ->where('almacen_id', $item->almacen_id)
                        ->where('producto_id', $item->producto_id)
                        ->where('color_id', $item->color_id)
                        ->where('talla_id', $item->talla_id)
                        ->update(['estado' => 'EN ESPERA']);

                    DB::table('cotizacion_documento_detalles')
                        ->where('documento_id', $venta_id)
                        ->where('almacen_id', $item->almacen_id)
                        ->where('producto_id', $item->producto_id)
                        ->where('color_id', $item->color_id)
                        ->where('talla_id', $item->talla_id)
                        ->update(['estado' => 'EN ESPERA']);
                }

                $this->s_repository->actualizarItemPedidoDetalle($item);
                $this->s_repository->actualizarItemVentaDetalle($item, $venta_id);
                continue;
            }

            //========= ANALIZAR DE NUEVO Y ACTUALIZAR =========
            if ($item->estado === 'EN ESPERA') {

                $_items                 =   [$item];
                $lst_validado           =   $this->s_producto_color_talla->analizarStock($_items);
                $item_validado          =   $lst_validado[0];

                //========= BAJANDO STOCKS ==========
                if ($item_validado->valido) {

                    $this->s_producto_color_talla->decrementarStocks($item->almacen_id, $item->producto_id, $item->color_id, $item->talla_id, $item->cantidad);

                    //======= SET ESTADO SEPARADO =========
                    DB::table('pedidos_detalles')
                        ->where('pedido_id', $item->pedido_id)
                        ->where('almacen_id', $item->almacen_id)
                        ->where('producto_id', $item->producto_id)
                        ->where('color_id', $item->color_id)
                        ->where('talla_id', $item->talla_id)
                        ->update([
                            'estado' => 'SEPARADO',
                        ]);

                    DB::table('cotizacion_documento_detalles')
                        ->where('documento_id', $venta_id)
                        ->where('almacen_id', $item->almacen_id)
                        ->where('producto_id', $item->producto_id)
                        ->where('color_id', $item->color_id)
                        ->where('talla_id', $item->talla_id)
                        ->update([
                            'estado' => 'SEPARADO',
                        ]);
                }

                $this->s_repository->actualizarItemPedidoDetalle($item);
                $this->s_repository->actualizarItemVentaDetalle($item, $venta_id);
            }

            if ($item->estado === 'EN PRODUCCION') {

                $cantidad   =   floatval($item->cantidad_anterior) - floatval($item->cantidad_nueva);
                if ($cantidad != 0) {
                    throw new Exception($item->producto_nombre . '-' . $item->color_nombre . '-' . $item->talla_nombre . ', NO PUEDE EDITARSE, SE ENCUENTRA EN UNA ORDEN DE PRODUCCIÓN');
                }

                if ($cantidad == 0) {

                    $_items                 =   [$item];
                    $lst_validado           =   $this->s_producto_color_talla->analizarStock($_items);
                    $item_validado          =   $lst_validado[0];

                    //========= BAJANDO STOCKS ==========
                    if ($item_validado->valido) {

                        $this->s_producto_color_talla->decrementarStocks($item->almacen_id, $item->producto_id, $item->color_id, $item->talla_id, $item->cantidad);

                        //======= SET ESTADO SEPARADO =========
                        DB::table('pedidos_detalles')
                            ->where('pedido_id', $item->pedido_id)
                            ->where('almacen_id', $item->almacen_id)
                            ->where('producto_id', $item->producto_id)
                            ->where('color_id', $item->color_id)
                            ->where('talla_id', $item->talla_id)
                            ->update([
                                'estado' => 'SEPARADO',
                            ]);

                        DB::table('cotizacion_documento_detalles')
                            ->where('documento_id', $venta_id)
                            ->where('almacen_id', $item->almacen_id)
                            ->where('producto_id', $item->producto_id)
                            ->where('color_id', $item->color_id)
                            ->where('talla_id', $item->talla_id)
                            ->update([
                                'estado' => 'SEPARADO',
                            ]);
                    }

                    $this->s_repository->actualizarItemPedidoDetalle($item);
                    $this->s_repository->actualizarItemVentaDetalle($item, $venta_id);
                }
            }
        }
    }

    public function operarProductosEliminados($items, $venta_id)
    {
        foreach ($items as $item) {

            //======== DEVOLVER STOCK ==========
            if ($item->estado === 'SEPARADO') {
                $this->s_producto_color_talla->incrementarStocks($item->almacen_id, $item->producto_id, $item->color_id, $item->talla_id, $item->cantidad);

                DB::table('pedidos_detalles')
                    ->where('pedido_id', $item->pedido_id)
                    ->where('almacen_id', $item->almacen_id)
                    ->where('producto_id', $item->producto_id)
                    ->where('color_id', $item->color_id)
                    ->where('talla_id', $item->talla_id)
                    ->delete();

                DB::table('cotizacion_documento_detalles')
                    ->where('documento_id', $venta_id)
                    ->where('almacen_id', $item->almacen_id)
                    ->where('producto_id', $item->producto_id)
                    ->where('color_id', $item->color_id)
                    ->where('talla_id', $item->talla_id)
                    ->delete();
            }

            //========== ELIMINAR DEL DETALLE RESERVA Y TICKET VENTA CON NORMALIDAD =========
            if ($item->estado === 'EN ESPERA') {

                DB::table('pedidos_detalles')
                    ->where('pedido_id', $item->pedido_id)
                    ->where('almacen_id', $item->almacen_id)
                    ->where('producto_id', $item->producto_id)
                    ->where('color_id', $item->color_id)
                    ->where('talla_id', $item->talla_id)
                    ->delete();

                DB::table('cotizacion_documento_detalles')
                    ->where('documento_id', $venta_id)
                    ->where('almacen_id', $item->almacen_id)
                    ->where('producto_id', $item->producto_id)
                    ->where('color_id', $item->color_id)
                    ->where('talla_id', $item->talla_id)
                    ->delete();
            }

            if ($item->estado === 'EN PRODUCCION') {
                throw new Exception($item->producto_nombre . '-' . $item->color_nombre . '-' . $item->talla_nombre . ', NO PUEDE ELIMINARSE, SE ENCUENTRA EN UNA ORDEN DE PRODUCCIÓN');
            }
        }
    }

    public function operarProductosNuevos($items, $venta_id)
    {
        $lst_validado           =   $this->s_producto_color_talla->analizarStock($items);

        $collect_validado       =   collect($lst_validado);
        $items_con_stock_valido =   $collect_validado->where('valido', true);
        $items_sin_stock_valido =   $collect_validado->where('valido', false);
        $venta                  =   Documento::findOrFail($venta_id);
        //$no_validos           =   $collect_validado->where('valido', false)->count();

        //======= BAJANDO STOCKS ======
        foreach ($items_con_stock_valido as $item) {

            $this->s_producto_color_talla->decrementarStocks($item->almacen_id, $item->producto_id, $item->color_id, $item->talla_id, $item->cantidad);

            //========= OBTENER ITEM CON DATOS COMPLETOS =======
            $items_completos = collect($items);
            $item_completo = $items_completos->first(function ($ic) use ($item) {
                return $ic->almacen_id == $item->almacen_id
                    && $ic->producto_id == $item->producto_id
                    && $ic->color_id == $item->color_id
                    && $ic->talla_id == $item->talla_id;
            });

            $this->s_repository->insertarItemPedidoDetalle($item_completo);
            $this->s_repository->insertarItemVentaDetalle($item_completo, $venta_id);

            //======= SET ESTADO SEPARADO =========
            DB::table('pedidos_detalles')
                ->where('pedido_id', $venta->pedido_id)
                ->where('almacen_id', $item->almacen_id)
                ->where('producto_id', $item->producto_id)
                ->where('color_id', $item->color_id)
                ->where('talla_id', $item->talla_id)
                ->update([
                    'estado' => 'SEPARADO',
                ]);

            DB::table('cotizacion_documento_detalles')
                ->where('documento_id', $venta_id)
                ->where('almacen_id', $item->almacen_id)
                ->where('producto_id', $item->producto_id)
                ->where('color_id', $item->color_id)
                ->where('talla_id', $item->talla_id)
                ->update([
                    'estado' => 'SEPARADO',
                ]);
        }

        //======= SET ESTADO EN ESPERA =======
        foreach ($items_sin_stock_valido as $item) {

            $items_completos = collect($items);
            $item_completo = $items_completos->first(function ($ic) use ($item) {
                return $ic->almacen_id == $item->almacen_id
                    && $ic->producto_id == $item->producto_id
                    && $ic->color_id == $item->color_id
                    && $ic->talla_id == $item->talla_id;
            });

            $this->s_repository->insertarItemPedidoDetalle($item_completo);
            $this->s_repository->insertarItemVentaDetalle($item_completo, $venta_id);

            DB::table('cotizacion_documento_detalles')
                ->where('documento_id', $venta_id)
                ->where('almacen_id', $item->almacen_id)
                ->where('producto_id', $item->producto_id)
                ->where('color_id', $item->color_id)
                ->where('talla_id', $item->talla_id)
                ->update([
                    'estado' => 'EN ESPERA',
                ]);
            DB::table('pedidos_detalles')
                ->where('pedido_id', $venta->pedido_id)
                ->where('almacen_id', $item->almacen_id)
                ->where('producto_id', $item->producto_id)
                ->where('color_id', $item->color_id)
                ->where('talla_id', $item->talla_id)
                ->update([
                    'estado' => 'EN ESPERA',
                ]);
        }
    }

    public function operarPedidoCuenta(float $total, Pedido $pedido)
    {
        $cuenta_cliente         = CuentaCliente::where('cotizacion_documento_id', $pedido->doc_venta_credito_id)->first();
        $monto_pagado           = bcsub($cuenta_cliente->monto, $cuenta_cliente->saldo, 2);
        $nuevo_saldo            = bcsub($total, $monto_pagado, 2);

        $cuenta_cliente->monto  = $total;
        $cuenta_cliente->saldo  = $nuevo_saldo;

        if (bccomp($cuenta_cliente->saldo, '0', 2) < 0) {
            throw new Exception("SALDO DE LA CUENTA CLIENTE NEGATIVO");
        }
        if (bccomp($cuenta_cliente->saldo, '0', 2) == 0) {
            $cuenta_cliente->estado = 'PAGADO';
        } else {
            $cuenta_cliente->estado = 'PENDIENTE';
        }
        $cuenta_cliente->save();

        $venta  =   Documento::findOrFail($pedido->doc_venta_credito_id);
        if ($cuenta_cliente->estado === 'PAGADO') {
            $venta->estado_pago =   'PAGADA';
        }
        if ($cuenta_cliente->estado === 'PENDIENTE') {
            $venta->estado_pago =   'PENDIENTE';
        }
        $venta->save();


        $pedido->doc_venta_credito_saldo        =   $cuenta_cliente->saldo;
        $pedido->doc_venta_credito_estado_pago  =   $cuenta_cliente->estado;
        $pedido->save();
    }

    /*
array:2 [
  "pedido_id"       => 2
  "lstProductos"    => "[{"producto_id":"1","producto_nombre":"PRODUCTO TEST","producto_codigo":null,"modelo_nombre":"","color_id":"2","color_nombre":"AZUL","talla_id":"1","talla_nombre":"34","cantidad":"10","precio_venta":"1.00","subtotal":0,"subtotal_nuevo":0,"porcentaje_descuento":0,"monto_descuento":0,"precio_venta_nuevo":0},{"producto_id":"1","producto_nombre":"PRODUCTO TEST","producto_codigo":null,"modelo_nombre":"","color_id":"3","color_nombre":"CELESTE","talla_id":"1","talla_nombre":"34","cantidad":"4","precio_venta":"1.00","subtotal":0,"subtotal_nuevo":0,"porcentaje_descuento":0,"monto_descuento":0,"precio_venta_nuevo":0}]"
]
*/
    public function validarCantidadAtendida(Request $request)
    {

        try {

            $lstProductos   =   json_decode($request->get('lstProductos'));
            $pedido_id      =   $request->get('pedido_id');

            $lstProductosValidados  =   [];
            $lstErroresValidacion   =   [];

            foreach ($lstProductos as $producto) {

                //========= OBTENIENDO CANTIDAD NUEVA ======
                $cantidad_nueva =   $producto->cantidad;

                //======== OBTENIENDO LA CANTIDAD ATENDIDA DEL PRODUCTO EN TIEMPO REAL =========
                $producto_en_detalle    =   DB::select(
                    'select
                                            pd.cantidad_atendida,
                                            pd.cantidad_pendiente,
                                            pd.producto_id,
                                            pd.color_id,
                                            pd.talla_id
                                            from pedidos_detalles as pd
                                            where
                                            pd.pedido_id = ?
                                            and pd.producto_id = ?
                                            and pd.color_id = ?
                                            and pd.talla_id = ?',
                    [
                        $pedido_id,
                        $producto->producto_id,
                        $producto->color_id,
                        $producto->talla_id
                    ]
                );

                //======== EN CASO EL PRODUCTO EXISTA EN EL DETALLE PREVIAMENTE ======
                if (count($producto_en_detalle) === 1) {

                    //======= VALIDAR CANTIDAD NUEVA CON LA CANTIDAD ATENDIDA =======
                    //======= LA CANTIDAD NUEVA DEBE SER MAYOR O IGUAL A LA CANTIDAD ATENDIDA ======
                    if ($cantidad_nueva < $producto_en_detalle[0]->cantidad_atendida) {

                        $mensaje    =   $producto->producto_nombre . "-" . $producto->color_nombre . "-" . $producto->talla_nombre .
                            ", CANT NUEVA(" . $cantidad_nueva . ") DEBE SER MAYOR O IGUAL A CANT ATEND(" . $producto_en_detalle[0]->cantidad_atendida . ").";

                        throw new Exception($mensaje);

                        $producto->validacion           =   false;
                        //$producto->mensaje_validacion   =   $mensaje;
                        $lstErroresValidacion[] =   (object)[
                            'producto_id' => $producto->producto_id,
                            'color_id' => $producto->color_id,
                            'talla_id' => $producto->talla_id,
                            'mensaje' => $mensaje
                        ];
                    } else {

                        $producto->validacion           =   true;
                        $producto->mensaje_validacion   =   '';
                    }

                    $lstProductosValidados[]    =   $producto;
                }

                //========= EN CASO EL PRODUCTO SEA NUEVO =======
                if (count($producto_en_detalle) === 0) {

                    if ($producto->cantidad > 0) {
                        $producto->validacion           =   true;
                        $lstProductosValidados[]        =   $producto;
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'CANTIDADES VALIDADAS']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy(int $id): Pedido
    {
        $pedido     =   Pedido::findOrFail($id);
        $this->s_pedido_validaciones->validacionDestroy($pedido);
        $this->s_repository->setEstadoPedido($id, 'ANULADO');

        $detalles           =   PedidoDetalle::where('pedido_id', $id)->get();
        $tiene_produccion   =   $detalles->contains('estado', 'EN PRODUCCION');

        if ($tiene_produccion) {
            throw new Exception("NO PUEDE ELIMINARSE UNA RESERVA CON ITEMS EN ORDEN DE PRODUCCIÓN");
        }

        $separados = $detalles->where('estado', 'SEPARADO');
        foreach ($separados as  $item) {
            $this->s_producto_color_talla->incrementarStocks($item->almacen_id, $item->producto_id, $item->color_id, $item->talla_id, $item->cantidad);
        }

        $this->s_repository->setEstadoPedidoDetalle($id, 'ANULADO');
        $this->s_cuenta->eliminarPorVentaId($pedido->doc_venta_credito_id);
        $this->s_venta->destroy($pedido->doc_venta_credito_id);

        return $pedido;
    }

    public function cambiarCliente(array $datos): Pedido
    {
        $this->s_pedido_validaciones->validacionCambiarCliente($datos);
        $pedido =   $this->s_repository->setClientePedido($datos);
        return $pedido;
    }

    public function storeFromCotizacion(array $datos):Pedido
    {
        $dto    =   $this->s_pedido_dto->getDtoStoreFromCotizacion($datos);
        $pedido =   $this->s_repository->insertarPedidoDto($dto);

        //===== OBTENIENDO DETALLE DE LA COTIZACIÓN ===========
        $detalle_cotizacion =   CotizacionDetalle::where('cotizacion_id',$datos['cotizacion_id'])->where('tipo','PRODUCTO')->get();
        $dto_detalle        =   $this->s_pedido_dto->getDtoDetalleFromCotizacion($detalle_cotizacion,$pedido);
        $this->s_repository->insertarPedidoDetalleDto($dto_detalle);

        return $pedido;
    }
}
