<?php

namespace App\Http\Services\Almacen\Traslados;

use App\Models\Almacenes\Traslados\Traslado;
use App\Models\Almacenes\Traslados\TrasladoDetalle;
use Illuminate\Support\Facades\Auth;

class TrasladoRepository
{
    public function insertarTraslado(array $dto): Traslado
    {
        return Traslado::create($dto);
    }

    public function insertarTrasladoDetalle(array $dto){
        TrasladoDetalle::insert($dto);
    }

    public function setEstado(int $id,string $estado):Traslado{
        $traslado                       =   Traslado::findOrFail($id);
        $traslado->estado               =   $estado;
        $traslado->usuario_envio_id     =   Auth::user()->id;
        $traslado->usuario_envio_nombre =   Auth::user()->usuario;
        $traslado->fecha_traslado       =   now();
        $traslado->save();

        return $traslado;
    }
}
