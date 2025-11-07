<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilidadesController;
use App\Http\Requests\Despachos\EnvioVenta\EnvioVentaStoreRequest;
use App\Http\Services\Ventas\Despacho\DespachoManager;
use App\Http\Services\Ventas\Ventas\VentaManager;
use App\Jobs\EnviarComprobanteWsp;
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
use Illuminate\Support\Facades\Auth;
use Throwable;

class DespachoController extends Controller
{
    private DespachoManager $s_despacho;

    public function __construct()
    {
        $this->s_despacho   =   new DespachoManager();
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
            'ventas.despachos.index',
            compact('cliente_varios', 'departamentos', 'origenes_ventas', 'tipos_pago_envio', 'tipos_envio', 'tipos_documento')
        );
    }

    public function indexEmbalaje()
    {
        $this->authorize('haveaccess', 'embalaje.index');

        $cliente_varios     =   DB::select('SELECT c.id,c.nombre FROM clientes AS c WHERE c.id = 1');
        $departamentos      =   departamentos();
        $origenes_ventas    =   UtilidadesController::getOrigenesVentas();
        $tipos_pago_envio   =   UtilidadesController::getTiposPagoEnvio();
        $tipos_envio        =   UtilidadesController::getTiposEnvio();
        $tipos_documento    =   UtilidadesController::getTiposDocumento();

        return view(
            'ventas.despachos.index_embalaje',
            compact('cliente_varios', 'departamentos', 'origenes_ventas', 'tipos_pago_envio', 'tipos_envio', 'tipos_documento')
        );
    }

    public function getEmbalaje(Request $request)
    {
        $fecha_inicio   =   $request->get('fecha_inicio');
        $fecha_fin      =   $request->get('fecha_fin');
        $estado         =   $request->get('estado');
        $cliente_id     =   $request->get('cliente_id');


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
                'ev.modo'
            )
            ->join('empresa_sedes as es', 'es.id', 'ev.sede_despachadora_id')
            ->join('empresa_sedes as eso', 'eso.id', 'ev.sede_id')
            ->whereIn('ev.estado', ['PENDIENTE', 'RESERVADO'])
            ->orderByDesc('ev.cliente_nombre', 'ev.id');

        if ($fecha_inicio) {
            $query->whereDate('ev.created_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('ev.created_at', '<=', $fecha_fin);
        }
        // if ($estado) {
        //     $query->where('ev.estado', '=', $estado);
        // }
        if ($cliente_id) {
            $query->where('ev.cliente_id', '=', $cliente_id);
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
                                        cdd.cantidad_sin_cambio
                                        from cotizacion_documento_detalles as cdd
                                        where cdd.documento_id=?', [$documento_id]);

            return response()->json(['success' => true, 'detalles_doc_venta' => $detalles_doc_venta, 'documento' => $documento]);
        } catch (\Throwable $th) {
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

    public function setEmbalaje(Request $request)
    {

        try {
            DB::beginTransaction();

            //======= ACTUALIZANDO DESPACHO ========
            DB::table('envios_ventas')
                ->where('id', $request->get('despacho_id'))
                ->where('documento_id', $request->get('documento_id'))
                ->update(['estado' => 'EMBALADO']);

            $venta                  =   Documento::findOrFail($request->get('documento_id'));
            $venta->estado_despacho = 'EMBALADO';
            $venta->update();

            DB::commit();

            return response()->json(['success' => true, 'message' => "ENVÍO EMBALADO"]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => "ERROR EN EL SERVIDOR",
                'exception' => $th->getMessage()
            ]);
        }
    }

    public function setReserva(Request $request)
    {
        try {
            DB::beginTransaction();
            //======= ACTUALIZANDO DESPACHO ========
            DB::table('envios_ventas')
                ->where('id', $request->get('despacho_id'))
                ->where('documento_id', $request->get('documento_id'))
                ->update(['estado' => 'RESERVADO']);

            $venta                  =   Documento::findOrFail($request->get('documento_id'));
            $venta->estado_despacho = 'RESERVADO';
            $venta->update();
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
            $venta      =   Documento::findOrFail($documento_id);
            $cliente    =   Cliente::findOrFail($venta->cliente_id);
            return response()->json(['success' => true, 'despacho' => $despacho,'cliente'=>$cliente]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
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
    public function store(EnvioVentaStoreRequest $request)
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
    public function updateDespacho(EnvioVentaStoreRequest $request)
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
}
