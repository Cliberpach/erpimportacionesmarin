<?php

namespace App\Http\Services\Ventas\Cotizaciones;

use App\Models\Ventas\Cotizaciones\Cotizacion;
use App\Models\Ventas\Cotizaciones\CotizacionDetalle;
use App\Ventas\Documento\Documento;

class CotizacionRepository
{

    public function insertarCotizacion(array $dto): Cotizacion
    {
        return Cotizacion::create($dto);
    }

    public function insertarCotizacionDetalle(array $dto)
    {
        CotizacionDetalle::insert($dto);
    }

    public function actualizarCotizacion(int $id, array $dto): Cotizacion
    {

        $cotizacion                     =   Cotizacion::findOrFail($id);
        $cotizacion->update($dto);

        return $cotizacion;
    }

    public function eliminarDetalleCotizacion(int $id)
    {
        CotizacionDetalle::where('cotizacion_id', $id)->delete();
    }

    public function enlazarCotizacionAVenta(int $cotizacion_id,Documento $venta)
    {
        //======== ENLAZAR DOC A COT ========
        $cotizacion                     =   Cotizacion::findOrFail($cotizacion_id);
        $cotizacion->venta_id           =   $venta->id;
        $cotizacion->venta_serie        =   $venta->serie;
        $cotizacion->venta_correlativo  =   $venta->correlativo;
        $cotizacion->save();
    }

    public function enlazarCotizacionAPedido(int $cotizacion_id,int $pedido_id){
        $cotizacion                     =   Cotizacion::findOrFail($cotizacion_id);
        $cotizacion->pedido_id          =   $pedido_id;
        $cotizacion->save();
    }
}
