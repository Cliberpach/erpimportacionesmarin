<?php

namespace App\Exports\Kardex\Stock;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KStockExport implements FromView
{
    protected $resultado;
    protected $filtros;
    protected $empresa;
    protected $tallas;

    public function __construct($resultado,$filtros,$empresa,$tallas)
    {
        $this->resultado    =   $resultado;
        $this->filtros      =   $filtros;
        $this->empresa      =   $empresa;
        $this->tallas       =   $tallas;
    }

    public function view(): View
    {
        return view('kardex.stock.reports.excel', [
            'resultado'                 =>  $this->resultado,
            'filtros'                   =>  $this->filtros,
            'empresa'                   =>  $this->empresa,
            'tallas'                    =>  $this->tallas
        ]);
    }
}
