<?php

namespace App\Http\Services\Almacen\Traslados;

use App\Almacenes\Producto;
use App\Models\Almacenes\Traslados\Traslado;
use Exception;
use Illuminate\Support\Collection;

class TrasladoValidacion
{

    public function validacionSetEnvio(int $id){
        $traslado   =   Traslado::findOrFail($id);
        if($traslado->estado != 'PENDIENTE'){
            throw new Exception("TRASLADO TR-".$traslado->id.", YA SE ENCUENTRA ".$traslado->estado);
        }
    }

}
