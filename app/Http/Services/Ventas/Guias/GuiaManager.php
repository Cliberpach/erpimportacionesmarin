<?php

namespace App\Http\Services\Ventas\Guias;

use App\Ventas\Guia;
use Barryvdh\DomPDF\PDF;

class GuiaManager
{
    private GuiaService $s_guia;

    public function __construct()
    {
        $this->s_guia = new GuiaService();
    }

    public function store(array $data):Guia
    {
        return $this->s_guia->store($data);
    }

    public function getPdf(int $id):PDF
    {
        return $this->s_guia->getPdf($id);
    }

}
