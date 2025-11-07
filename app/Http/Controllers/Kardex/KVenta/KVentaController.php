<?php

namespace App\Http\Controllers\Kardex\KVenta;

use App\Almacenes\Modelo;
use App\Almacenes\Talla;
use App\Exports\Kardex\Stock\KStockExport;
use App\Exports\Kardex\Venta\KVentaExport;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class KVentaController extends Controller
{
    public function index()
    {
        $modelos    =   Modelo::where('estado', 'ACTIVO')->get();
        $tallas     =   Talla::where('estado', 'ACTIVO')->get();
        return view('kardex.venta.index', compact('tallas', 'modelos'));
    }

    public function getKVenta(Request $request)
    {
        $k_stock = $this->queryKVenta($request);

        return DataTables::of($k_stock)
            ->make(true);
    }

    public function queryKVenta(Request $request)
    {
        $modelo_id = $request->get('modelo');
        $fecha_inicio = $request->get('fecha_inicio');
        $fecha_fin = $request->get('fecha_fin');
        $tallas = Talla::all();

        $selects = [
            'p.id as producto_id',
            'p.nombre as producto_nombre',
            'c.id as color_id',
            'c.descripcion as color_nombre',
            'ca.descripcion as categoria_nombre'
        ];

        foreach ($tallas as $talla) {
            $selects[] = DB::raw("
                CAST(SUM(CASE WHEN cdd.talla_id = {$talla->id} THEN cdd.cantidad ELSE 0 END) AS SIGNED) as talla_{$talla->id}
            ");
        }

        $query  =   DB::table('cotizacion_documento_detalles as cdd')
            ->join('cotizacion_documento as cd', 'cd.id', '=', 'cdd.documento_id')
            ->join('productos as p', 'p.id', '=', 'cdd.producto_id')
            ->join('modelos as m', 'm.id', '=', 'p.modelo_id')
            ->join('colores as c', 'c.id', '=', 'cdd.color_id')
            ->join('categorias as ca','ca.id','=','p.categoria_id')
            ->when($modelo_id, function ($q) use ($modelo_id) {
                return $q->where('m.id', $modelo_id);
            })
            ->where('m.estado', 'ACTIVO')
            ->where('p.estado', 'ACTIVO')
            ->where('cdd.almacen_id', 1)
            ->groupBy('p.id', 'p.nombre', 'c.id', 'c.descripcion','ca.descripcion')
            ->select($selects);

        if ($fecha_inicio) {
            $query->whereDate('cd.created_at', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('cd.created_at', '<=', $fecha_fin);
        }

        return $query;
    }

    public function excelKardexVenta(Request $request)
    {
        ob_end_clean(); // this
        ob_start(); // and this

        $empresa    =   Empresa::find(1);
        $k_stock    =   $this->queryKVenta($request)->get();
        $tallas     =   Talla::where('estado', 'ACTIVO')->get();

        $modelo_nombre = 'TODOS';
        if ($request->get('modelo')) {
            $modelo_nombre  =   Modelo::findOrFail($request->get('modelo'))->descripcion;
        }
        $request->merge(['modelo_nombre' => $modelo_nombre]);

        $export = new KVentaExport($k_stock, $request, $empresa, $tallas);
        return Excel::download($export, 'k_venta.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
