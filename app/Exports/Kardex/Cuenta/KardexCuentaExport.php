<?php

namespace App\Exports\Kardex\Cuenta;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KardexCuentaExport implements FromView
{
    protected $data;
    protected $filters;
    protected $empresa;

    public function __construct($data,$filters,$empresa)
    {
        $this->data     =   $data;
        $this->filters  =   $filters;
        $this->empresa  =   $empresa;
    }

    public function view(): View
    {
        return view('kardex.cuenta.reports.excel', [
            'reporte'       =>  $this->data,
            'filters'       =>  $this->filters,
            'empresa'       =>  $this->empresa
        ]);
    }
}
