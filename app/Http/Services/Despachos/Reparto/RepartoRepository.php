<?php

namespace App\Http\Services\Despachos\Reparto;

use App\Models\Despachos\PaqueteEmbalado;
use App\Models\Despachos\PaqueteEmbaladoDetalle;
use App\Models\Despachos\Repartos\Reparto;
use App\Models\Despachos\Repartos\RepartoDetalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\Pedido;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class RepartoRepository
{

    public function registrarReparto(string $codigo, array $data): Reparto
    {
        $reparto = new Reparto();
        $reparto->codigo = $codigo;
        $reparto->registrador_id = auth()->user()->id;
        $reparto->registrador_nombre = auth()->user()->usuario;
        $reparto->observacion = $data['observacion'] ?? null;
        $reparto->sede_id       =   Auth::user()->sede_id;
        $reparto->save();
        return $reparto;
    }

    public function registrarDetalleReparto(int $reparto_id, array $lst_paquetes)
    {
        foreach ($lst_paquetes as $paquete) {

            $paquete = PaqueteEmbalado::findOrFail($paquete->id);
            if ($paquete->reparto_id) {
                throw new Exception("EL PAQUETE {$paquete->qr_codigo} YA TIENE UN REPARTO ASIGNADO");
            }
            if($paquete->sede_id != Auth::user()->sede_id){
                throw new Exception("EL PAQUETE {$paquete->qr_codigo} PERTENECE A UNA SEDE DIFERENTE A LA DEL USUARIO");
            }

            $detalle = new RepartoDetalle();
            $detalle->reparto_id = $reparto_id;
            $detalle->paquete_embalado_id = $paquete->id;
            $detalle->save();
        }
    }

    public function setRepartoEnPaquetes(array $lst_paquetes, int $reparto_id)
    {
        foreach ($lst_paquetes as $paquete) {
            $paquete_embalado = PaqueteEmbalado::findOrFail($paquete->id);
            $paquete_embalado->reparto_id = $reparto_id;
            $paquete_embalado->update();
        }
    }

    public function actualizarEstadoPaquetes(array $lst_paquetes, string $estado, int $reparto_id)
    {
        foreach ($lst_paquetes as $paquete) {

            $paquete_embalado = PaqueteEmbalado::findOrFail($paquete->id);
            $paquete_embalado->estado = $estado;
            $paquete_embalado->reparto_id = $reparto_id;
            $paquete_embalado->update();

            $lstEnvios = PaqueteEmbaladoDetalle::where('paquete_embalado_id', $paquete_embalado->id)->get();
            foreach ($lstEnvios as $envio) {

                $envio_bd                       =   EnvioVenta::findOrFail($envio->envio_venta_id);
                $envio_bd->estado               =   $estado;
                $envio->fecha_reparto           =   Carbon::now();
                $envio->usuario_reparto_id      =   Auth::user()->id;
                $envio->usuario_reparto         =   Auth::user()->usuario;
                $envio_bd->update();

                $venta = Documento::find($envio_bd->documento_id);
                $venta->estado_despacho = $estado;
                $venta->update();

                $pedido =   Pedido::find($venta->pedido_id);
                if ($pedido) {
                    $pedido->estado_despacho =   $estado;
                    $pedido->update();
                }
            }
        }
    }
}
