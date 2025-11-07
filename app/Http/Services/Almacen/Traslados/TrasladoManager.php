<?php

namespace App\Http\Services\Almacen\Traslados;

use App\Almacenes\Producto;
use App\Models\Almacenes\Traslados\Traslado;
use Illuminate\Support\Collection;

class TrasladoManager
{
    private TrasladoService $s_traslado;

    public function __construct() {
        $this->s_traslado   =   new TrasladoService();
    }

    public function setEnviado(int $id):Traslado{
        return $this->s_traslado->setEnviado($id);
    }

}
