<?php

namespace App\Http\Controllers\Almacenes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\UtilController;
use App\Http\Requests\Almacen\Transportista\TransportistaStoreRequest;
use App\Http\Requests\Almacen\Transportista\TransportistaUpdateRequest;
use App\Models\Almacenes\Transportista\Transportista;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class TransportistaController extends Controller
{
    public function index(): View
    {
        return view('almacenes.transportistas.index');
    }

    public function getTransportistas(Request $request): JsonResponse
    {

        $transportistas =   DB::table('transportistas as t')
            ->select(
                't.id',
                't.nombre',
                't.tipo_documento_nombre',
                't.nro_documento',
                't.direccion',
                't.mtc'
            )
            ->where('t.estado', 'ACTIVO')
            ->get();

        return DataTables::of($transportistas)->make(true);
    }


    public function create(): View
    {
        $tipos_documento    =   DB::select('SELECT
                                td.id,
                                td.simbolo,
                                td.descripcion
                                FROM tabladetalles AS td
                                WHERE
                                td.tabla_id = 3
                                AND td.simbolo = "RUC"');
        return view('almacenes.transportistas.create', compact('tipos_documento'));
    }

    /*
array:6 [ // app\Http\Controllers\General\Herramientas\TransportistaController.php:28
  "_token" => "OCsec3GMW84iFRM1pNdmAaBHqSkQI7qiCre0s1ix"
  "tipo_documento" => "2"
  "nro_documento" => "20370146994"
  "nombre" => "CORPORACION ACEROS AREQUIPA S.A."
  "direccion" => "CAR. PANAMERICANA SUR NRO. 241  PANAMERICANA SUR, ICA - PISCO - PARACAS"
  "mtc" => "asd123"
]
*/
    public function store(TransportistaStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $transportista                          =   new Transportista();
            $transportista->tipo_documento_id       =   $request->get('tipo_documento');
            $transportista->tipo_documento_nombre   =   'RUC';
            $transportista->tipo_documento_codigo   =   '06';
            $transportista->nombre                  =   $request->get('nombre');
            $transportista->nro_documento           =   $request->get('nro_documento');
            $transportista->direccion               =   $request->get('direccion');
            $transportista->mtc                     =   $request->get('mtc');
            $transportista->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'TRANSPORTISTA REGISTRADO CON ÉXITO']);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function edit(int $id): View
    {
        $tipos_documento    =   DB::select('SELECT
                                td.id,
                                td.simbolo,
                                td.descripcion
                                FROM tabladetalles AS td
                                WHERE
                                td.tabla_id = 3
                                AND td.simbolo = "RUC"');
        $transportista      =   Transportista::find($id);
        return view('almacenes.transportistas.edit', compact('tipos_documento', 'transportista'));
    }

    /*
array:6 [ // app\Http\Controllers\General\Herramientas\TransportistaController.php:86
  "_token" => "OCsec3GMW84iFRM1pNdmAaBHqSkQI7qiCre0s1ix"
  "tipo_documento" => "2"
  "nro_documento" => "20370146994"
  "nombre" => "CORPORACION ACEROS AREQUIPA S.A."
  "direccion" => "CAR. PANAMERICANA SUR NRO. 241  PANAMERICANA SUR, ICA - PISCO - PARACAS"
  "mtc" => "asd123"
]
*/
    public function update(int $id, TransportistaUpdateRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {

            $transportista                          =   Transportista::find($id);
            $transportista->tipo_documento_id       =   $request->get('tipo_documento');
            $transportista->tipo_documento_nombre   =   'RUC';
            $transportista->tipo_documento_codigo   =   '06';
            $transportista->nombre                  =   $request->get('nombre');
            $transportista->nro_documento           =   $request->get('nro_documento');
            $transportista->direccion               =   $request->get('direccion');
            $transportista->mtc                     =   $request->get('mtc');
            $transportista->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'TRANSPORTISTA ACTUALIZADO CON ÉXITO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();
        try {

            $transportista                    =   Transportista::find($id);
            $transportista->estado            =   'ANULADO';
            $transportista->update();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'TRANSPORTISTA ELIMINADO']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

/*
array:2 [ // app\Http\Controllers\General\Herramientas\ConductorController.php:27
    "tipo_documento"    => "1"
    "nro_documento"     => "75608753"
]
*/
    public function consultarDocumento(Request $request): JsonResponse
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
                                from tipos_documento as td
                                where
                                td.id = ?
                                and td.estado = "ACTIVO"', [$tipo_documento]);

            if (count($exists_tipo_doc) === 0) {
                throw new Exception("EL TIPO DE DOC NO EXISTE EN LA BD");
            }

            if ($tipo_documento != 1 && $tipo_documento != 2) {
                throw new Exception("SOLO SE PUEDEN CONSULTAR DNI Y RUC");
            }

            if ($tipo_documento == 1 && strlen($nro_documento) != 8) {
                throw new Exception("EL TIPO DE DOCUMENTO DNI DEBE TENER 8 DÍGITOS");
            }

            if ($tipo_documento == 2 && strlen($nro_documento) != 11) {
                throw new Exception("EL TIPO DE DOCUMENTO RUC DEBE TENER 11 DÍGITOS");
            }


            //======= COMPROBAR QUE NO EXISTA EL DOCUMENTO EN LA TABLA TRANSPORTISTAS =======
            $existe_nro_documento   =   DB::select(
                'select
                                    t.id,t.nombre
                                    from transportistas as t
                                    where
                                    t.tipo_documento_id = ?
                                    and t.nro_documento = ?
                                    and t.estado = "ACTIVO"',
                [$tipo_documento, $nro_documento]
            );

            if (count($existe_nro_documento) > 0) {
                throw new Exception($exists_tipo_doc[0]->descripcion . ':' . $nro_documento . '.YA EXISTE EN LA BD');
            }

            if ($tipo_documento == 1) {

                $res_consulta_api   =   UtilController::apiDni($nro_documento);
                $res                =   $res_consulta_api->getData();

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if ($res->success) {
                    return response()->json(['success' => true, 'data' => $res->data, 'message' => 'OPERACIÓN COMPLETADA']);
                } else {
                    throw new Exception($res->message);
                }
            }

            if ($tipo_documento == 2) {
                $res_consulta_api   =   UtilController::apiRuc($nro_documento);
                $res                =   $res_consulta_api->getData();

                //======= EN CASO LA CONSULTA FUE EXITOSA =====
                if ($res->success) {
                    return response()->json(['success' => true, 'data' => $res->data, 'message' => 'OPERACIÓN COMPLETADA']);
                } else {
                    throw new Exception($res->message);
                }
            }

            throw new Exception("SOLO SE PUEDEN CONSULTAR RUC Y DNI");
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
