<?php

namespace App\Http\Services\Despachos\Embalaje;

use App\Models\Despachos\PaqueteEmbalado;
use App\Ventas\Guia;

class EmbalajeManager
{
    private EmbalajeService $s_embalaje;

    public function __construct() {
        $this->s_embalaje      =   new EmbalajeService();
    }

    public function getMdlEmbalaje(int $id):array {
        return $this->s_embalaje->getMdlEmbalaje($id);
    }

    public function generarPaqueteEmbalaje(array $datos):PaqueteEmbalado{
        return $this->s_embalaje->generarPaqueteEmbalaje($datos);
    }

    public function getGuiaCreateEnvio(int $envio_id):array{
        return $this->s_embalaje->getGuiaCreateEnvio($envio_id);
    }

    public function storeGuiaEnvio(array $datos):Guia{
        return $this->s_embalaje->storeGuiaEnvio($datos);
    }

}
