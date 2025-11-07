<?php

namespace App\Http\Services\Despachos\Reparto;

use App\Models\Despachos\Repartos\Reparto;
use Exception;

class RepartoValidacion
{
    public function validacionStore(array $datos)
    {
        $lst_detalle = json_decode($datos['lstDetalleReparto']);
        if(count($lst_detalle) === 0){
            throw new Exception("EL DETALLE DEL REPARTO ESTÁ VACÍO");
        }
    }
}
