<?php

namespace App\Http\Services\Despachos\RepartoDetalle;

use App\Http\Services\Despachos\RepartoDetalle\RepartoDetalleService;

class RepartoDetalleManager
{
    private RepartoDetalleService $s_reparto_detalle;

    public function __construct() {
        $this->s_reparto_detalle      =   new RepartoDetalleService();
    }


    public function pdfBultos(int $id, int $nro_bultos):array{
        return $this->s_reparto_detalle->pdfBultos($id,$nro_bultos);
    }



}
