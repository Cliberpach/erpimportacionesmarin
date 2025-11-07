<?php

namespace App\Http\Controllers\Despachos;

use App\Exports\Despachos\Embalaje\EmbalajeExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilidadesController;
use App\Http\Services\Despachos\Embalaje\EmbalajeManager;
use App\Http\Services\Ventas\Despacho\DespachoManager;
use App\Jobs\EmbalajeWsp;
use Illuminate\Http\Request;
use App\Ventas\EnvioVenta;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\MetodoEntrega\EmpresaEnvioSede;
use App\Mantenimiento\MetodoEntrega\MetodoEntrega;
use App\Mantenimiento\Tabla\Detalle;
use App\Mantenimiento\Ubigeo\Departamento;
use App\Mantenimiento\Ubigeo\Distrito;
use App\Mantenimiento\Ubigeo\Provincia;
use App\Ventas\Cliente;
use App\Ventas\Documento\Documento;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;

class EmbalajeController extends Controller
{
    private DespachoManager $s_despacho;
    private EmbalajeManager $s_embalaje;

    public function __construct()
    {
        $this->s_despacho   =   new DespachoManager();
        $this->s_embalaje   =   new EmbalajeManager();
    }

    public function index()
    {
        $this->authorize('haveaccess', 'embalaje.index');

        $cliente_varios     =   DB::select('SELECT c.id,c.nombre FROM clientes AS c WHERE c.id = 1');
        $departamentos      =   departamentos();
        $origenes_ventas    =   UtilidadesController::getOrigenesVentas();
        $tipos_pago_envio   =   UtilidadesController::getTiposPagoEnvio();
        $tipos_envio        =   UtilidadesController::getTiposEnvio();
        $tipos_documento    =   UtilidadesController::getTiposDocumento();

        return view(
            'despachos.embalaje.index',
            compact('cliente_varios', 'departamentos', 'origenes_ventas', 'tipos_pago_envio', 'tipos_envio', 'tipos_documento')
        );
    }

    public function getEmbalaje(Request $request)
    {
        $fecha_inicio   =   $request->get('fecha_inicio');
        $fecha_fin      =   $request->get('fecha_fin');
        $estado_items   =   $request->get('estado_items');
        $cliente_id     =   $request->get('cliente_id');
        $modo           =   $request->get('modo');

        $query  =   DB::table('envios_ventas as ev')
            ->select(
                'eso.nombre as sede_origen_nombre',
                'es.nombre as sede_despachadora_nombre',
                'ev.id',
                'ev.documento_nro',
                'ev.cliente_nombre',
                'ev.cliente_celular',
                'ev.user_vendedor_nombre',
                'ev.almacen_nombre',
                'ev.user_despachador_nombre',
                'ev.fecha_envio_propuesta',
                DB::raw("IFNULL(ev.fecha_envio, '-') AS fecha_envio"),
                DB::raw("DATE_FORMAT(ev.created_at, '%Y-%m-%d %H:%i:%s') AS fecha_registro"),
                'ev.tipo_envio',
                'ev.empresa_envio_nombre',
                'ev.sede_envio_nombre',
                DB::raw("CONCAT(ev.departamento, ' - ', ev.provincia, ' - ', ev.distrito) AS ubigeo"),
                'ev.tipo_pago_envio',
                'ev.destinatario_nombre',
                DB::raw("CONCAT(ev.destinatario_tipo_doc, ': ', ev.destinatario_nro_doc) AS destinatario_nro_doc"),
                'ev.monto_envio',
                'ev.entrega_domicilio',
                'ev.direccion_entrega',
                'ev.estado',
                'ev.documento_id',
                'ev.obs_despacho',
                'ev.obs_rotulo',
                'ev.modo',
                'cd.cliente_id',
                'ev.estado_items',
                't.estado as traslado_estado',
            )
            ->join('empresa_sedes as es', 'es.id', 'ev.sede_despachadora_id')
            ->join('empresa_sedes as eso', 'eso.id', 'ev.sede_id')
            ->join('cotizacion_documento as cd', 'cd.id', 'ev.documento_id')
            ->leftJoin('traslados as t', 't.id', '=', 'ev.traslado_id')
            ->where('cd.estado_pago', 'PAGADA')
            ->where('cd.sunat', '<>', '2')
            ->whereIn('ev.estado', ['PENDIENTE', 'RESERVADO'])
            ->where(function ($q) {
                $q->whereIn('t.estado', ['RECIBIDO'])
                    ->orWhereNull('t.estado');
            });

        if ($fecha_inicio) {
            $query->whereDate('ev.created_at', '>=', $fecha_inicio);
        }

        if ($fecha_fin) {
            $query->whereDate('ev.created_at', '<=', $fecha_fin);
        }


        if ($cliente_id) {
            $query->where('ev.cliente_id', '=', $cliente_id);
        }

        if ($modo) {
            $query->where('ev.modo', '=', $modo);
        }

        if ($estado_items && $estado_items != 'TODO') {
            $query->where('ev.estado_items', '=', $estado_items);
        }

        //========= FILTRO POR ROLES ======
        $roles = DB::table('role_user as rl')
            ->join('roles as r', 'r.id', '=', 'rl.role_id')
            ->where('rl.user_id', Auth::user()->id)
            ->pluck('r.name')
            ->toArray();

        //======== ADMIN PUEDE VER TODOS LOS DESPACHOS DE SU SEDE =====
        if (in_array('ADMIN', $roles)) {

            $query->where('ev.sede_despachadora_id', Auth::user()->sede_id);
        } else {

            //====== USUARIOS PUEDEN VER SUS PROPIOS DESPACHOS ======
            $query->where('ev.sede_despachadora_id', Auth::user()->sede_id);
        }

        return DataTables::of($query)->make(true);
    }


