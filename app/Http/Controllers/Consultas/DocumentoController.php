<?php

namespace App\Http\Controllers\Consultas;

use App\Almacenes\Talla;
use App\Almacenes\LoteProducto;
use App\Almacenes\Producto;
use App\Exports\Consultas\Documentos\DocumentoExport;
use App\Exports\DocumentosExport;
use App\Exports\GuiaExport;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Persona\Persona;
use App\User;
use App\Ventas\Cliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use App\Ventas\Nota;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;
use Yajra\DataTables\Facades\DataTables;

class DocumentoController extends Controller
{
    public function index()
    {

        $usuarios   =   User::where('estado', 'ACTIVO')->get();

        return view('consultas.documentos.index', compact('usuarios'));
    }

    public function getTable(Request $request)
    {
        $ventas =   $this->queryCDocumentos($request);

        return DataTables::of($ventas)
            ->filterColumn('documento', function ($query, $keyword) {
                $query->whereRaw("CONCAT(cd.serie,'-', cd.correlativo) LIKE ?", ["%{$keyword}%"]);
            })
            ->make(true);
    }

    public function queryCDocumentos(Request $request)
    {
        $cliente_id     =   $request->get('cliente_id');
        $usuario_id     =   $request->get('usuario_id');
        $fecha_inicio   =   $request->get('fecha_inicio');
        $fecha_fin      =   $request->get('fecha_fin');

        $ventas =   DB::table('cotizacion_documento as cd')
            ->leftJoin('envios_ventas as ev', 'ev.documento_id', 'cd.id')
            ->leftJoin('cuenta_cliente as cc', 'cc.cotizacion_documento_id', 'cd.id')
            ->select(
                DB::raw('CONCAT(cd.serie,"-",cd.correlativo) as documento'),
                'cd.cliente',
                DB::raw('CONCAT("RE-",cd.pedido_id) as pedido'),
                'cd.total_pagar',
                'cc.saldo',
                'cd.registrador_nombre',
                'cd.created_at as fecha_registro',
                'cd.estado_despacho',
                'ev.usuario_embalaje',
                'ev.usuario_reparto',
                'ev.fecha_embalaje',
                'ev.fecha_reparto'
            );

        if ($cliente_id) {
            $ventas =   $ventas->where('cd.cliente_id', $cliente_id);
        }
        if ($usuario_id) {
            $ventas =   $ventas->where('cd.user_id', $usuario_id);
        }
        if ($fecha_inicio) {
            $ventas =   $ventas->whereDate('cd.created_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $ventas =   $ventas->whereDate('cd.created_at', '<=', $fecha_fin);
        }

        return $ventas;
    }

    public function getExcel(Request $request)
    {
        ob_end_clean();
        ob_start();
        $empresa          =   Empresa::find(1);
        $cdocumentos      =   $this->queryCDocumentos($request)->get();

        return Excel::download(new DocumentoExport($cdocumentos, $request, $empresa), 'cdocumentos' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }
}
