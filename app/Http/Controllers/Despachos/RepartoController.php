<?php

namespace App\Http\Controllers\Despachos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilidadesController;
use App\Http\Services\Despachos\Reparto\RepartoManager;
use App\Jobs\RepartoWsp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class RepartoController extends Controller
{
    private RepartoManager $s_reparto;
    public function __construct()
    {
        $this->s_reparto      =   new RepartoManager();
    }

    public function index()
    {
        $this->authorize('haveaccess', 'reparto.index');

        $cliente_varios     =   DB::select('SELECT c.id,c.nombre FROM clientes AS c WHERE c.id = 1');
        $departamentos      =   departamentos();
        $origenes_ventas    =   UtilidadesController::getOrigenesVentas();
        $tipos_pago_envio   =   UtilidadesController::getTiposPagoEnvio();
        $tipos_envio        =   UtilidadesController::getTiposEnvio();
        $tipos_documento    =   UtilidadesController::getTiposDocumento();

        return view(
            'despachos.reparto.index',
            compact('cliente_varios', 'departamentos', 'origenes_ventas', 'tipos_pago_envio', 'tipos_envio', 'tipos_documento')
        );
    }

    public function create()
    {
        $this->authorize('haveaccess', 'reparto.index');
        return view('despachos.reparto.create');
    }

    public function getRepartos(Request $request)
    {
        $fecha_inicio   =   $request->get('fecha_inicio');
        $fecha_fin      =   $request->get('fecha_fin');
        $estado         =   $request->get('estado');

        $query  =   DB::table('repartos as r')
            ->select(
                DB::raw("CONCAT('RT-', r.id) as codigo"),
                'r.id',
                'r.registrador_nombre',
                'r.estado',
                'r.observacion'
            )->where('r.sede_id',Auth::user()->sede_id);

        if ($fecha_inicio) {
            $query->whereDate('r.created_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('r.created_at', '<=', $fecha_fin);
        }
        if ($estado) {
            $query->where('r.estado', '=', $estado);
        }

        return DataTables::of($query)
            ->filterColumn('codigo', function ($query, $keyword) {
                $query->whereRaw("CONCAT('RT-', r.id) like ?", ["%{$keyword}%"]);
            })
            ->make(true);
    }

    public function pdfBultos($id, $nro_bultos)
    {
        try {
            $result = $this->s_reparto->pdfBultos($id, $nro_bultos);
            return $result['pdf']->stream($result['name']);
        } catch (Throwable $th) {
            return back()->with('message_error', 'Ocurrió un error al generar el PDF de bultos. ' . $th->getMessage());
        }
    }

    public function getPaquetesEmbaladosPendientes(Request $request)
    {
        $fecha_inicio   =   $request->get('fecha_inicio');
        $fecha_fin      =   $request->get('fecha_fin');
        $estado         =   $request->get('estado');
        $cliente_id     =   $request->get('cliente_id');
        $qr_codigo      =   $request->get('qr_codigo');

        $query  =   DB::table('paquetes_embalados as p')
            ->select(
                'p.id',
                'p.cliente_id',
                'p.cliente_nombre',
                'p.empresa_envio_id',
                'p.empresa_envio_nombre',
                'p.sede_envio_id',
                'p.sede_envio_nombre',
                'p.destinatario_tipo_doc',
                'p.destinatario_nro_doc',
                'p.destinatario_nombre',
                'p.registrador_id',
                'p.registrador_nombre',
                'p.qr_ruta',
                'p.estado',
                'p.qr_codigo'
            )->whereNull('p.reparto_id')
            ->where('p.sede_id',Auth::user()->sede_id);

        if ($fecha_inicio) {
            $query->whereDate('p.created_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('p.created_at', '<=', $fecha_fin);
        }
        if ($estado) {
            $query->where('p.estado', '=', $estado);
        }
        if ($cliente_id) {
            $query->where('p.cliente_id', '=', $cliente_id);
        }
        if ($qr_codigo) {
            $query->where('p.qr_codigo', $qr_codigo);
        }

        return DataTables::of($query)->make(true);
    }

/*
array:1 [
  "lstDetalleReparto" => "[{"id":1,"cliente_id":2,"cliente_nombre":"LUIS DANIEL ALVA LUJAN","empresa_envio_id":2,"empresa_envio_nombre":"EMTRAFESA","sede_envio_id":4,"sede_envio_nombre":"AV TUPAC AMARU 123","destinatario_tipo_doc":"DNI","destinatario_nro_doc":"75608753","destinatario_nombre":"LUIS DANIEL ALVA LUJAN","registrador_id":1,"registrador_nombre":"ADMINISTRADOR","qr_ruta":"storage/paquetes_embalados/qrs/qr_68a8ac6943754.svg","estado":"PENDIENTE"}]"
]
*/
    public function store(Request $request)
    {
        $this->authorize('haveaccess', 'reparto.index');

        DB::beginTransaction();
        try {

            $reparto    =   $this->s_reparto->store($request->toArray());

            Session::flash('message_success', 'Reparto registrado con éxito.');
            DB::commit();

            RepartoWsp::dispatch($reparto->id);

            return response()->json(['success' => true, 'message' => 'Reparto registrado con éxito.']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getMdlRShow(int $id)
    {
        try {
            $datos = $this->s_reparto->getMdlRShow($id);
            return response()->json(['success' => true, 'message' => 'Datos Obtenidos.', 'data' => $datos]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getEnviosPorPaquete(int $paquete_id)
    {
        try {
            $datos = $this->s_reparto->getEnviosPorPaquete($paquete_id);
            return response()->json(['success' => true, 'message' => 'Datos Obtenidos.', 'data' => $datos]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
