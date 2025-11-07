<?php

namespace App\Http\Controllers\Despachos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilidadesController;
use App\Http\Services\Despachos\RepartoDetalle\RepartoDetalleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class RepartoDetalleController extends Controller
{
    private RepartoDetalleManager $s_reparto_detalle;
    public function __construct()
    {
        $this->s_reparto_detalle      =   new RepartoDetalleManager();
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
            'despachos.reparto_detalle.index',
            compact('cliente_varios', 'departamentos', 'origenes_ventas', 'tipos_pago_envio', 'tipos_envio', 'tipos_documento')
        );
    }

    public function create()
    {
        $this->authorize('haveaccess', 'reparto.index');
        return view('despachos.reparto.create');
    }

    public function getRepartoDetalle(Request $request)
    {
        $fecha_inicio   =   $request->get('fecha_inicio');
        $fecha_fin      =   $request->get('fecha_fin');
        $estado         =   $request->get('estado');
        $cliente_id     =   $request->get('cliente_id');

        $query = DB::table('paquetes_embalados as p')
            ->leftJoin('repartos as r', 'p.reparto_id', '=', 'r.id')
            ->select(
                'p.id',
                'p.qr_codigo',
                'p.departamento_id',
                'p.provincia_id',
                'p.distrito_id',
                'p.departamento',
                'p.provincia',
                'p.distrito',
                'p.cliente_id',
                'p.cliente_nombre',
                'p.cliente_celular',
                'p.tipo_pago_envio_id',
                'p.tipo_pago_envio',
                'p.empresa_envio_id',
                'p.empresa_envio_nombre',
                'p.sede_envio_id',
                'p.sede_envio_nombre',
                'p.destinatario_tipo_doc',
                'p.destinatario_nro_doc',
                'p.destinatario_nombre',
                'p.entrega_domicilio',
                'p.direccion_entrega',
                'p.registrador_id',
                'p.registrador_nombre as usuario_embalaje',
                'p.qr_ruta',
                'p.qr_nombre',
                'p.estado',
                'p.created_at as fecha_embalaje',
                'p.reparto_id',
                'p.qr_codigo',
                'r.codigo as reparto_codigo',
                'r.registrador_nombre as usuario_reparto',
                'r.created_at as fecha_reparto',
                'p.guia_id',
                'p.guia_serie',
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(cd.serie,'-',cd.correlativo) SEPARATOR '| ')
                  FROM paquetes_embalados_detalle ped
                  JOIN envios_ventas ev ON ev.id = ped.envio_venta_id
                  JOIN cotizacion_documento cd ON cd.id = ev.documento_id
                  WHERE ped.paquete_embalado_id = p.id
                ) as ventas")
            )->where('p.sede_id',Auth::user()->sede_id);


        if ($fecha_inicio) {
            $query->whereDate('p.created_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('p.created_at', '<=', $fecha_fin);
        }
        if ($cliente_id) {
            $query->where('p.cliente_id', '=', $cliente_id);
        }
        // if ($estado) {
        //     $query->where('p.estado', '=', $estado);
        // }

        return DataTables::of($query)
            ->make(true);
    }

    public function pdfBultos($id, $nro_bultos)
    {
        try {
            $result = $this->s_reparto_detalle->pdfBultos($id, $nro_bultos);
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
}
