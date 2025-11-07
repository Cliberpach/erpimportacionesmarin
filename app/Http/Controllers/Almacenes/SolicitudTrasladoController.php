<?php

namespace App\Http\Controllers\Almacenes;

use App\Almacenes\Almacen;
use App\Almacenes\Kardex;
use App\Almacenes\ProductoColor;
use App\Almacenes\ProductoColorTalla;
use App\Almacenes\Talla;
use App\Almacenes\TrasladoDetalle;
use App\Exports\Almacenes\Traslados\TrasladoExport;
use App\Http\Controllers\Controller;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Sedes\Sede;
use App\Models\Almacenes\Traslados\Traslado;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Session;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;

class SolicitudTrasladoController extends Controller
{
    public function index()
    {
        return view('almacenes.solicitudes_traslado.index');
    }

    public function getSolicitudesTraslado(Request $request)
    {

        $traslados  =   $this->queryGetSolicitudesTraslados($request);

        return DataTables::of($traslados)->make(true);
    }

    public function queryGetSolicitudesTraslados(Request $request)
    {
        $filtro_estado  =   $request->get('estado');
        $sede_id        =   Auth::user()->sede_id;
        $filtro_producto    =   $request->get('producto_id');
        $id                 =   $request->get('id');

        $traslados  =   DB::table('traslados as t')
            ->join('almacenes as ao', 'ao.id', '=', 't.almacen_origen_id')
            ->join('almacenes as ad', 'ad.id', '=', 't.almacen_destino_id')
            ->join('empresa_sedes as eso', 'eso.id', '=', 't.sede_origen_id')
            ->join('empresa_sedes as esd', 'esd.id', '=', 't.sede_destino_id')
            ->select(
                DB::raw('CONCAT("TR-", t.id) as simbolo'),
                't.id',
                'ao.descripcion as almacen_origen_nombre',
                'ad.descripcion as almacen_destino_nombre',
                't.observacion',
                'eso.direccion as sede_origen_direccion',
                'esd.direccion as sede_destino_direccion',
                't.created_at as fecha_registro',
                't.fecha_traslado',
                't.registrador_nombre',
                't.estado',
                't.venta_serie'
            )
            ->where('t.sede_destino_id', $sede_id);

        if ($filtro_estado) {
            $traslados  =   $traslados->where('t.estado', $filtro_estado);
        }
        if ($filtro_producto) {
            $traslados->whereExists(function ($q) use ($filtro_producto) {
                $q->select(DB::raw(1))
                    ->from('traslados_detalle as td')
                    ->whereRaw('td.traslado_id = t.id');

                if ($filtro_producto) {
                    $q->where('td.producto_id', $filtro_producto);
                }

            });
        }
        if ($id) {
            $traslados  =   $traslados->where('t.id', $id);
        }

        return $traslados;
    }

    public function queryGetSolTrasladosDetalles(Request $request)
    {
        $traslados  =   $this->queryGetSolicitudesTraslados($request);

        $traslados->join('traslados_detalle as td', 'td.traslado_id', 't.id')
            ->select(
                DB::raw('CONCAT("TR-", t.id) as simbolo'),
                'ao.descripcion as almacen_origen_nombre',
                'ad.descripcion as almacen_destino_nombre',
                't.observacion',
                'eso.direccion as sede_origen_direccion',
                'esd.direccion as sede_destino_direccion',
                'td.producto_nombre',
                'td.color_nombre',
                'td.talla_nombre',
                'td.cantidad',
                't.estado'
            );

        return $traslados;
    }

    public function confirmarShow($id)
    {

        $traslado   =   Traslado::find($id);
        $detalle    =   TrasladoDetalle::where('traslado_id', $id)->get();
        $tallas     =   Talla::where('estado', 'ACTIVO')->get();
        $almacen_origen     =   Almacen::find($traslado->almacen_origen_id);
        $almacen_destino    =   Almacen::find($traslado->almacen_destino_id);

        return view(
            'almacenes.solicitudes_traslado.confirmar',
            compact('tallas', 'detalle', 'traslado', 'almacen_origen', 'almacen_destino')
        );
    }

