<?php

namespace App\Http\Services\Ventas\NotaCredito;

use App\Ventas\Nota;

class NotaCreditoManager
{
    private NotaCreditoService $s_nota_credito;

    public function __construct() {
        $this->s_nota_credito      =   new NotaCreditoService();
    }

    public function store(array $datos):Nota{
        return $this->s_nota_credito->store($datos);
    }

}
