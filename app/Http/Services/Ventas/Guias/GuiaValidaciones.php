<?php

namespace App\Http\Services\Ventas\Guias;

use App\Almacenes\Almacen;
use App\Almacenes\Conductor;
use App\Almacenes\Vehiculo;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Sedes\Sede;
use App\Models\Almacenes\Transportista\Transportista;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuiaValidaciones
{
    public static function validacionStore(array $request)
    {
        //========= VALIDAR LA SEDE ========
        if (!$request['sede_genera_guia']) {
            throw new Exception("FALTA LA SEDE QUE GENERA LA GUÍA!!!");
        }
        if (!$request['sede_usa_guia']) {
            throw new Exception("FALTA LA SEDE QUE USA LA GUÍA!!!");
        }

        $sede_genera_guia   =   Sede::findOrFail($request['sede_genera_guia']);

        if (!$sede_genera_guia) {
            throw new Exception("NO EXISTE LA SEDE GENERADORA DE GUÍA EN LA BD!!!");
        }

        $sede_usa_guia  =   Sede::findOrFail($request['sede_usa_guia']);

        if (!$sede_usa_guia) {
            throw new Exception("NO EXISTE LA SEDE QUE USARÁ LA GUÍA EN LA BD!!!");
        }

        if(Auth::user()->sede_id != $sede_genera_guia){
            throw new Exception("LA SEDE DEL USUARIO ES DIFERENTE A LA SEDE QUE DEBE GENERAR LA GUÍA");
        }

        $conductor      =   null;
        $transportista  =   null;
        $vehiculo       =   null;

        //======== MODALIDAD TRASLADO PÚBLICO Y M1L ENCENDIDO =========
        if ($request['modalidad_traslado'] === '01' && isset($data['categoria_M1L'])) {
            throw new Exception("SOLO SE PERMITE CATEGORÍA M1L EN MODALIDAD TRASLADO PRIVADO");
        }

        //========== MODALIDAD TRASLADO PÚBLICO =========
        if ($request['modalidad_traslado'] === '01' && !isset($data['categoria_M1L'])) {
            $transportista  =   Transportista::findOrFail($request['conductor']);
        }
        if ($request['modalidad_traslado'] === '02' && !isset($data['categoria_M1L'])) {
            $conductor  =   Conductor::findOrFail($request['conductor']);
        }
        if($request['vehiculo'] && !isset($data['categoria_M1L'])){
            $vehiculo   =   Vehiculo::findOrFail($request['vehiculo']);
        }

        //======== VALIDAR DETALLE VENTA ======
        $lstGuia   =   json_decode($request['lstGuia']);

        if (count($lstGuia) === 0) {
            throw new Exception("EL DETALLE DE LA GUÍA ESTÁ VACÍO!!!");
        }

        //========= VALIDANDO TIPO COMPROBANTE ========
        $tipo_comprobante   =   DB::select(
            'SELECT
                                td.*
                                from tabladetalles as td
                                where
                                td.tabla_id = 21
                                AND td.simbolo = ?
                                AND td.parametro = ?',
            ["09", "T"]
        )[0];


        $almacen    =   Almacen::findOrFail($request['almacen']);

        $datos_validados    =   (object)[
            'sede_genera_guia'      =>  $request['sede_genera_guia'],
            'sede_usa_guia'         =>  $request['sede_usa_guia'],
            'tipo_comprobante'      =>  $tipo_comprobante,
            'porcentaje_igv'        =>  Empresa::find(1)->igv,
            'almacen'               =>  $almacen,
            'lstGuia'               =>  $lstGuia,
            'empresa'               =>  Empresa::find(1),
            'transportista'         =>  $transportista,
            'conductor'             =>  $conductor,
            'categoria_M1L'         =>  isset($data['categoria_M1L'])?true:false,
            'vehiculo'              =>  $vehiculo
        ];

        return  $datos_validados;
    }
}
