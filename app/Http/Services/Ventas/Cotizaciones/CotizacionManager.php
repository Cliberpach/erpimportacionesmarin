<?php

namespace App\Http\Services\Ventas\Cotizaciones;

use App\Models\Reservas\Reservas\Pedido;
use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\Ventas\Documento\Documento;
use Illuminate\Contracts\View\View;

class CotizacionManager
{
    private CotizacionService $s_cotizacion;

    public function __construct()
    {
        $this->s_cotizacion      =   new CotizacionService();
    }

    public function store(array $datos): Cotizacion
    {
        return $this->s_cotizacion->store($datos);
    }

    public function update(array $datos, int $id): Cotizacion
    {
        return $this->s_cotizacion->update($datos, $id);
    }

    public function getDatosConvertirAVenta(int $id):View{
        return $this->s_cotizacion->getDatosConvertirAVenta($id);
    }

    public function convertirADocVenta(array $datos):Documento{
        return $this->s_cotizacion->convertirADocVenta($datos);
    }

    public function convertirAPedido(array $datos):Pedido{
        return $this->s_cotizacion->convertirAPedido($datos);
    }
}
