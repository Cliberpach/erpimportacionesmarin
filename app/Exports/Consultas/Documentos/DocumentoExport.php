<?php

namespace App\Exports\Consultas\Documentos;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DocumentoExport implements FromView
{
    protected $resultado;
    protected $filtros;
    protected $empresa;

    public function __construct($resultado,$filtros,$empresa)
    {
        $this->resultado    =   $resultado;
        $this->filtros      =   $filtros;
        $this->empresa      =   $empresa;
    }

    public function view(): View
    {
        return view('consultas.documentos.reports.excel', [
            'resultado'                 =>  $this->resultado,
            'filtros'                   =>  $this->filtros,
            'empresa'                   =>  $this->empresa
        ]);
    }
}
