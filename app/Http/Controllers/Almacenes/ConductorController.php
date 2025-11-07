<?php

namespace App\Http\Controllers\Almacenes;

use App\Almacenes\Conductor;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UtilidadesController;
use App\Http\Requests\Almacen\Conductor\ConductorStoreRequest;
use App\Http\Requests\Almacen\Conductor\ConductorUpdateRequest;
use App\Mantenimiento\Tabla\Detalle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ConductorController extends Controller
{
    public function index()
    {
        return view('almacenes.conductores.index');
    }

    public function getConductores(Request $request)
    {
        $conductores    =   DB::table('conductores as co')
            ->select(
                'co.id',
                DB::raw("CONCAT(co.nombres, ' ', co.apellidos) AS nombres"),
                'co.tipo_documento_nombre',
                'co.nro_documento',
                'co.licencia'
            )
            ->where('co.estado', 'ACTIVO');

        return DataTables::of($conductores)
            ->make(true);
    }

    public function create()
    {
        $tipos_documento    =   DB::select('SELECT
                                td.id,
                                td.simbolo,
                                td.descripcion
                                FROM tabladetalles AS td
                                WHERE
                                td.tabla_id = 3
                                AND td.simbolo = "DNI"');


        return view('almacenes.conductores.create', compact('tipos_documento'));
    }

    public function edit($id)
    {
        $tipos_documento    =   DB::select('SELECT
                                td.id,
                                td.simbolo,
                                td.descripcion
                                FROM tabladetalles AS td
                                WHERE
                                td.tabla_id = 3
                                AND td.simbolo = "DNI"');

        $conductor          =   Conductor::find($id);

        return view('almacenes.conductores.edit', compact('conductor', 'tipos_documento'));
    }

    /*
    array:7 [ // app\Http\Controllers\Registros\ConductorController.php:67
    "_token"                => "40VPBenxpHS6nf6bF8zTNjfnxCLwLU3OGy5CRd1c"
    "tipo_documento"        => "1"
    "nro_documento"         => "80239830"
    "nombres"                => "CLIBER LESTER"
    "apellidos"              => "PACHECO PERINANGO"
    "licencia"              => "4124sad"
    "registro_mtc"          =>  "MTC"
    ]
    */
    public function store(ConductorStoreRequest $request)
    {

        DB::beginTransaction();
        try {

            $tipo_documento =   Detalle::where('id', $request->get('tipo_documento'))
                ->where('estado', 'ACTIVO')
                ->first();

            $conductor                          =   new Conductor();
            $conductor->tipo_documento_id       =   $request->get('tipo_documento');
            $conductor->nro_documento           =   $request->get('nro_documento');
            $conductor->nombres                 =   mb_strtoupper($request->get('nombres'));
            $conductor->apellidos               =   mb_strtoupper($request->get('apellidos'));
            $conductor->licencia                =   mb_strtoupper(trim($request->get('licencia')), 'UTF-8');
            $conductor->tipo_documento_nombre   =   $tipo_documento->simbolo;
            $conductor->tipo_documento_codigo   =   $tipo_documento->parametro;
            $conductor->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CONDUCTOR REGISTRADO']);
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
    array:7 [ // app\Http\Controllers\Registros\ConductorController.php:67
        "_token"            => "40VPBenxpHS6nf6bF8zTNjfnxCLwLU3OGy5CRd1c"
        "tipo_documento"    => "1"
        "nro_documento"     => "80239830"
        "nombres"            => "CLIBER LESTER"
        "apellidos"          => "PACHECO PERINANGO"
        "licencia"          => "4124sad"
    ]
*/
    public function update(ConductorUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $tipo_documento =   Detalle::where('id', $request->get('tipo_documento'))
                ->where('estado', 'ACTIVO')
                ->first();

            $conductor                          =   Conductor::find($id);
            $conductor->tipo_documento_id       =   $request->get('tipo_documento');
            $conductor->nro_documento           =   $request->get('nro_documento');
            $conductor->nombres                 =   mb_strtoupper($request->get('nombres'));
            $conductor->apellidos               =   mb_strtoupper($request->get('apellidos'));
            $conductor->licencia                =   mb_strtoupper(trim($request->get('licencia')), 'UTF-8');
            $conductor->tipo_documento_nombre   =   $tipo_documento->simbolo;
            $conductor->tipo_documento_codigo   =   $tipo_documento->parametro;
            $conductor->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CONDUCTOR ACTUALIZADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage(), 'line' => $th->getLine()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $conductor                    =   Conductor::find($id);
            $conductor->estado            =   'ANULADO';
            $conductor->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'CONDUCTOR ELIMINADO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    /*
array:2 [
  "tipo_documento" => "6"
  "nro_documento" => "71114222"
]
*/
    public function consultarDocumento(Request $request)
    {
        try {

            //========= VALIDANDO QUE EL TIPO DOCUMENTO Y N° DOCUMENTO NO SEAN NULL =======
            $tipo_documento =   $request->get('tipo_documento', null);
            $nro_documento  =   $request->get('nro_documento', null);

            if (!$tipo_documento) {
                throw new Exception("EL TIPO DE DOCUMENTO ES OBLIGATORIO");
            }

            if (!$nro_documento) {
                throw new Exception("EL N° DOC ES OBLIGATORIO");
            }

            if (!is_numeric($nro_documento)) {
                throw new Exception("EL N° DOCUMENTO DEBE SER NUMÉRICO");
            }

            //========= VERIFICANDO QUE EXISTA EL TIPO DOC EN LA BD ========
            $exists_tipo_doc    =   DB::select('select
                                td.id,
                                td.descripcion
                                from tabladetalles as td
                                where
                                td.id = ?
                                AND td.tabla_id = 3
                                and td.estado = "ACTIVO"', [$tipo_documento]);

            if (count($exists_tipo_doc) === 0) {
                throw new Exception("EL TIPO DE DOC NO EXISTE EN LA BD");
            }

            if ($tipo_documento != 6 && $tipo_documento != 8) {
                throw new Exception("SOLO SE PUEDEN CONSULTAR DNI Y RUC");
            }

            if ($tipo_documento == 6 && strlen($nro_documento) != 8) {
                throw new Exception("EL TIPO DE DOCUMENTO DNI DEBE TENER 8 DÍGITOS");
            }

            if ($tipo_documento == 8 && strlen($nro_documento) != 11) {
                throw new Exception("EL TIPO DE DOCUMENTO RUC DEBE TENER 11 DÍGITOS");
            }


            //======= COMPROBAR QUE NO EXISTA EL DOCUMENTO EN LA TABLA conductores =======
            $existe_nro_documento   =  Conductor::where('nro_documento', $nro_documento)
                ->where('tipo_documento_id', $tipo_documento)
                ->where('estado', 'ACTIVO')
                ->select(
                    'id',
                    DB::raw("CONCAT(nombres, ' ', apellidos) AS nombre_completo")
                )
                ->first();

            if ($existe_nro_documento) {
                throw new Exception($exists_tipo_doc[0]->descripcion . ':' . $nro_documento . '.YA EXISTE EN LA BD');
            }

            if ($tipo_documento == 6) {

                $res_consulta_api   =   UtilidadesController::apiDni($nro_documento);
                $res                =   $res_consulta_api->getData();

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if ($res->success) {
                    return response()->json(['success' => true, 'data' => $res->data, 'message' => 'OPERACIÓN COMPLETADA']);
                } else {
                    throw new Exception($res->message);
                }
            }

            if ($tipo_documento == 8) {
                $res_consulta_api   =   UtilidadesController::apiRuc($nro_documento);
                $res                =   $res_consulta_api->getData();

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if ($res->success) {
                    return response()->json(['success' => true, 'data' => $res->data, 'message' => 'OPERACIÓN COMPLETADA']);
                } else {
                    throw new Exception($res->message);
                }
            }
        } catch (Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
