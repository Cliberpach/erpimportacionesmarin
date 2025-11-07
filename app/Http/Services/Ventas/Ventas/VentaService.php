<?php

namespace App\Http\Services\Ventas\Ventas;

use App\Almacenes\Almacen;
use App\Http\Controllers\UtilidadesController;
use App\Http\Services\Almacen\ProductoColorTalla\ProductoColorTallaService;
use App\Http\Services\Almacen\Traslados\TrasladoService;
use App\Http\Services\Kardex\Cuenta\KardexCuentaService;
use App\Http\Services\Ventas\Despacho\DespachoService;
use App\Http\Services\Whatsapp\WhatsappService;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Sedes\Sede;
use App\Mantenimiento\TipoPago\TipoPago;
use App\Models\Almacenes\Traslados\Traslado;
use App\Models\Ventas\Cotizaciones\CotizacionDetalle;
use App\Ventas\Cliente;
use App\Ventas\CuentaCliente;
use App\Ventas\DetalleCuentaCliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VentaService
{
    private VentaValidacion $s_validacion;
    private CorrelativoService $s_correlativo;
    private CalculosService $s_calculos;
    private VentaRepository $s_repository;
    private DespachoService $s_despacho;
    private ProductoColorTallaService $s_pct;
    private KardexCuentaService $s_kardex_cuenta;
    private WhatsappService $s_whatsapp;
    private VentaDto $s_venta_dto;
    private TrasladoService $s_traslado;

    public function __construct()
    {
        $this->s_validacion         =   new VentaValidacion();
        $this->s_correlativo        =   new CorrelativoService();
        $this->s_calculos           =   new CalculosService();
        $this->s_repository         =   new VentaRepository();
        $this->s_despacho           =   new DespachoService();
        $this->s_pct                =   new ProductoColorTallaService();
        $this->s_kardex_cuenta      =   new KardexCuentaService();
        $this->s_whatsapp           =   new WhatsappService();
        $this->s_venta_dto          =   new VentaDto();
        $this->s_traslado           =   new TrasladoService();
    }

    public function registrar(array $datos): Documento
    {
        //========= VALIDACIONES COMPLEJAS ======
        $datos_validados    =   $this->s_validacion->validacionStore($datos);

        $this->s_validacion->comprobanteActivo($datos_validados->sede_id, $datos_validados->tipo_venta);

        //======== OBTENER CORRELATIVO Y SERIE ======
        $datos_correlativo  =   $this->s_correlativo->getCorrelativo($datos_validados->tipo_venta, $datos_validados->sede_id);

        //========== CALCULAR MONTOS ======
        $montos =   $this->s_calculos->calcularMontos($datos_validados->lstVenta, $datos_validados);

        //======== OBTENIENDO LEYENDA ======
        $legenda                =   UtilidadesController::convertNumeroLetras($montos->monto_total_pagar);

        //======= INSERTAR VENTA =======
        $venta  =   $this->s_repository->insertarVenta($datos_validados, $montos, $datos_correlativo, $legenda);

        $this->s_repository->insertarDetalleVenta($datos_validados, $venta);

        //======== ASOCIAR LA VENTA CON EL MOVIMIENTO CAJA DEL COLABORADOR ====
        $this->s_repository->asociarVentaCaja($venta, $datos_validados);

        //======== EN CASO DE CONVERSIÓN DE DOCUMENTO =====
        $this->s_repository->asociarDocConvertido($datos_validados, $venta);

        //========== ACTUALIZAR ESTADO FACTURACIÓN A INICIADA ======
        DB::table('empresa_numeracion_facturaciones')
            ->where('empresa_id', Empresa::find(1)->id)
            ->where('sede_id', $datos_validados->sede_id)
            ->where('tipo_comprobante', $datos_validados->tipo_venta->id)
            ->where('emision_iniciada', '0')
            ->where('estado', 'ACTIVO')
            ->update([
                'emision_iniciada'       => '1',
                'updated_at'             => Carbon::now()
            ]);

        //======== EN CASO VENTA CONTADO Y PAGADA ELECTRÓNICO, VA AL KARDEX ===========
        if (!isset($datos['documento_convertido']) && $venta->condicion_id == 1 && $venta->tipo_pago_id != 1 && $venta->estado_pago == 'PAGADA') {
            $this->s_kardex_cuenta->registrarDesdeVenta($venta);
        }

        //======= DESPACHO ======
        $data_envio     =   $datos['data_envio'];
        $envio_venta    =   null;
        if ($data_envio) {
            $data_envio     =   json_decode($datos['data_envio']);
            $envio_venta    =   $this->s_repository->insertarDespacho($venta, $data_envio, $datos_validados->modo);
        }

        $this->operarTraslado($venta, $envio_venta);

        return $venta;
    }

    public function operarTraslado(Documento $venta, ?EnvioVenta $envio_venta = null)
    {
        $sede_almacen_id  = Almacen::findOrFail($venta->almacen_id)->sede_id;
        //======= SEDE VENTA != SEDE ALMACÉN ========
        if ($venta->sede_id != $sede_almacen_id) {
            $traslado   =   $this->s_traslado->storeFromVenta($venta);
            if ($traslado->envio_venta_id) {
                $this->s_despacho->enlazarDespachoTraslado($envio_venta, $traslado->id);
            }
        } else {

            //========= VENTA DE FÁBRICA CON ALMACÉN FÁBRICA Y ENVIO RECOJO EN TIENDA =========
            if (
                $venta->sede_id == 1
                && $envio_venta
                && $envio_venta->tipo_envio == 'RECOJO EN TIENDA'
            ) {
                $traslado   =   $this->s_traslado->storeFromVenta($venta);
                $this->s_despacho->enlazarDespachoTraslado($envio_venta, $traslado->id);
            }
        }
    }

    public function storePago(array $datos)
    {
        $cuenta_id      =   $datos['cuenta_id'] ?? null;
        $tipo_pago_id   =   $datos['tipo_pago_id'] ?? null;
        $tipo_pago      =   TipoPago::findOrFail($tipo_pago_id);

        $validacion =   DB::selectOne('SELECT
                            c.banco_nombre,
                            c.nro_cuenta,
                            c.cci,
                            c.celular,
                            c.titular,
                            c.moneda
                            FROM tipo_pago_cuentas as tpc
                            INNER JOIN cuentas as c ON c.id = tpc.cuenta_id
                            INNER JOIN tipos_pago as tp ON tp.id = tpc.tipo_pago_id
                            WHERE tpc.cuenta_id = ?
                            AND tpc.tipo_pago_id = ?
                            AND c.estado = "ACTIVO"
                            AND tp.estado = "ACTIVO"
                            LIMIT 1', [$cuenta_id, $tipo_pago_id]);

        if (!$validacion && $cuenta_id && $tipo_pago_id != 1) {
            throw new Exception('NO EXISTE EL TIPO DE PAGO ASOCIADO CON LA CUENTA BANCARIA SELECCIONADA');
        }

        $documento                          =   Documento::find($datos['venta_id']);

        $documento->tipo_pago_id            =   $datos['tipo_pago_id'] ?? null;
        $documento->importe                 =   $datos['importe'];
        $documento->efectivo                =   $datos['efectivo'];
        $documento->estado_pago             =   'PAGADA';
        $documento->pago_1_tipo_pago_nombre =   $tipo_pago->descripcion;
        $documento->pago_1_tipo_pago_id     =   $datos['tipo_pago_id'];
        $documento->pago_1_monto            =   $datos['importe'];
        $documento->pago_1_fecha_operacion  =   $datos['fecha_pago'] ?? null;
        $documento->pago_1_hora_operacion   =   $datos['hora_pago'] ?? null;

        if ($validacion) {
            $documento->pago_1_banco_nombre     =   $validacion->banco_nombre;
            $documento->pago_1_nro_cuenta       =   $validacion->nro_cuenta;
            $documento->pago_1_cci              =   $validacion->cci;
            $documento->pago_1_celular          =   $validacion->celular;
            $documento->pago_1_titular          =   $validacion->titular;
            $documento->pago_1_moneda           =   $validacion->moneda;
            $documento->pago_1_nro_operacion    =   $datos['nro_operacion'] ?? null;
            $documento->pago_1_cuenta_id        =   $datos['cuenta_id'] ?? null;
        }

        if (isset($datos['imagen']) && $datos['imagen'] instanceof UploadedFile) {
            if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'pagos'))) {
                mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'pagos'));
            }
            $extension              =   $datos['imagen']->getClientOriginalExtension();
            $nombreImagenPago       =   $documento->serie . '-' . $documento->correlativo . '.' . $extension;
            $documento->ruta_pago   =   $datos['imagen']->storeAs('public/pagos', $nombreImagenPago);
        }

        if (isset($datos['imagen2']) && $datos['imagen2'] instanceof UploadedFile) {
            if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'pagos'))) {
                mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'pagos'));
            }
            $extension              = $datos['imagen2']->getClientOriginalExtension();
            $nombreImagenPago       =   $documento->serie . '-' . $documento->correlativo . '-2' . '.' . $extension;
            $documento->ruta_pago_2 = $datos['imagen2']->storeAs('public/pagos', $nombreImagenPago);
        }

        $documento->update();

        if ($documento->convertir) {
            $doc_convertido                     = Documento::find($documento->convertir);
            $doc_convertido->estado_pago        = $documento->estado_pago;
            $doc_convertido->importe            = $documento->importe;
            $doc_convertido->efectivo           = $documento->efectivo;
            $doc_convertido->tipo_pago_id       = $documento->tipo_pago_id;
            $doc_convertido->banco_empresa_id   = $documento->banco_empresa_id;
            $doc_convertido->ruta_pago          = $documento->ruta_pago;
            $doc_convertido->update();
        }

        //======== EN CASO VENTA CONTADO Y PAGADA ELECTRÓNICO, VA AL KARDEX ===========
        if ($documento->condicion_id == 1 && $documento->tipo_pago_id != 1 && $documento->estado_pago == 'PAGADA') {
            $this->s_kardex_cuenta->registrarDesdeVenta($documento);
        }
    }

    public function operarVentaReserva(int $venta_id)
    {
        $lst_validado           =   $this->s_pct->analizarStockVenta($venta_id);
        $collect_validado       =   collect($lst_validado);
        $items_con_stock_valido =   $collect_validado->where('valido', true);
        $items_sin_stock_valido =   $collect_validado->where('valido', false);
        $venta                  =   Documento::findOrFail($venta_id);
        //$no_validos           =   $collect_validado->where('valido', false)->count();

        //======= BAJANDO STOCKS =====
        foreach ($items_con_stock_valido as $item) {

            $this->s_pct->decrementarStocks($item->almacen_id, $item->producto_id, $item->color_id, $item->talla_id, $item->cantidad);

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

    public function update(array $datos, int $id): Documento
    {
        $datos_validados        =   $this->s_validacion->validacionUpdate($datos, $id);

        //========== CALCULAR MONTOS ======
        $montos                     =   $this->s_calculos->calcularMontos($datos_validados->lstVenta, $datos_validados);
        $datos_validados->montos    =   $montos;

        //======== OBTENIENDO LEYENDA ======
        $legenda                    =   UtilidadesController::convertNumeroLetras($montos->monto_total_pagar);
        $datos_validados->legenda   =   $legenda;

        $documento  =   $this->s_repository->actualizarVenta($datos_validados, $datos_validados->documento);

        $traslado   =   Traslado::where('venta_id', $documento->id)->where('estado', 'ACTIVO')->first();
        if ($datos_validados->estado_pago_anterior === 'PENDIENTE' && (!$traslado || $traslado->estado === 'PENDIENTE')) {

            $detalle_anterior   =   Detalle::where('documento_id', $id)->get();
            //======== DEVOLVER STOCKS =======
            foreach ($detalle_anterior as $da) {
                $this->s_pct->incrementarStocks($da->almacen_id, $da->producto_id, $da->color_id, $da->talla_id, $da->cantidad);
            }

            //========= ELIMINAR DETALLE ANTERIOR =========
            $this->s_repository->eliminarDetalle($id);
            $this->s_repository->insertarDetalleVentaUpdate($datos_validados, $documento);


            //======== EN CASO VENTA CONTADO Y PAGADA ELECTRÓNICO, VA AL KARDEX ===========
            if ($documento->condicion_id == 1 && $documento->tipo_pago_id != 1 && $documento->estado_pago == 'PAGADA') {
                $this->s_kardex_cuenta->registrarDesdeVenta($documento);
            }
        }

        if ($documento->estado_despacho === 'PENDIENTE' || $documento->estado_despacho == 'S/D') {
            $datos_envio    =   json_decode($datos['data_envio']);
            $tiene_envio    =   EnvioVenta::where('documento_id', $id)->first();

            if ($tiene_envio) {
                $tiene_envio->cliente_id        =   $documento->cliente_id;
                $tiene_envio->cliente_nombre    =   $documento->cliente;
                $tiene_envio->cliente_celular   =   $datos_validados->cliente->telefono_movil;
                $tiene_envio->update();
            }

            if (!$datos_envio) {
                return $documento;
            }

            //========= SI YA TENÍA ENVÍO ACTUALIZAMOS ========
            $datos_envio                    =   (array)$datos_envio;
            $datos_envio['documento_id']    =   $id;
            $datos_envio['destinatario']    =   (array)$datos_envio['destinatario'];

            if ($tiene_envio) {
                if ($tiene_envio->estado == 'PENDIENTE') {
                    $this->s_despacho->update($datos_envio);
                }
            } else {
                //======= NUEVO ENVÍO ========
                $this->s_despacho->store($datos_envio);
            }
        }

        if ($datos_validados->estado_pago_anterior === 'PENDIENTE' && $traslado &&  $traslado->estado === 'PENDIENTE') {

            DB::table('traslados_detalle')
                ->where('traslado_id', $traslado->id)
                ->delete();

            DB::table('traslados')
                ->where('id', $traslado->id)
                ->delete();

            $envio_venta    =   EnvioVenta::where('documento_id', $documento->id);
            $this->operarTraslado($documento, $envio_venta);
        }

        if ($datos_validados->estado_pago_anterior === 'PENDIENTE' && !$traslado) {

            $envio_venta    =   EnvioVenta::where('documento_id', $documento->id);
            $this->operarTraslado($documento, $envio_venta);

        }

        return $documento;
    }

    public function destroy(int $id)
    {
        $this->s_repository->destroy($id);
    }

    public function getVoucherPdf(int $id, int $size): array
    {
        $documento  =   Documento::findOrFail($id);

        $this->qr_code($id);

        $detalles           =   Detalle::where('documento_id', $id)->where('eliminado', '0')->get();

        $mostrar_cuentas    =   DB::select('SELECT
                                c.propiedad
                                FROM configuracion AS c
                                WHERE c.slug = "MCB"')[0]->propiedad;

        $cuenta             =   CuentaCliente::where('cotizacion_documento_id', $id)->first();
        $detalle_pago       =   [];
        if ($cuenta) {
            $detalle_pago = DetalleCuentaCliente::from('detalle_cuenta_cliente as dcc')
                ->join('tipos_pago as tp', 'tp.id', '=', 'dcc.tipo_pago_id')
                ->where('dcc.cuenta_cliente_id', $cuenta->id)
                ->select(
                    'dcc.*',
                    'tp.descripcion as tipo_pago_nombre'
                )
                ->orderBy('dcc.created_at')
                ->get();
        }

        $empresa            =   Empresa::find(1);
        $sede               =   Sede::find($documento->sede_id);
        $despacho           =   EnvioVenta::where('documento_id', $id)->first();

        $pdf    =   Pdf::loadview('ventas.documentos.impresion.comprobante_ticket', [
            'documento'         =>  $documento,
            'detalles'          =>  $detalles,
            'empresa'           =>  $empresa,
            'mostrar_cuentas'   =>  $mostrar_cuentas,
            'sede'              =>  $sede,
            'despacho'          =>  $despacho,
            'cuenta'            =>  $cuenta,
            'detalle_pago'      =>  $detalle_pago
        ])->setPaper([0, 0, 226.772, 651.95]);

        if ($size == 80) {
            $pdf    =   $pdf->setPaper([0, 0, 226.772, 651.95]);
        }
        if ($size == 100) {
            $pdf    =   $pdf->setPaper('a4')->setWarnings(false);
        }

        return ['pdf' => $pdf, 'nombre' => $documento->serie . '-' . $documento->correlativo . '.pdf'];
    }

    public function qr_code(int $id): array
    {

        $documento = Documento::findOrFail($id);
        $name_qr = '';

        if ($documento->contingencia == '0') {
            $name_qr = $documento->serie . "-" . $documento->correlativo . '.svg';
        } else {
            $name_qr = $documento->serie_contingencia . "-" . $documento->correlativo . '.svg';
        }

        //======= NOTA DE VENTA =====
        if ($documento->tipo_venta_id == 129) {
            $data_qr = $documento->ruc_empresa . '|' .        // RUC
                '04' . '|' .                           // Tipo de Documento (04 para Nota de Venta)
                ($documento->contingencia == '0' ? $documento->serie : $documento->serie_contingencia) . '|' . // SERIE
                $documento->correlativo . '|' .        // NUMERO
                (float) $documento->total_pagar . '|' .      // MTO TOTAL DEL COMPROBANTE
                $documento->created_at; // FECHA DE EMISION
        } else {

            //========= BOLETA O FACTURA =======
            $data_qr =  $documento->ruc_empresa . '|' .                // RUC
                $documento->tipoDocumento() . '|' .            // TIPO DE DOCUMENTO
                ($documento->contingencia == '0' ? $documento->serie : $documento->serie_contingencia) . '|' . // SERIE
                $documento->correlativo . '|' .                // NUMERO
                (float) $documento->total_igv . '|' .                                     // MTO TOTAL IGV
                (float) $documento->total_pagar . '|' .              // MTO TOTAL DEL COMPROBANTE
                $documento->created_at . '|' .  // FECHA DE EMISION
                $documento->tipoDocumentoCliente() . '|' .     // TIPO DE DOCUMENTO ADQUIRENTE
                $documento->documento_cliente;                 // NUMERO DE DOCUMENTO ADQUIRENTE

        }


        $miQr = QrCode::format('svg')
            ->size(130)
            ->backgroundColor(0, 0, 0)
            ->color(255, 255, 255)
            ->margin(1)
            ->generate($data_qr);

        $pathToFile_qr = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'qrs' . DIRECTORY_SEPARATOR . $name_qr);

        // Crea el directorio si no existe
        if (!file_exists(dirname($pathToFile_qr))) {
            mkdir(dirname($pathToFile_qr), 0755, true);
        }

        // Guarda el QR en el archivo
        file_put_contents($pathToFile_qr, $miQr);

        // Actualiza la ruta del QR en la base de datos
        $documento->ruta_qr = 'public/qrs/' . $name_qr;
        $documento->update();

        return array('success' => true, 'mensaje' => 'QR creado exitosamente');
    }

    public function enviarPdfWsp(int $venta_id, string $tipo_pdf)
    {
        $venta      =   Documento::findOrFail($venta_id);
        $cliente    =   Cliente::findOrFail($venta->cliente_id);
        $telefono   =   $cliente->telefono_movil;

        if (!$telefono || !ctype_digit($telefono) || strlen($telefono) !== 9) {
            return;
        }

        $empresa    =   Empresa::find(1);
        $serie      =   $venta->serie . '-' . $venta->correlativo;
        $mensaje    =   <<<MSG
                        📦 *COMPROBANTE DE VENTA*

                        🏢 *{$empresa->razon_social}*
                        🧾 RUC: {$empresa->ruc}

                        📅 *Fecha:* {$venta->created_at->format('d/m/Y')}
                        🆔 *N°:* {$serie}
                        👤 *Cliente:* {$cliente->nombre}
                        MSG;

        $mensaje .= "\n\n📎 Se adjunta su comprobante en PDF.\n\n🤝 ¡Gracias por su preferencia!";

        $res        =   $this->getVoucherPdf($venta->id, $tipo_pdf);
        $pdf        =   $res['pdf'];
        $pdfContent =   $pdf->output();
        $pdf_name   =   $res['nombre'];

        //======= ENVIAR A TODOS LOS TELEFONOS DEL CLIENTE ==========
        $telefonos = array_filter([
            $cliente->telefono_movil,
            // $cliente->telefono_2,
            // $cliente->telefono_3,
            // $cliente->telefono_4,
        ]);

        //======== GUARDAR PDF ======
        $folder = public_path('storage/wsp_ventas');
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
        $pdfPath = $folder . '/' . $pdf_name;
        file_put_contents($pdfPath, $pdfContent);
        $pdfUrl = url('storage/wsp_ventas/' . $pdf_name);

        $this->s_whatsapp->enviarMensaje($telefonos, $mensaje, $pdfUrl, $pdf_name);
    }

    public function convertirStore(array $datos): Documento
    {
        $this->s_validacion->validacionConvertir($datos);
        $venta  =   Documento::findOrFail($datos['documento_id']);

        $_datos =   [
            'almacenSeleccionado'   =>  $venta->almacen_id,
            'sede_id'               =>  $venta->sede_id,
            'cliente_id'            =>  $datos['cliente'],
            //'condicion_id'        =>  $venta->condicion_id,
            'condicion_id'          =>  1,
            'productos_tabla'       =>  $datos['lstVenta'],
            'tipo_venta'            =>  $datos['tipo_comprobante'],
            'documento_convertido'  =>  $datos['documento_id'],
            'monto_embalaje'        =>  $venta->monto_embalaje,
            'monto_envio'           =>  $venta->monto_envio,
            'metodoPagoId'          =>  $venta->pago_1_tipo_pago_id,
            'cuentaPagoId'          =>  $venta->pago_1_cuenta_id,
            'montoPago'             =>  $venta->total_pagar,
            'nroOperacionPago'      =>  $venta->pago_1_nro_operacion,
            'fechaOperacionPago'    =>  $venta->pago_1_fecha_operacion,
            'origen_venta'          =>  $venta->origen_venta_id,
            'data_envio'            =>  null
        ];

        $venta_conversion   =   $this->registrar($_datos);
        return $venta_conversion;
    }

    public function storeVentaFromCotizacion(array $datos): Documento
    {
        $datos = $this->s_validacion->validacionStoreFromCotizacion($datos);
        $this->s_validacion->comprobanteActivo($datos['cotizacion']->sede_id, $datos['tipo_comprobante']);

        //======== OBTENIENDO LEYENDA ======
        $legenda                    =   UtilidadesController::convertNumeroLetras($datos['cotizacion']->total_pagar);
        $datos_correlativo          =   $this->s_correlativo->getCorrelativo($datos['tipo_comprobante'], $datos['cotizacion']->sede_id);
        $montos                     =   $this->s_calculos->calcularMontosFromCotizacion($datos['cotizacion']);
        $datos['montos']            =   $montos;
        $datos['datos_correlativo'] =   $datos_correlativo;
        $datos['legenda']           =   $legenda;

        $dto    =   $this->s_venta_dto->getDtoStoreFromCotizacion($datos);
        $venta  =   $this->s_repository->storeVenta($dto);

        //=========== DETALLE COTIZACIÓN =======
        $cotizacion_detalle =   CotizacionDetalle::where('cotizacion_id', $datos['cotizacion_id'])->where('tipo', 'PRODUCTO')->get();
        $this->s_repository->insertarDetalleVentaFromCotizacion($datos['cotizacion'], $cotizacion_detalle, $venta);

        //============ ASOCIAR A CAJA ==========
        $this->s_repository->asociarVentaCajaFromCotizacion($venta, $datos);

        //========== ACTUALIZAR ESTADO FACTURACIÓN A INICIADA ======
        DB::table('empresa_numeracion_facturaciones')
            ->where('empresa_id', $venta->empresa_id)
            ->where('sede_id', $venta->sede_id)
            ->where('tipo_comprobante', $venta->tipo_venta_id)
            ->where('emision_iniciada', '0')
            ->where('estado', 'ACTIVO')
            ->update([
                'emision_iniciada'       => '1',
                'updated_at'             => Carbon::now()
            ]);

        //======= DESPACHO ======
        $data_envio =   $datos['data_envio'];
        if ($data_envio) {
            $data_envio = json_decode($datos['data_envio']);
            $this->s_repository->insertarDespacho($venta, $data_envio, 'VENTA');
        }

        return $venta;
    }
}
