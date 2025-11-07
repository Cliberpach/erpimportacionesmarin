<?php

namespace App\Http\Services\Despachos\Reparto;

use App\Models\Despachos\Repartos\Reparto;

class RepartoManager
{
    private RepartoService $s_reparto;

    public function __construct() {
        $this->s_reparto      =   new RepartoService();
    }

    public function pdfBultos(int $id, int $nro_bultos):array{
        return $this->s_reparto->pdfBultos($id,$nro_bultos);
    }

    public function store(array $data):Reparto{
        return $this->s_reparto->store($data);
    }


    public function getMdlRShow(int $id):array{
        return $this->s_reparto->getMdlRShow($id);
    }

    public function getEnviosPorPaquete(int $paquete_id):array{
        return $this->s_reparto->getEnviosPorPaquete($paquete_id);
    }
}
