<?php

namespace App\Http\Services\Ventas\Ventas;

use App\Ventas\Documento\Documento;

class VentaManager
{
    private VentaService $s_venta;

    public function __construct() {
        $this->s_venta      =   new VentaService();
    }

    public function registrar(array $datos):Documento {
        return $this->s_venta->registrar($datos);
    }

    public function storePago(array $datos){
        $this->s_venta->storePago($datos);
    }

    public function update(array $datos,int $id):Documento{
        return $this->s_venta->update($datos,$id);
    }

     public function operarVentaReserva(int $venta_id)
    {
        $this->s_venta->operarVentaReserva($venta_id);
    }

    public function getVoucherPdf(int $id,int $size):array{
        return $this->s_venta->getVoucherPdf($id,$size);
    }

    public function enviarPdfWsp(int $venta_id, string $tipo_pdf){
        $this->s_venta->enviarPdfWsp($venta_id,$tipo_pdf);
    }

    public function convertirStore(array $datos):Documento{
        return $this->s_venta->convertirStore($datos);
    }


}