    /*
array:1 [
  "traslado_id" => "1"
]
*/
    public function confirmarStore(Request $request)
    {

        DB::beginTransaction();
        try {

            //======== VALIDACIONES PREVIAS =====
            if (!$request->get('traslado_id')) {
                throw new Exception("FALTA EL PARÁMETRO TRASLADO ID EN LA PETICIÓN!!!");
            }
            $traslado   =   Traslado::findOrFail($request->get('traslado_id'));

            if (!$traslado) {
                throw new Exception("NO EXISTE EL TRASLADO EN LA BD!!!");
            }
            if ($traslado->estado !== 'ENVIADO') {
                throw new Exception("EL TRASLADO SE ENCUENTRA CON ESTADO " . $traslado->estado . '!!!');
            }
            if ($traslado->estado === 'RECIBIDO') {
                throw new Exception("EL TRASLADO YA SE ENCUENTRA CON ESTADO " . $traslado->estado . '!!!');
            }


            //======== INCREMENTANDO STOCK EN ALMACÉN DESTINO ========
            $detalle    =   TrasladoDetalle::where('traslado_id', $traslado->id)->get();
            if (count($detalle) === 0) {
                throw new Exception("EL DETALLE DEL TRASLADO ESTÁ VACÍO!!!");
            }

            $almacen_destino    =   Almacen::find($traslado->almacen_destino_id);

            if (!$traslado->venta_id) {
                foreach ($detalle as  $item) {

                    //====== VERIFICAR EXISTENCIA DEL PRODUCTO EN EL ALMACÉN DESTINO =====
                    $producto_destino   =   DB::select(
                        'select
                                        pct.*
                                        from producto_color_tallas as pct
                                        where
                                        pct.almacen_id  = ?
                                        and pct.producto_id = ?
                                        and pct.color_id = ?
                                        and pct.talla_id = ?',
                        [
                            $traslado->almacen_destino_id,
                            $item->producto_id,
                            $item->color_id,
                            $item->talla_id
                        ]
                    );

                    //======== TALLA EXISTE, INCREMENTAR STOCK =========
                    if (count($producto_destino) > 0) {

                        ProductoColorTalla::where('producto_id', $item->producto_id)
                            ->where('color_id', $item->color_id)
                            ->where('talla_id', $item->talla_id)
                            ->where('almacen_id', $traslado->almacen_destino_id)
                            ->update([
                                'stock'         =>  DB::raw("stock + $item->cantidad"),
                                'stock_logico'  =>  DB::raw("stock_logico + $item->cantidad"),
                                'estado'        =>  '1',
                            ]);
                    } else {

                        //========= TALLA NO EXISTE =============

                        //======= VERIFICANDO EXISTENCIA DEL COLOR ======
                        $existeColor    =   ProductoColor::where('producto_id', $item->producto_id)
                            ->where('color_id', $item->color_id)
                            ->where('almacen_id', $traslado->almacen_destino_id)
                            ->exists();

                        //======== COLOR NO EXISTE, REGISTRAR COLOR =======
                        if (!$existeColor) {
                            $producto_color                 =   new ProductoColor();
                            $producto_color->producto_id    =   $item->producto_id;
                            $producto_color->color_id       =   $item->color_id;
                            $producto_color->almacen_id     =   $traslado->almacen_destino_id;
                            $producto_color->save();
                        }

                        //====== REGISTRAR TALLA ============
                        $producto                   =   new ProductoColorTalla();
                        $producto->producto_id      =   $item->producto_id;
                        $producto->color_id         =   $item->color_id;
                        $producto->talla_id         =   $item->talla_id;
                        $producto->stock            =   $item->cantidad;
                        $producto->stock_logico     =   $item->cantidad;
                        $producto->almacen_id       =   $traslado->almacen_destino_id;
                        $producto->save();
                    }

                    //========== REGISTRANDO EN KARDEX ========
                    //=========== OBTENIENDO PRODUCTO CON STOCK NUEVO ===========
                    $producto   =   DB::select(
                        'select
                                pct.*
                                from producto_color_tallas as pct
                                where
                                pct.producto_id = ?
                                and pct.color_id = ?
                                and pct.talla_id = ?
                                and pct.almacen_id = ?',
                        [
                            $item->producto_id,
                            $item->color_id,
                            $item->talla_id,
                            $traslado->almacen_destino_id
                        ]
                    );

                    //==================== KARDEX ==================
                    $kardex                     =   new Kardex();
                    $kardex->sede_id            =   $traslado->sede_destino_id;
                    $kardex->almacen_id         =   $traslado->almacen_destino_id;
                    $kardex->producto_id        =   $item->producto_id;
                    $kardex->color_id           =   $item->color_id;
                    $kardex->talla_id           =   $item->talla_id;
                    $kardex->almacen_nombre     =   $almacen_destino->descripcion;
                    $kardex->producto_nombre    =   $item->producto_nombre;
                    $kardex->color_nombre       =   $item->color_nombre;
                    $kardex->talla_nombre       =   $item->talla_nombre;
                    $kardex->cantidad           =   $item->cantidad;
                    $kardex->precio             =   null;
                    $kardex->importe            =   null;
                    $kardex->accion             =   'TRASLADO INGRESO';
                    $kardex->stock              =   $producto[0]->stock;
                    $kardex->numero_doc         =   'TR-' . $traslado->id;
                    $kardex->documento_id       =   $traslado->id;
                    $kardex->registrador_id     =   Auth::user()->id;
                    $kardex->registrador_nombre =   Auth::user()->usuario;
                    $kardex->fecha              =   Carbon::today()->toDateString();
                    $kardex->descripcion        =   mb_strtoupper("TRASLADO INGRESO", 'UTF-8');
                    $kardex->save();
                }
            }

            $traslado->estado           =   'RECIBIDO';
            $traslado->aprobador_id     =   Auth::user()->id;
            $traslado->aprobador_nombre =   Auth::user()->usuario;
            $traslado->fecha_aprobacion =   now();
            $traslado->save();

            Session::flash('message_success', 'TRASLADO RECIBIDO CON ÉXITO');

            DB::commit();
            return response()->json(['success' => true, 'message' => "TRASLADO RECIBIDO CON ÉXITO!!!"]);
        } catch (Throwable $th) {
            DB::rollBack();
            Session::flash('message_success', $th->getMessage());
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function entregarShow($id)
    {

        $traslado   =   Traslado::find($id);
        $detalle    =   TrasladoDetalle::where('traslado_id', $id)->get();
        $tallas     =   Talla::where('estado', 'ACTIVO')->get();
        $almacen_origen     =   Almacen::find($traslado->almacen_origen_id);
        $almacen_destino    =   Almacen::find($traslado->almacen_destino_id);

        return view(
            'almacenes.solicitudes_traslado.entregar',
            compact('tallas', 'detalle', 'traslado', 'almacen_origen', 'almacen_destino')
        );
    }

    /*
array:1 [
  "traslado_id" => "1"
]
*/
    public function entregarStore(Request $request)
    {
        DB::beginTransaction();
        try {

            //======== VALIDACIONES PREVIAS =====
            if (!$request->get('traslado_id')) {
                throw new Exception("FALTA EL PARÁMETRO TRASLADO ID EN LA PETICIÓN!!!");
            }
            $traslado   =   Traslado::findOrFail($request->get('traslado_id'));

            if (!$traslado) {
                throw new Exception("NO EXISTE EL TRASLADO EN LA BD!!!");
            }
            if ($traslado->estado !== 'RECIBIDO') {
                throw new Exception("EL TRASLADO SE ENCUENTRA CON ESTADO " . $traslado->estado . '!!!');
            }
            if ($traslado->estado === 'ENTREGADO') {
                throw new Exception("TRASLADO TR-" . $request->get('traslado_id') . " YA FUE ENTREGADO AL CLIENTE");
            }

            $traslado->estado                   =   'ENTREGADO';
            $traslado->usuario_entrega_id       =   Auth::user()->id;
            $traslado->usuario_entrega_nombre   =   Auth::user()->usuario;
            $traslado->fecha_entrega            =   now();
            $traslado->update();

            DB::commit();

            Session::flash('message_success', 'TRASLADO ENTREGADO CON ÉXITO');

            return response()->json(['success' => true, 'message' => "ENTREGADO CON ÉXITO!!!"]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function show($traslado_id)
    {

        $detalle            =   TrasladoDetalle::where('traslado_id', $traslado_id)->where('tipo','PRODUCTO')->get();
        $traslado           =   Traslado::find($traslado_id);
        $tallas             =   Talla::where('estado', 'ACTIVO')->get();
        $almacen_origen     =   Almacen::find($traslado->almacen_origen_id);
        $almacen_destino    =   Almacen::find($traslado->almacen_destino_id);

        return view('almacenes.solicitudes_traslado.show', compact('detalle', 'traslado', 'tallas', 'almacen_origen', 'almacen_destino'));
    }

    public function generarEtiquetas($id)
    {

        try {

            $traslado_detalle   =   DB::select(
                'select
                                    p.nombre as producto_nombre,
                                    c.descripcion as color_nombre,
                                    t.descripcion as talla_nombre,
                                    m.descripcion as modelo_nombre,
                                    cb.ruta_cod_barras,
                                    td.cantidad,
                                    p.id as producto_id,
                                    c.id as color_id,
                                    t.id as talla_id,
                                    m.id as modelo_id,
                                    ca.descripcion as categoria_nombre
                                    from traslados_detalle as td
                                    inner join productos as p on p.id = td.producto_id
                                    inner join colores as c on c.id = td.color_id
                                    inner join tallas as t on t.id = td.talla_id
                                    inner join modelos as m on m.id = p.modelo_id
                                    inner join categorias as ca on ca.id = p.categoria_id
                                    left join codigos_barra as cb on (cb.producto_id = p.id and cb.color_id = c.id and cb.talla_id = t.id)
                                    where td.traslado_id = ?',
                [$id]
            );


            $empresa        =   Empresa::first();


            $width_in_points    = 300 * 72 / 25.4;  // Ancho en puntos 5cm = 50 mm
            $height_in_points   = 170 * 72 / 25.4; // Alto en puntos

            // Establecer el tamaño del papel
            $custom_paper = array(0, 0, $width_in_points, $height_in_points);
            $pdf = PDF::loadview('almacenes.productos.pdf.adhesivo', [
                'nota_id'       =>  $id,
                'nota_detalle'  =>  $traslado_detalle,
                'empresa'       =>  $empresa
            ])->setPaper($custom_paper)
                ->setWarnings(false);

            return $pdf->stream('etiquetas.pdf');
        } catch (\Throwable $th) {
            dd($th->getMessage());


            return redirect()->route('almacenes.traslados.index');
        }
    }

      public function excel(Request $request)
    {
        ob_end_clean();
        ob_start();
        $company    =   Empresa::find(1);
        $traslados  =   $this->queryGetSolTrasladosDetalles($request)->get();

        // $request->merge([
        //     'banco_nombre'      =>  $cuenta_bancaria->banco_nombre,
        //     'nro_cuenta'        =>  $cuenta_bancaria->nro_cuenta,
        //     'moneda'            =>  $cuenta_bancaria->moneda
        // ]);


        return Excel::download(
            new TrasladoExport($traslados, $request, $company),
            'reporte_traslados' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function pdf(Request $request)
    {

        $company    =   Empresa::find(1);
        $traslados  =   $this->queryGetSolTrasladosDetalles($request)->get();

        // $request->merge([
        //     'banco_nombre'      =>  $cuenta_bancaria->banco_nombre,
        //     'nro_cuenta'        =>  $cuenta_bancaria->nro_cuenta,
        //     'moneda'            =>  $cuenta_bancaria->moneda
        // ]);

        $pdf = Pdf::loadview('almacenes.traslados.reports.pdf', [
            'empresa'               =>  $company,
            'reporte'               =>  $traslados,
            'filters'               =>  $request

        ])->setPaper('a4', 'landscape');

        return $pdf->stream('reporte_traslados' . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }

     public function pdfOne(int $id)
    {
        $company    =   Empresa::find(1);

        $request    =   new Request();
        $request->merge(['id' => $id]);

        $traslado   =   Traslado::findOrFail($id);
        $detalle    =   $this->queryGetSolTrasladosDetalles($request)->get();
        $almacen_origen     =   Almacen::findOrFail($traslado->almacen_origen_id)->descripcion;
        $almacen_destino    =   Almacen::findOrFail($traslado->almacen_destino_id)->descripcion;
        $sede_origen        =   Sede::findOrFail($traslado->sede_origen_id)->nombre;
        $sede_destino       =   Sede::findOrFail($traslado->sede_destino_id)->nombre;

        $request->merge([
            'codigo'            =>  'TR-' . $traslado->id,
            'almacen_origen'    =>  $almacen_origen,
            'almacen_destino'   =>  $almacen_destino,
            'sede_origen'       =>  $sede_origen,
            'sede_destino'      =>  $sede_destino
        ]);

        $pdf = Pdf::loadview('almacenes.traslados.reports.pdf', [
            'empresa'               =>  $company,
            'reporte'               =>  $detalle,
            'filters'               =>  $request

        ])->setPaper('a4', 'landscape');

        return $pdf->stream('TR-' . $traslado->id . Carbon::now()->format('Y_m_d_H_i_s') . '.pdf');
    }
}