    public function showDetalles($documento_id)
    {
        try {

            $documento              =   DB::select('
                                        SELECT
                                            cd.serie,
                                            cd.correlativo,
                                            cd.almacen_nombre as almacen_despacho,
                                            es.nombre as sede_despacho
                                        FROM cotizacion_documento as cd
                                        JOIN almacenes as a on a.id = cd.almacen_id
                                        JOIN empresa_sedes as es on es.id = a.sede_id
                                            WHERE cd.id = ?
                                        ', [$documento_id])[0];

            $detalles_doc_venta     =   DB::select('select
                                        cdd.nombre_producto,
                                        cdd.nombre_color,
                                        cdd.nombre_talla,
                                        cdd.nombre_modelo,
                                        cdd.cantidad,
                                        IFNULL(cdd.cantidad_cambiada, 0) AS cantidad_cambiada,
                                        cdd.cantidad_sin_cambio,
                                        cdd.estado
                                        from cotizacion_documento_detalles as cdd
                                        where cdd.documento_id=?', [$documento_id]);

            return response()->json(['success' => true, 'detalles_doc_venta' => $detalles_doc_venta, 'documento' => $documento]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => "ERROR EN EL SERVIDOR", 'exception' => $th->getMessage()]);
        }
    }

    public function pdfBultos($documento_id, $despacho_id, $nro_bultos)
    {
        set_time_limit(300);
        $empresa = Empresa::first();

        $despacho = DB::select('SELECT ev.distrito, ev.destinatario_nombre, ev.documento_nro,ev.cliente_nombre,
                        ev.destinatario_tipo_doc,ev.destinatario_nro_doc, ev.cliente_celular, ev.entrega_domicilio,
                        ev.direccion_entrega,ev.created_at,ev.empresa_envio_nombre,ev.tipo_pago_envio,ev.obs_rotulo
                        FROM envios_ventas AS ev
                        WHERE ev.id=? AND ev.documento_id=?', [$despacho_id, $documento_id]);

        $pdf = PDF::loadview('ventas.despachos.pdf-bultos.pdf2', [
            'empresa'       =>  $empresa,
            'nro_bultos'    =>  $nro_bultos,
            'despacho'      =>  $despacho[0]
        ])->setPaper('a4')
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        return $pdf->stream($despacho[0]->distrito . '-' . $despacho[0]->cliente_nombre . '-' . $despacho[0]->created_at . '.pdf');
    }

    public function setFallado(Request $request)
    {
        try {
            DB::beginTransaction();

            //======= ACTUALIZANDO DESPACHO ========
            DB::table('envios_ventas')
                ->where('id', $request->get('despachoId'))
                ->where('modo', 'VENTA')
                ->update(['estado_items' => 'CON FALLAS']);

            DB::commit();

            return response()->json(['success' => true, 'message' => "ENVÍO ESTABLECIDO CON FALLAS"]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function setRevision(Request $request)
    {
        try {
            DB::beginTransaction();

            //======= ACTUALIZANDO DESPACHO ========
            DB::table('envios_ventas')
                ->where('id', $request->get('despachoId'))
                ->where('modo', 'VENTA')
                ->update(['estado_items' => 'EN REVISION']);

            DB::commit();

            return response()->json(['success' => true, 'message' => "ENVÍO ESTABLECIDO EN REVISIÓN"]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function setReserva(Request $request)
    {
        dd('nel pastel');
        try {
            DB::beginTransaction();

            //======= ACTUALIZANDO DESPACHO ========
            DB::table('envios_ventas')
                ->where('id', $request->get('despacho_id'))
                ->where('documento_id', $request->get('documento_id'))
                ->where('modo', 'RESERVA')
                ->update(['estado' => 'RESERVADO']);


            $venta                  =   Documento::findOrFail($request->get('documento_id'));
            $venta->estado_despacho = 'RESERVADO';
            $venta->update();

            DB::table('cotizacion_documento_detalles')
                ->where('documento_id', $request->get('documento_id'))
                ->where('estado', 'SEPARADO')
                ->where('tipo', 'PRODUCTO')
                ->update(['estado' => 'RESERVADO']);


            DB::table('pedidos_detalles')
                ->where('pedido_id', $venta->pedido_id)
                ->where('estado', 'SEPARADO')
                ->where('tipo', 'PRODUCTO')
                ->update(['estado' => 'RESERVADO']);

            DB::commit();

            return response()->json(['success' => true, 'message' => "ENVÍO RESERVADO"]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => "ERROR EN EL SERVIDOR",
                'exception' => $th->getMessage()
            ]);
        }
    }

    public function setDespacho(Request $request)
    {

        try {
            DB::beginTransaction();
            $fecha_actual = Carbon::now()->format('Ymd');

            //======= ACTUALIZANDO DESPACHO ========
            DB::table('envios_ventas')
                ->where('id', $request->get('despacho_id'))
                ->where('documento_id', $request->get('documento_id'))
                ->update([
                    'estado'          => 'DESPACHADO',
                    'fecha_envio'               =>  $fecha_actual,
                    'user_despachador_id'       =>  Auth::user()->id,
                    'user_despachador_nombre'   =>  Auth::user()->usuario
                ]);

            //======= REVIZAR SI EL DOCUMENTO ESTÁ LIGADO A UN PEDIDO =======
            $pedido_atencion    =   DB::select('select cd.pedido_id
                                    from cotizacion_documento as cd
                                    where cd.id = ?', [$request->get('documento_id')]);

            //========== EN CASO EL DOCUMENTO SEA PRODUCTO DE UNA ATENCIÓN DE PEDIDO ========
            if (count($pedido_atencion) === 1) {
                if ($pedido_atencion[0]->pedido_id) {
                    //======= OBTENER DETALLE DEL DOCUMENTO ======
                    $doc_detalles   =   DB::select('select * from cotizacion_documento_detalles as cdd
                    where cdd.documento_id = ?', [$request->get('documento_id')]);

                    //===== RECORRER EL DETALLE DEL DOCUMENTO DE VENTA =====
                    foreach ($doc_detalles as $item) {

                        //===== ACTUALIZAR CANTIDADES ENVIADAS DEL PEDIDO ASOCIADO AL DOC VENTA ======
                        DB::update(
                            'UPDATE pedidos_detalles
                    SET cantidad_enviada = cantidad_enviada + ?
                    WHERE pedido_id = ? and producto_id = ? and color_id = ? and talla_id = ?',
                            [
                                $item->cantidad,
                                $pedido_atencion[0]->pedido_id,
                                $item->producto_id,
                                $item->color_id,
                                $item->talla_id
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => "ENVÍO DESPACHADO"]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => "ERROR EN EL SERVIDOR",
                'exception' => $th->getMessage()
            ]);
        }
    }

    public function getDespacho($documento_id)
    {
        try {
            $despacho   =   EnvioVenta::where('documento_id', $documento_id)->first();
            return response()->json(['success' => true, 'despacho' => $despacho]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => "ERROR EN EL SERVIDOR", 'exception' => $th->getMessage()]);
        }
    }

    /*
array:15 [
  "departamento" => 13
  "provincia" => 1301
  "distrito" => 130101
  "tipo_envio" => 190
  "empresa_envio" => 1
  "sede_envio" => 1
  "destinatario" => array:3 [
    "tipo_documento" => "DNI"
    "nro_documento" => "99999999"
    "nombres" => "VARIOS"
  ]
  "documento_id" => 53
  "direccion_entrega" => null
  "entrega_domicilio" => false
  "origen_venta" => 192
  "fecha_envio_propuesta" => "2025-08-20"
  "obs_rotulo" => "test 1"
  "obs_despacho" => "test 2"
  "tipo_pago_envio" => 195
]
*/
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->s_despacho->store($request->toArray());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'DATOS DE ENVÍO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    /*
array:15 [
  "departamento" => 13
  "provincia" => 1301
  "distrito" => 130101
  "tipo_envio" => 190
  "empresa_envio" => 1
  "sede_envio" => 1
  "destinatario" => array:3 [
    "tipo_documento" => "DNI"
    "nro_documento" => "99999999"
    "nombres" => "VARIOS"
  ]
  "documento_id" => 53
  "direccion_entrega" => "av union 123"
  "entrega_domicilio" => true
  "origen_venta" => 193
  "fecha_envio_propuesta" => "2025-08-20"
  "obs_rotulo" => "TEST 1"
  "obs_despacho" => "TEST 2"
  "tipo_pago_envio" => 195
]
*/
    public function updateDespacho(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->s_despacho->update($request->toArray());

            DB::commit();

            return response()->json(['success' => true, 'message' => 'DATOS DE ENVÍO ACTUALIZADOS']);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json(['success' => false, 'exception' => $th->getMessage()]);
        }
    }

    public function getDespachoById(int $id)
    {
        try {
            $despacho   =   EnvioVenta::findOrFail($id);
            return response()->json(['success' => true, 'message' => 'DESPACHO OBTENIDO', 'data' => $despacho]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:17 [
  "departamento" => "13"
  "provincia" => "1301"
  "distrito" => "130101"
  "zona" => "NORTE"
  "tipo_envio" => "190"
  "tipo_pago_envio" => "195"
  "empresa_envio" => "1"
  "sede_envio" => "1"
  "entrega_domicilio" => "SI"
  "direccion_entrega" => "AV UNION 321"
  "origen_venta" => "191"
  "fecha_envio" => null
  "obs_rotulo" => "Y DALE U"
  "obs_despacho" => "test"
  "tipo_doc_destinatario" => "6"
  "nro_doc_destinatario" => "99999999"
  "nombres_destinatario" => "CLIENTES VARIOS"
  "despacho_id" => 11
]
*/
    public function actualizarDespacho(Request $request)
    {
        try {

            $departamentoId = str_pad($request->get('departamento'), 2, '0', STR_PAD_LEFT);
            $provinciaId   = str_pad($request->get('provincia'), 4, '0', STR_PAD_LEFT);
            $distritoId    = str_pad($request->get('distrito'), 6, '0', STR_PAD_LEFT);

            //====== ACTUALIZAR DESPACHO ========
            $envio_venta                            =   EnvioVenta::findOrFail($request->get('despacho_id'));
            $envio_venta->departamento_id           =   $departamentoId;
            $envio_venta->provincia_id              =   $provinciaId;
            $envio_venta->distrito_id               =   $distritoId;

            $envio_venta->departamento = Departamento::findOrFail($departamentoId)->nombre;
            $envio_venta->provincia    = Provincia::findOrFail($provinciaId)->nombre;
            $envio_venta->distrito     = Distrito::findOrFail($distritoId)->nombre;

            $empresa_envio                          =   MetodoEntrega::findOrFail($request->get('empresa_envio'));
            $empresa_envio_sede                     =   EmpresaEnvioSede::findOrFail($request->get('sede_envio'));

            $envio_venta->empresa_envio_id          =   $empresa_envio->id;
            $envio_venta->empresa_envio_nombre      =   $empresa_envio->empresa;
            $envio_venta->sede_envio_id             =   $empresa_envio_sede->id;
            $envio_venta->sede_envio_nombre         =   $empresa_envio_sede->direccion;

            $tipo_envio                             =   Detalle::findOrFail($request->get('tipo_envio'));
            $envio_venta->tipo_envio_id             =   $tipo_envio->id;
            $envio_venta->tipo_envio                =   $tipo_envio->descripcion;

            $tipo_pago_envio                        =   Detalle::findOrFail($request->get('tipo_pago_envio'));
            $envio_venta->tipo_pago_envio           =   $tipo_pago_envio->descripcion;
            $envio_venta->tipo_pago_envio_id        =   $tipo_pago_envio->id;

            $origen_venta                           =   Detalle::findOrFail($request->get('origen_venta'));
            $envio_venta->origen_venta_id           =   $origen_venta->id;
            $envio_venta->origen_venta              =   $origen_venta->descripcion;

            $envio_venta->fecha_envio_propuesta     =   $request->get('fecha_envio');
            $envio_venta->obs_rotulo                =   mb_strtoupper($request->obs_rotulo, 'UTF-8');
            $envio_venta->obs_despacho              =   mb_strtoupper($request->obs_despacho, 'UTF-8');

            $envio_venta->destinatario_tipo_doc     =   $request->tipo_doc_destinatario;
            $envio_venta->destinatario_nro_doc      =   $request->nro_doc_destinatario;
            $envio_venta->destinatario_nombre       =   mb_strtoupper($request->nombres_destinatario, 'UTF-8');
            $envio_venta->entrega_domicilio         =   $request->entrega_domicilio;
            $envio_venta->direccion_entrega         =   mb_strtoupper($request->direccion_entrega, 'UTF-8');

            $envio_venta->update();

            return response()->json(['success' => true, 'message' => "DESPACHO ACTUALIZADO CON ÉXITO"]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getMdlEmbalaje($id)
    {
        try {
            $envio = $this->s_embalaje->getMdlEmbalaje($id);
            return response()->json(['success' => true, 'data' => $envio]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function generarPaqueteEmbalaje(Request $request)
    {
        Db::beginTransaction();
        try {

            $paquete    =   $this->s_embalaje->generarPaqueteEmbalaje($request->toArray());

            $url_rotulo = route('despachos.reparto_detalle.pdfBultos', [
                'id' => $paquete->id,
                'nro_bultos' => 1
            ]);

            DB::commit();

            //EmbalajeWsp::dispatch($paquete->id);

            return response()->json(['success' => true, 'message' => "PAQUETE GENERADO CON ÉXITO", 'data' => $url_rotulo]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function createGuiaEnvio(int $envio_id)
    {
        try {
            $envio  =   EnvioVenta::findOrFail($envio_id);
            if ($envio->guia_id) {
                Session::flash('message_error', 'YA EXISTE UNA GUÍA PARA ESTOS ENVÍOS: ' . $envio->guia_serie);
                return back();
            }
            if ($envio->sede_despachadora_id != Auth::user()->sede_id) {
                throw new Exception("SOLO USUARIOS DE LA SEDE DESPACHADORA PUEDEN GENERAR GUÍA PARA ESTE ENVÍO");
            }

            $data   =   $this->s_embalaje->getGuiaCreateEnvio($envio_id);
            $ventas =   $data['ventas'];
            $ventasInvalidas = $ventas->filter(function ($venta) {
                return $venta->tipo_venta_id == 129 && is_null($venta->convert_en_id);
            });

            if ($ventasInvalidas->isNotEmpty()) {
                $docs = $ventasInvalidas->pluck('documento_nro')->implode(', ');
                Session::flash('message_error', "Las siguientes ventas deben estar convertidas antes de generar la guía: {$docs}");
                return back();
            }

            return view('despachos.embalaje.guia_paquete.create', $data);
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }

    /*
array:14 [
  "registrador" => "ADMINISTRADOR"
  "fecha_emision" => "2025-09-25"
  "modalidad_traslado" => "01"
  "fecha_traslado" => "2025-09-25"
  "peso" => "0.1"
  "unidad" => "KGM"
  "vehiculo" => "1"
  "conductor" => "1"
  "sede_id" => "1"
  "registrador_id" => "1"
  "ventas" => "[{"id":374,"documento_nro":"TK01-345","documento_id":376,"estado_pago":"PAGADA","convert_en_serie":"B002-4","tipo_venta_id":"129","guia_id":null},{"id":376,"documento_nro":"TK01-347","documento_id":378,"estado_pago":"PAGADA","convert_en_serie":"B002-3","tipo_venta_id":"129","guia_id":null}]"
  "almacen" => "1"
  "motivo_traslado" => "01"
  "cliente" => "288"
]
*/
    public function storeGuiaEnvio(Request $request)
    {
        DB::beginTransaction();
        try {

            $guia   =   $this->s_embalaje->storeGuiaEnvio($request->toArray());

            //======= REGISTRO DE ACTIVIDAD ========
            $descripcion = "SE REGISTRÓ LA GUÍA CON LA DESCRIPCION: " . $guia->serie . '-' . $guia->correlativo;
            $gestion = "GUIA REMISIÓN";
            crearRegistro($guia, $descripcion, $gestion);

            Session::flash('message_success', 'GUÍA REGISTRADA CON ÉXITO ' . $guia->serie . '-' . $guia->correaltivo);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'GUÍA GENERADA CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function queryReporteEmbalaje(Request $request)
    {
        $filtro_modo            =   $request->get('filtroModo');
        $filtro_cliente         =   $request->get('filtroCliente');
        $filtro_fecha_inicio    =   $request->get('filtroFechaInicio');
        $filtro_fecha_fin       =   $request->get('filtroFechaFin');

        $datos  =   DB::table('envios_ventas as ev')
            ->join('cotizacion_documento_detalles as cdd', 'cdd.documento_id', 'ev.documento_id')
            ->join('cotizacion_documento as cd', 'cd.id', 'ev.documento_id')
            ->select(
                'ev.cliente_nombre',
                'cd.serie',
                'cd.correlativo',
                'cdd.nombre_producto',
                'cdd.nombre_color',
                'cdd.nombre_talla',
                'cdd.cantidad',
                'cd.estado_despacho',
                'cdd.estado as estado_item'
            )
            ->where('cd.sunat', '<>', '2')
            ->where('cdd.tipo', 'PRODUCTO')
            ->where('ev.estado', 'PENDIENTE')
            ->where('ev.estado_items', 'PENDIENTE')
            ->orderBy('ev.cliente_nombre', 'asc')
            ->orderBy('ev.created_at', 'asc')
            ->orderBy('cd.serie', 'asc')
            ->orderBy('cd.correlativo', 'asc');

        if ($filtro_modo) {
            $datos  =   $datos->where('ev.modo', $filtro_modo);
        }
        if ($filtro_cliente) {
            $datos  =   $datos->where('ev.cliente_id', $filtro_cliente);
        }
        if ($filtro_fecha_inicio) {
            $datos  =   $datos->whereDate('ev.created_at', '>=', $filtro_fecha_inicio);
        }
        if ($filtro_fecha_fin) {
            $datos  =   $datos->whereDate('ev.created_at', '<=', $filtro_fecha_fin);
        }

        $datos  =   $datos->get();

        return $datos;
    }

    public function getPdf(Request $request)
    {
        $company       =   Empresa::find(1);
        $datos         =   $this->queryReporteEmbalaje($request);

        $cliente    =   null;
        if ($request->get('filtroCliente')) {
            $cliente = Cliente::findOrFail($request->get('filtroCliente'))->nombre;
        }

        $request->merge([
            'cliente'      =>  $cliente,
        ]);

        $pdf = Pdf::loadview('despachos.embalaje.reports.pdf', [
            'empresa'               =>  $company,
            'reporte'               =>  $datos,
            'filters'               =>  $request
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('reporte_embalaje' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }

    public function getExcel(Request $request)
    {
        ob_end_clean();
        ob_start();
        $company       =   Empresa::find(1);
        $datos         =   $this->queryReporteEmbalaje($request);

        $cliente    =   null;
        if ($request->get('filtroCliente')) {
            $cliente = Cliente::findOrFail($request->get('filtroCliente'))->nombre;
        }

        $request->merge([
            'cliente'      =>  $cliente,
        ]);

        return Excel::download(
            new EmbalajeExport($datos, $request, $company),
            'reporte_embalaje' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
