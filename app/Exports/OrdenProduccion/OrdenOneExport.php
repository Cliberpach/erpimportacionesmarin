<?php

namespace App\Exports\OrdenProduccion;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrdenOneExport implements FromView
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
        return view('pedidos.ordenes.reports.excel-one', [
            'lstProgramacionProduccion' =>  $this->resultado,
            'datos'                     =>  $this->filtros,
            'empresa'                   =>  $this->empresa
        ]);
    }
}
