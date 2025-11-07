<?php

namespace App\Http\Controllers\Ventas;

use App\Almacenes\Almacen;
use App\Almacenes\Categoria;
use App\Almacenes\LoteProducto;
use App\Almacenes\Marca;
use App\Almacenes\Modelo;
use App\Almacenes\Talla;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilidadesController;
use App\Http\Requests\Ventas\Cotizacion\CotizacionADocVentaRequest;
use App\Http\Requests\Ventas\Cotizacion\CotizacionStoreRequest;
use App\Http\Services\Ventas\Cotizaciones\CotizacionManager;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Sedes\Sede;
use App\Ventas\Cliente;
use App\Ventas\Cotizacion;
use App\Ventas\CotizacionDetalle;
use App\Ventas\Documento\Documento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\User;
use Exception;
use Illuminate\Contracts\View\View;
use Throwable;

class CotizacionController extends Controller
{
    private CotizacionManager $s_cotizacion;

    public function __construct()
    {
        $this->s_cotizacion =   new CotizacionManager();
    }

    public function index()
    {

        return view('ventas.cotizaciones.index');
    }

    public function getCotizaciones(Request $request)
    {
        $query = DB::table('cotizaciones as co')
            ->select(
                DB::raw('CONCAT("CO-",co.id) as simbolo'),
                'co.id',
                'co.almacen_nombre',
                'co.cliente_nombre as cliente',
                'co.created_at',
                'co.registrador_nombre',
                'co.total_pagar',
                'co.estado',
                'co.created_at',
                'co.telefono',
                DB::raw('IF(co.venta_id IS NULL, "-", CONCAT(co.venta_serie,"-",co.venta_correlativo)) as documento'),
                DB::raw('IF(co.pedido_id IS NULL, "-", CONCAT("RE-", co.pedido_id)) as pedido_id'),
            )
            ->where('co.estado', '<>', 'ANULADO');

        $roles = DB::table('role_user as rl')
            ->join('roles as r', 'r.id', '=', 'rl.role_id')
            ->where('rl.user_id', Auth::user()->id)
            ->pluck('r.name')
            ->toArray();

        //======== ADMIN PUEDE VER TODAS LAS COTIZACIONES DE SU SEDE =====
        if (in_array('ADMIN', $roles)) {
            $query->where('co.sede_id', Auth::user()->sede_id);
        } else {

            //====== USUARIOS PUEDEN VER SOLO SUS PROPIAS COTIZACIONES ======
            $query->where('co.sede_id', Auth::user()->sede_id)
                ->where('co.registrador_id', Auth::user()->id);
        }

        return DataTables::of($query)
            ->filterColumn('documento', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereRaw("IF(co.venta_id IS NULL, '-', CONCAT(co.venta_serie,'-',co.venta_correlativo)) LIKE ?", ["%{$keyword}%"])
                        ->orWhereRaw("IF(co.pedido_id IS NULL, '-', CONCAT('PE-', co.pedido_id)) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->filterColumn('pedido_id', function ($query, $keyword) {
                $query->whereRaw("IF(co.pedido_id IS NULL, '-', CONCAT('PE-', co.pedido_id)) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('simbolo', function ($query, $keyword) {
                $query->whereRaw("CONCAT('CO-', co.id) LIKE ?", ["%{$keyword}%"]);
            })
            ->make(true);
    }

    public function create()
    {
        $tipos_documento    =   tipos_documento();
        $departamentos      =   departamentos();
        $tipo_clientes      =   tipo_clientes();

        $condiciones        =   Condicion::where('estado', 'ACTIVO')->get();
        $modelos            =   Modelo::where('estado', 'ACTIVO')->get();
        $categorias         =   Categoria::where('estado', 'ACTIVO')->get();
        $marcas             =   Marca::where('estado', 'ACTIVO')->get();
        $tallas             =   Talla::where('estado', 'ACTIVO')->get();

        $registrador        =   Auth::user();

        $sede_id            =   Auth::user()->sede_id;
        $sede               =   Sede::find($sede_id);

        $almacenes          =   Almacen::where('estado', 'ACTIVO')->where('tipo_almacen', 'PRINCIPAL')->get();
        $porcentaje_igv     =   Empresa::find(1)->igv;
        $origenes_ventas    =   UtilidadesController::getOrigenesVentas();
        $cliente_varios     =   Cliente::first();

        return view(
            'ventas.cotizaciones.create',
            compact(
                'tallas',
                'modelos',
                'condiciones',
                'tipos_documento',
                'departamentos',
                'tipo_clientes',
                'categorias',
                'marcas',
                'registrador',
                'almacenes',
                'sede_id',
                'porcentaje_igv',
                'sede',
                'origenes_ventas',
                'cliente_varios'
            )
        );
    }


    /*
array:10 [
  "_token"              => "XCcigubfOivrL6d1gCUmLO5X52q2rCU2nt6Xk2vN"
  "registrador"         => "ADMINISTRADOR"
  "fecha_registro"      => "2025-02-05"
  "almacen"             => "1"
  "condicion_id"        => "1"
  "cliente"             => "1"
  "lstCotizacion"       => "[{"producto_id":"1","color_id":"1","producto_nombre":"PRODUCTO TEST","color_nombre":"BLANCO","precio_venta":"1.00","monto_descuento":0,"porcentaje_descuento":0,"precio_venta_nuevo":0,"subtotal_nuevo":0,"tallas":[{"talla_id":"1","talla_nombre":"34","cantidad":"10"}],"subtotal":10},{"producto_id":"1","color_id":"2","producto_nombre":"PRODUCTO TEST","color_nombre":"AZUL","precio_venta":"1.00","monto_descuento":0,"porcentaje_descuento":0,"precio_venta_nuevo":0,"subtotal_nuevo":0,"tallas":[{"talla_id":"1","talla_nombre":"34","cantidad":"20"}],"subtotal":20},{"producto_id":"1","color_id":"3","producto_nombre":"PRODUCTO TEST","color_nombre":"CELESTE","precio_venta":"1.00","monto_descuento":0,"porcentaje_descuento":0,"precio_venta_nuevo":0,"subtotal_nuevo":0,"tallas":[{"talla_id":"1","talla_nombre":"34","cantidad":"30"}],"subtotal":30},{"producto_id":"1","color_id":"4","producto_nombre":"PRODUCTO TEST","color_nombre":"PLOMO","precio_venta":"1.00","monto_descuento":0,"porcentaje_descuento":0,"precio_venta_nuevo":0,"subtotal_nuevo":0,"tallas":[{"talla_id":"1","talla_nombre":"34","cantidad":"40"}],"subtotal":40}]"
  "sede_id"             => "1"
  "registrador_id"      => "1"
  "porcentaje_igv"      => "18.00"
  "montos_cotizacion"   =>
                            "{"subtotal":"100.00","embalaje":"11.00","envio":"12.00",
                            "total":"104.24","igv":"18.76",
                            "totalPagar":"123.00","monto_descuento":"0.00"}"
]
*/
    public function store(CotizacionStoreRequest $request)
    {
        DB::beginTransaction();
        try {

            $cotizacion = $this->s_cotizacion->store($request->toArray());

            //Registro de actividad
            $descripcion = "SE AGREGÓ LA COTIZACION CON LA FECHA: " . Carbon::parse($cotizacion->fecha_documento)->format('d/m/y');
            $gestion = "COTIZACION";
            crearRegistro($cotizacion, $descripcion, $gestion);

            Session::flash('message_success', 'COTIZACIÓN REGISTRADA CON ÉXITO');
            DB::commit();
            return response()->json(['success' => true, 'message' => "COTIZACIÓN REGISTRADA CON ÉXITO"]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function edit($id)
    {
        //=========== SI LA COTIZACIÓN TIENE UN DOC DE VENTA, YA NO PUEDE MODIFICARSE =========
        $exists_doc_venta   =   DB::table('cotizacion_documento')->where('cotizacion_venta', $id)->exists();
        if ($exists_doc_venta) {
            Session::flash('error', 'NO PUEDE MODIFICAR UNA COTIZACIÓN QUE TIENE UN DOCUMENTO DE VENTA GENERADO');
            return redirect()->back();
        }

        $tipos_documento    =   tipos_documento();
        $departamentos      =   departamentos();
        $tipo_clientes      =   tipo_clientes();

        $cotizacion         =   Cotizacion::findOrFail($id);
        $empresas           =   Empresa::where('estado', 'ACTIVO')->get();
        $condiciones        =   Condicion::where('estado', 'ACTIVO')->get();
        $detalles           =   CotizacionDetalle::where('cotizacion_id', $id)->where('estado', 'ACTIVO')
            ->where('tipo', 'PRODUCTO')
            ->with('producto', 'color', 'talla')->get();

        $modelos            =   Modelo::where('estado', 'ACTIVO')->get();
        $categorias         =   Categoria::where('estado', 'ACTIVO')->get();
        $marcas             =   Marca::where('estado', 'ACTIVO')->get();
        $tallas             =   Talla::where('estado', 'ACTIVO')->get();
        $porcentaje_igv     =   Empresa::find(1)->igv;

        $registrador        =   User::find($cotizacion->registrador_id);
        $almacenes          =   Almacen::where('estado', 'ACTIVO')->where('tipo_almacen', 'PRINCIPAL')->get();
        $sede_id            =   Auth::user()->sede_id;
        $sede               =   Sede::find($sede_id);
        $cliente            =   Cliente::findOrFail($cotizacion->cliente_id);
        $origenes_ventas    =   UtilidadesController::getOrigenesVentas();

        return view('ventas.cotizaciones.edit', [
            'cotizacion'        =>  $cotizacion,
            'empresas'          =>  $empresas,
            'condiciones'       =>  $condiciones,
            'detalles'          =>  $detalles,
            'modelos'           =>  $modelos,
            'categorias'        =>  $categorias,
            'marcas'            =>  $marcas,
            'tallas'            =>  $tallas,
            'tipos_documento'   =>  $tipos_documento,
            'departamentos'     =>  $departamentos,
            'tipo_clientes'     =>  $tipo_clientes,
            'porcentaje_igv'    =>  $porcentaje_igv,
            'registrador'       =>  $registrador,
            'almacenes'         =>  $almacenes,
            'sede_id'           =>  $sede_id,
            'sede'              =>  $sede,
            'cliente'           =>  $cliente,
            'origenes_ventas'   =>  $origenes_ventas
        ]);
    }

    /*
array:11 [
  "_token"              => "tUfxjoYQNI8rXMfIXQtSNELqv4znU9yyUA5PT9hD"
  "registrador"         => "ADMINISTRADOR"
  "fecha_registro"      => "2025-02-10"
  "almacen"             => "2"
  "condicion_id"        => "1"
  "cliente"             => "1"
  "lstCotizacion"       => "[{"producto_id":1,"color_id":3,"talla_id":1,"cantidad":8,"precio_venta":"1.00","porcentaje_descuento":0,"precio_venta_nuevo":0},{"producto_id":1,"color_id":3,"talla_id":1,"cantidad":8,"precio_venta":"1.00","porcentaje_descuento":0,"precio_venta_nuevo":0},{"producto_id":1,"color_id":3,"talla_id":1,"cantidad":8,"precio_venta":"1.00","porcentaje_descuento":0,"precio_venta_nuevo":0},{"producto_id":1,"color_id":3,"talla_id":1,"cantidad":8,"precio_venta":"1.00","porcentaje_descuento":0,"precio_venta_nuevo":0}]"
  "sede_id"             => "1"
  "registrador_id"      => "1"
  "montos_cotizacion"   => "{"subtotal":"16.00","embalaje":"0.00","envio":"0.00","total":"13.56","igv":"2.44","totalPagar":"16.00","monto_descuento":"0.00"}"
  "porcentaje_igv"      => "18.00"
]
*/
    public function update(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            $cotizacion =   $this->s_cotizacion->update($request->toArray(), $id);

            //Registro de actividad
            $descripcion = "SE MODIFICÓ LA COTIZACION CON LA FECHA: " . Carbon::parse($cotizacion->fecha_documento)->format('d/m/y');
            $gestion    = "COTIZACION";
            modificarRegistro($cotizacion, $descripcion, $gestion);

            Session::flash('success', 'Cotización modificada.');

            DB::commit();
            return response()->json(['success' => true, 'message' => "COTIZACIÓN ACTUALIZADA"]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function show($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $nombre_completo = $cotizacion->user->empleado->persona->apellido_paterno . ' ' . $cotizacion->user->empleado->persona->apellido_materno . ' ' . $cotizacion->user->empleado->persona->nombres;
        $presentaciones = presentaciones();
        $detalles = CotizacionDetalle::where('cotizacion_id', $id)->where('estado', 'ACTIVO')->get();
        $condiciones = Condicion::where('estado', 'ACTIVO')->get();

        return view('ventas.cotizaciones.show', [
            'cotizacion' => $cotizacion,
            'detalles' => $detalles,
            'presentaciones' => $presentaciones,
            'condiciones' => $condiciones,
            'nombre_completo' => $nombre_completo
        ]);
    }

    public function destroy($id)
    {

        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->estado = "ANULADO";
        $cotizacion->update();

        $cotizacion_detalle = CotizacionDetalle::where('cotizacion_id', $id)->get();
        foreach ($cotizacion_detalle as $detalle) {
            $detalle->estado = "ANULADO";
            $detalle->update();
        }

        //Registro de actividad
        $descripcion = "SE ELIMINÓ LA COTIZACION CON LA FECHA: " . Carbon::parse($cotizacion->fecha_documento)->format('d/m/y');
        $gestion = "COTIZACION";
        eliminarRegistro($cotizacion, $descripcion, $gestion);

        Session::flash('success', 'Cotización eliminada.');
        return redirect()->route('ventas.cotizacion.index')->with('eliminar', 'success');
    }

    public function email($id)
    {

        $cotizacion = Cotizacion::findOrFail($id);
        $nombre_completo = $cotizacion->user->empleado->persona->apellido_paterno . ' ' . $cotizacion->user->empleado->persona->apellido_materno . ' ' . $cotizacion->user->empleado->persona->nombres;
        $igv = '';
        $tipo_moneda = '';
        $detalles = $cotizacion->detalles->where('estado', 'ACTIVO');


        // $presentaciones = presentaciones();
        $paper_size = array(0, 0, 360, 360);
        $pdf = PDF::loadview('ventas.cotizaciones.reportes.detalle', [
            'cotizacion' => $cotizacion,
            'nombre_completo' => $nombre_completo,
            'detalles' => $detalles,
        ])->setPaper('a4')->setWarnings(false);

        Mail::send('email.cotizacion', compact("cotizacion"), function ($mail) use ($pdf, $cotizacion) {
            $mail->to($cotizacion->cliente->correo_electronico);
            $mail->subject('COTIZACION OC-0' . $cotizacion->id);
            $mail->attachdata($pdf->output(), 'COTIZACION CO-0' . $cotizacion->id . '.pdf');
        });

        Session::flash('success', 'Cotización enviado al correo ' . $cotizacion->cliente->correo_electronico);
        return redirect()->route('ventas.cotizacion.show', $cotizacion->id)->with('enviar', 'success');
    }

    public function report($id)
    {
        $cotizacion         = Cotizacion::findOrFail($id);
        $sede               = Sede::find($cotizacion->sede_id);
        $tallas             = Talla::all();
        $nombre_completo    = $cotizacion->registrador_nombre;
        $vendedor_nombre    = $nombre_completo;
        $detalles           = $cotizacion->detalles->where('estado', 'ACTIVO')->where('tipo', 'PRODUCTO');
        $empresa            = Empresa::first();

        $mostrar_cuentas =   DB::select('SELECT
                                c.propiedad
                                from configuracion as c
                                where c.slug = "MCB"')[0]->propiedad;

        $detalles = $this->formatearArrayDetalle($detalles);


        $pdf = PDF::loadview('ventas.cotizaciones.reportes.detalle_nuevo', [
            'cotizacion'        => $cotizacion,
            'nombre_completo'   => $nombre_completo,
            'detalles'          => $detalles,
            'empresa'           => $empresa,
            'tallas'            => $tallas,
            'vendedor_nombre'   => $vendedor_nombre,
            'mostrar_cuentas'   => $mostrar_cuentas,
            'sede'              => $sede
        ])->setPaper('a4')->setWarnings(false);
        return $pdf->stream('CO-' . $cotizacion->id . '.pdf');
    }

    public function formatearArrayDetalle($detalles)
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

                $producto['producto_codigo']        =   $detalle->producto->codigo;
                $producto['producto_id']            =   $detalle->producto_id;
                $producto['color_id']               =   $detalle->color_id;
                $producto['producto_nombre']        =   $detalle->producto->nombre;
                $producto['color_nombre']           =   $detalle->color->descripcion;
                $producto['modelo_nombre']          =   $detalle->producto->modelo->descripcion;
                $producto['precio_unitario']        =   $detalle->precio_unitario;
                $producto['porcentaje_descuento']   =   $detalle->porcentaje_descuento;
                $producto['precio_unitario_nuevo']  =   $detalle->precio_unitario_nuevo;

                $tallas             =   [];
                $subtotal           =   0.0;
                $subtotal_with_desc =   0.0;
                $cantidadTotal = 0;
                foreach ($producto_color_tallas as $producto_color_talla) {
                    $talla = [];
                    $talla['talla_id'] = $producto_color_talla->talla_id;
                    $talla['cantidad'] = $producto_color_talla->cantidad;
                    $talla['talla_nombre'] = $producto_color_talla->talla->descripcion;
                    $subtotal += $talla['cantidad'] * $producto['precio_unitario_nuevo'];

                    $cantidadTotal += $talla['cantidad'];
                    array_push($tallas, $talla);
                }

                $producto['tallas'] = $tallas;
                $producto['subtotal'] = $subtotal;
                $producto['cantidad_total'] = $cantidadTotal;
                array_push($detalleFormateado, $producto);
                $productosProcesados[] = $detalle->producto_id . '-' . $detalle->color_id;
            }
        }
        return $detalleFormateado;
    }

    public function convertirAVentaCreate($id)
    {

        try {

            $vista  =   $this->s_cotizacion->getDatosConvertirAVenta($id);

            return $vista;
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return redirect()->route('ventas.cotizacion.index');
        }
    }

    /*
array:9 [
  "_token" => "YUdETOmyjOWG0aKqmE0JrYKJEK7sQgraASRVE9Ih"
  "cotizacion_id" => "7"
  "data_envio" => null
  "fecha_documento_campo" => "2025-10-28"
  "tipo_comprobante" => "129"
  "observacion" => null
  "fecha_vencimiento_campo" => "2025-10-28"
  "cliente" => "437"
  "telefono" => "945356916"
]
*/
    public function convertirADocVenta(CotizacionADocVentaRequest $request)
    {
        DB::beginTransaction();
        try {

            $venta  =   $this->s_cotizacion->convertirADocVenta($request->toArray());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'DOCUMENTO DE VENTA GENERADO CON ÉXITO',
                'documento_id' => $venta->id
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function newDocument($id)
    {
        $documento_old =  Documento::where('cotizacion_venta', $id)->where('estado', '!=', 'ANULADO')->first();
        if ($documento_old->sunat == '1' && $documento_old->tipo_venta != '129') {
            Session::flash('error', 'Este documento ya fue informado a sunat, si desea reemplazarlo debe hacerle una nota de credito y crear un nuevo documento.');
            return redirect()->route('ventas.cotizacion.index');
        }
        foreach ($documento_old->detalles as $detalle) {
            $lote = LoteProducto::find($detalle->lote_id);
            $cantidad = $detalle->cantidad - $detalle->detalles->sum('cantidad');
            $lote->cantidad = $lote->cantidad + $cantidad;
            $lote->cantidad_logica = $lote->cantidad_logica + $cantidad;
            $lote->update();
            //ANULAMOS EL DETALLE
            $detalle->estado = "ANULADO";
            $detalle->update();
        }
        //ANULADO ANTERIO DOCUMENTO
        $documento = Documento::findOrFail($documento_old->id);
        $documento->estado = 'ANULADO';
        $documento->update();
        //REDIRECCIONAR AL DOCUMENTO DE VENTA
        return redirect()->route('ventas.documento.create', ['cotizacion' => $id]);
    }

    /*
array:1 [
  "cotizacion_id" => 18
]
*/
    public function generarPedido(Request $request)
    {
        DB::beginTransaction();

        try {

            $pedido     =   $this->s_cotizacion->convertirAPedido($request->toArray());
            $url_pdf    =   route('pedidos.pedido.reporte', $pedido->id);

            //DB::commit();

            return response()->json([
                'success'   =>  true,
                'message'   =>  "SE HA GENERADO EL PEDIDO N° " . $pedido->pedido_nro,
                'url_pdf'   =>  $url_pdf
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);
        }
    }

    public function getProductos(Request $request)
    {
        try {

            $categoria_id   =   $request->get('categoria_id');
            $marca_id       =   $request->get('marca_id');
            $modelo_id      =   $request->get('modelo_id');


            $query = 'SELECT p.id, p.nombre
                    FROM productos AS p
                    WHERE p.estado = "ACTIVO"';

            $params = [];

            if ($modelo_id) {
                $query .= ' AND p.modelo_id = ?';
                $params[] = $modelo_id;
            }

            if ($marca_id) {
                $query .= ' AND p.marca_id = ?';
                $params[] = $marca_id;
            }

            if ($categoria_id) {
                $query .= ' AND p.categoria_id = ?';
                $params[] = $categoria_id;
            }

            $productos = DB::select($query, $params);

            return response()->json(['success' => true, 'productos' => $productos]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getColoresTallas($almacen_id, $producto_id)
    {

        try {


            $precios_venta  =   DB::select('SELECT
                                p.id AS producto_id,
                                p.nombre AS producto_nombre,
                                p.precio_venta_1,
                                p.precio_venta_2,
                                p.precio_venta_3
                                FROM
                                    productos AS p
                                WHERE
                                    p.id = ? AND p.estado = "ACTIVO" ', [$producto_id]);


            $colores =  DB::select(
                'SELECT
                                    p.id AS producto_id,
                                    p.nombre AS producto_nombre,
                                    c.id AS color_id,
                                    c.descripcion AS color_nombre
                                FROM
                                    producto_colores AS pc
                                    inner join productos as p on p.id = pc.producto_id
                                    inner join colores as c on c.id = pc.color_id
                                WHERE
                                    pc.almacen_id  = ?
                                    AND pc.producto_id = ?
                                    AND p.estado = "ACTIVO" and c.estado = "ACTIVO" ',
                [$almacen_id, $producto_id]
            );

            $stocks =   DB::select(
                'select
                        pct.producto_id,
                        pct.color_id,
                        pct.talla_id,
                        pct.stock,
                        pct.stock_logico,
                        t.descripcion as talla_nombre
                        from producto_color_tallas as pct
                        inner join productos as p on p.id = pct.producto_id
                        inner join colores as c on c.id = pct.color_id
                        inner join tallas as t on t.id = pct.talla_id
                        where
                        p.estado = "ACTIVO"
                        and c.estado = "ACTIVO"
                        and t.estado = "ACTIVO"
                        and pct.almacen_id = ?
                        AND p.id = ?',
                [$almacen_id, $producto_id]
            );

            $tallas =   Talla::where('estado', 'ACTIVO')->orderBy('id')->get();

            $producto_color_tallas  =   null;
            $_precios_venta          =   null;
            if (count($colores) > 0) {
                $producto_color_tallas  =   $this->formatearColoresTallas($colores, $stocks, $tallas);
            }
            if (count($precios_venta) !== 0) {
                $_precios_venta   =   $precios_venta[0];
            }

            return response()->json([
                'success' => true,
                'producto_color_tallas'     =>  $producto_color_tallas,
                'precios_venta'             =>  $_precios_venta,
                'message'                   =>  'TALLAS OBTENIDAS'
            ]);
        } catch (\Throwable $th) {

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function formatearColoresTallas($colores, $stocks, $tallas)
    {
        $producto = [];

        // Verifica si $colores no está vacío
        if (count($colores) > 0) {
            $producto['id'] = $colores[0]->producto_id;
            $producto['nombre'] = $colores[0]->producto_nombre;
        } else {
            // Maneja el caso cuando $colores está vacío
            $producto['id'] = null;
            $producto['nombre'] = null;
        }

        $lstColores = [];

        //======== RECORRIENDO COLORES =======
        foreach ($colores as $color) {
            $item_color = [];
            $item_color['id'] = $color->color_id;
            $item_color['nombre'] = $color->color_nombre;

            //======== OBTENIENDO TALLAS DEL COLOR =======
            $lstTallas = [];

            foreach ($tallas as $talla) {
                $item_talla = [];
                $item_talla['id'] = $talla->id;
                $item_talla['nombre'] = $talla->descripcion;

                // Filtrar stocks para color y talla actuales
                $stock_filtrado = array_filter($stocks, function ($stock) use ($producto, $color, $talla) {
                    return $stock->producto_id == $producto['id'] &&
                        $stock->color_id == $color->color_id &&
                        $stock->talla_id == $talla->id;
                });

                // Asignar stock y stock lógico si existe, o establecer en 0
                if (!empty($stock_filtrado)) {
                    $first_stock = reset($stock_filtrado); // Obtiene el primer elemento del array filtrado
                    $item_talla['stock'] = $first_stock->stock;
                    $item_talla['stock_logico'] = $first_stock->stock_logico;
                } else {
                    $item_talla['stock'] = 0;
                    $item_talla['stock_logico'] = 0;
                }

                $lstTallas[] = $item_talla;
            }

            $item_color['tallas'] = $lstTallas;
            $lstColores[] = $item_color;
        }

        $producto['colores'] = $lstColores;

        return $producto;
    }

    public function getProductoBarCode($barcode)
    {
        try {

            $producto   =   DB::select('SELECT
                            cb.*,
                            c.descripcion AS color_nombre,
                            t.descripcion AS talla_nombre,
                            p.id,
                            p.nombre AS nombre,
                            p.categoria_id,
                            p.marca_id,
                            p.modelo_id,
                            p.precio_venta_1
                            FROM codigos_barra AS cb
                            INNER JOIN colores AS c ON c.id = cb.color_id
                            INNER JOIN tallas AS t ON t.id = cb.talla_id
                            INNER JOIN productos AS p ON p.id = cb.producto_id
                            WHERE cb.codigo_barras = ?', [$barcode]);

            if (count($producto) === 0) {
                throw new Exception("NO SE ENCONTRÓ NINGÚN PRODUCTO CON ESTE CÓDIGO DE BARRAS!!!");
            }

            return response()->json(['success' => true, 'producto' => $producto[0]]);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function devolverCantidades(Request $request)
    {

        DB::beginTransaction();
        try {
            $cotizacion_detalle =   CotizacionDetalle::where('cotizacion_id', $request->get('cotizacion_id'))->get();

            foreach ($cotizacion_detalle as $item) {

                //===== DEVOLVER STOCK LÓGICO ===========
                DB::update(
                    'UPDATE producto_color_tallas
                SET stock_logico = stock_logico + ?
                WHERE
                almacen_id = ?
                AND producto_id = ?
                AND color_id = ?
                AND talla_id = ?',
                    [
                        $item->cantidad,
                        $item->almacen_id,
                        $item->producto_id,
                        $item->color_id,
                        $item->talla_id
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CANTIDAD DEVUELTA CON ÉXITO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, $th->getMessage()]);
        }
    }

    public function convertirAReserva(int $id)
    {
        try {

            $cotizacion         =   Cotizacion::findOrFail($id);
            if($cotizacion->pedido_id){
                throw new Exception("LA COTIZACIÓN YA FUE CONVERTIDA EN RESERVA: RE-".$cotizacion->pedido_id);
            }
            if($cotizacion->estado != 'VIGENTE'){
                throw new Exception("EL ESTADO DE LA COTIZACIÓN ES: ".$cotizacion->estado);
            }

            $empresas           =   Empresa::where('estado', 'ACTIVO')->get();
            $condiciones        =   Condicion::where('estado', 'ACTIVO')->get();

            $sede_id            =   Auth::user()->sede_id;
            $sede               =   Sede::find($sede_id);

            $almacenes          =   Almacen::where('estado', 'ACTIVO')->where('tipo_almacen', 'PRINCIPAL')->get();

            $tallas             =   Talla::where('estado', 'ACTIVO')->get();

            $tipos_documento    =   tipos_documento();
            $departamentos      =   departamentos();
            $tipo_clientes      =   tipo_clientes();
            $metodos_pago       =   tipos_pago();
            $cuentas            =   UtilidadesController::getCuentas();
            $tipos_envio        =   UtilidadesController::getTiposEnvio();
            $origenes_ventas    =   UtilidadesController::getOrigenesVentas();
            $tipos_pago_envio   =   UtilidadesController::getTiposPagoEnvio();

            //======== DATOS FORMULARIO ==========
            $detalle            =   CotizacionDetalle::where('cotizacion_id', $id)->where('tipo', 'PRODUCTO')->get();
            $detalle            =   UtilidadesController::formatearArrayDetalleObjetos($detalle);

            $cliente            =   Cliente::findOrFail($cotizacion->cliente_id);
            $registrador        =   User::find(Auth::user()->id);
            $fecha_registro     =   date('Y-m-d');
            $almacen_id         =   $cotizacion->almacen_id;
            $telefono           =   $cotizacion->telefono;
            $origen_venta_id    =   $cotizacion->origen_venta_id;

            return view(
                'pedidos.pedido.create',
                compact(
                    'almacenes',
                    'sede_id',
                    'empresas',
                    'condiciones',
                    'tallas',
                    'tipos_documento',
                    'departamentos',
                    'tipo_clientes',
                    'registrador',
                    'sede',
                    'metodos_pago',
                    'cuentas',
                    'origenes_ventas',
                    'tipos_envio',
                    'tipos_pago_envio',
                    'cliente',

                    'cotizacion',
                    'detalle',
                    'cliente',
                    'registrador',
                    'fecha_registro',
                    'almacen_id',
                    'telefono',
                    'origen_venta_id'
                )
            );
        } catch (Throwable $th) {
            Session::flash('message_error', $th->getMessage());
            return back();
        }
    }
}
