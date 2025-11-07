<?php

namespace App\Http\Services\Almacen\Traslados;

use App\Almacenes\Producto;
use App\Models\Almacenes\Traslados\Traslado;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use Illuminate\Support\Collection;

class TrasladoService
{
    private TrasladoDto $s_traslado_dto;
    private TrasladoRepository $s_traslado_repo;
    private TrasladoValidacion $s_traslado_validacion;

    public function __construct()
    {
        $this->s_traslado_dto   =   new TrasladoDto();
        $this->s_traslado_repo  =   new TrasladoRepository();
        $this->s_traslado_validacion    =   new TrasladoValidacion();
    }

    public function storeFromVenta(Documento $venta):Traslado
    {
        $dto        =   $this->s_traslado_dto->getTrasladoDtoFromVenta($venta);
        $traslado   =   $this->s_traslado_repo->insertarTraslado($dto);

        $dto_detalle    =   $this->s_traslado_dto->getTrasladoDetalleDtoFromVenta($traslado, $venta);
        $this->s_traslado_repo->insertarTrasladoDetalle($dto_detalle);

        return $traslado;
    }

    public function setEnviado(int $id):Traslado
    {
        $this->s_traslado_validacion->validacionSetEnvio($id);
        return $this->s_traslado_repo->setEstado($id,'ENVIADO');
    }
}
