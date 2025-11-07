<?php

namespace App\Http\Services\Despachos\Embalaje;

use App\Models\Despachos\PaqueteEmbalado;
use App\Models\Despachos\PaqueteEmbaladoDetalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\Guia;
use App\Ventas\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;


class EmbalajeRepository
{

    public function crearPaqueteEmbalado($lstEnvios, array $qr_info, array $barcode_info, int $id): PaqueteEmbalado
    {
        $envio = EnvioVenta::findOrFail($id);

        $paquete                            = new PaqueteEmbalado();
        $paquete->qr_codigo                 = $qr_info['codigo'];
        $paquete->registrador_id            = Auth::user()->id;
        $paquete->registrador_nombre        = Auth::user()->usuario;
        $paquete->qr_nombre                 = $qr_info['fileName'];
        $paquete->qr_ruta                   = $qr_info['filePath'];
        $paquete->barcode_nombre            = $barcode_info['fileName'];
        $paquete->barcode_ruta              = $barcode_info['filePath'];
        $paquete->cliente_id                = $envio->cliente_id;
        $paquete->cliente_nombre            = $envio->cliente_nombre;
        $paquete->cliente_celular           = $envio->cliente_celular;
        $paquete->empresa_envio_id          = $envio->empresa_envio_id;
        $paquete->empresa_envio_nombre      = $envio->empresa_envio_nombre;
        $paquete->sede_envio_id             = $envio->sede_envio_id;
        $paquete->sede_envio_nombre         = $envio->sede_envio_nombre;
        $paquete->destinatario_tipo_doc     = $envio->destinatario_tipo_doc;
        $paquete->destinatario_nro_doc      = $envio->destinatario_nro_doc;
        $paquete->destinatario_nombre       = $envio->destinatario_nombre;
        $paquete->departamento_id           = $envio->departamento_id;
        $paquete->provincia_id              = $envio->provincia_id;
        $paquete->distrito_id               = $envio->distrito_id;
        $paquete->departamento              = $envio->departamento;
        $paquete->provincia                 = $envio->provincia;
        $paquete->distrito                  = $envio->distrito;
        $paquete->tipo_pago_envio_id        = $envio->tipo_pago_envio_id;
        $paquete->tipo_pago_envio           = $envio->tipo_pago_envio;
        $paquete->entrega_domicilio         = $envio->entrega_domicilio;
        $paquete->direccion_entrega         = $envio->direccion_entrega;
        $paquete->sede_id                   = $envio->sede_id;
        $paquete->save();

        return $paquete;
    }

    public function agregarDetallePaqueteEmbalado($lstEnvios, $paquete_id)
    {
        foreach ($lstEnvios as $envio) {
            $detalle = new PaqueteEmbaladoDetalle();
            $detalle->paquete_embalado_id = $paquete_id;
            $detalle->envio_venta_id      = $envio->id;
            $detalle->save();
        }
    }


    public function actualizarEstadoEnvios($lstEnvios, $estado)
    {
        foreach ($lstEnvios as $envio) {
            $envio                          =   EnvioVenta::findOrFail($envio->id);
            $envio->estado                  =   $estado;
            $envio->fecha_embalaje          =   Carbon::now();
            $envio->usuario_embalaje_id     =   Auth::user()->id;
            $envio->usuario_embalaje        =   Auth::user()->usuario;
            $envio->update();

            $venta = Documento::findOrFail($envio->documento_id);
            $venta->estado_despacho = $estado;
            $venta->update();

            $pedido =   Pedido::find($venta->pedido_id);
            if ($pedido) {
                $pedido->estado_despacho =   $estado;
                $pedido->update();
            }
        }
    }

    public function getEnviosSimilares(int $id): Collection
    {
        $envio  =   EnvioVenta::findOrFail($id);
        $venta  =   Documento::findOrFail($envio->documento_id);

        //========= DETECTANDO SIMILARES ========
        $similares = EnvioVenta::from('envios_ventas as ev')
            ->join('cotizacion_documento as cd', 'cd.id', 'ev.documento_id')
            ->leftJoin('traslados as t','t.id','ev.traslado_id')
            ->where('cd.almacen_id', $venta->almacen_id) //MISMO ALMACÉN
            ->where('ev.cliente_id', $envio->cliente_id)
            ->where('ev.empresa_envio_id', $envio->empresa_envio_id)
            ->where('ev.sede_envio_id', $envio->sede_envio_id) //======== AGENCIA DE ENVÍO ========
            ->where('ev.destinatario_tipo_doc', $envio->destinatario_tipo_doc)
            ->where('ev.destinatario_nro_doc', $envio->destinatario_nro_doc)
            ->where('ev.destinatario_nombre', $envio->destinatario_nombre)
            ->where('ev.sede_despachadora_id', $envio->sede_despachadora_id) //======== SOLO ENVÍOS DE LA SEDE DESPACHADORA =======
            ->whereIn('ev.estado', ['PENDIENTE', 'RESERVADO'])
            ->where('ev.modo', 'VENTA')
            ->where('cd.estado_pago', 'PAGADA')
            ->where('cd.estado', 'ACTIVO')
            ->where('cd.sunat', '<>', '2')
            ->where(function ($q) {
                $q->whereIn('t.estado', ['RECIBIDO'])
                    ->orWhereNull('t.estado');
            })
            ->select(
                'ev.id',
                'ev.documento_nro',
                'ev.documento_id',
                'cd.estado_pago',
                'cd.convert_en_id',
                'cd.convert_en_serie',
                'cd.tipo_venta_id',
                'ev.guia_id',
                'ev.guia_serie',
                'cd.monto_envio'
            )
            ->get();

        return $similares;
    }

    public function tieneReservasPendientes(EnvioVenta $envio_venta): string
    {
        $venta = Documento::findOrFail($envio_venta->documento_id);

        $pedidos = Pedido::where('cliente_id', $envio_venta->cliente_id)
            ->where('id', '<>', $venta->pedido_id)
            ->where('sede_id',$venta->sede_id)
            ->where('estado', 'PENDIENTE')
            ->pluck('id');

        if ($pedidos->isEmpty()) {
            return '';
        }

        return $pedidos->map(fn($id) => 'RE-' . $id)->implode(' | ');
    }

    public function enlazarPaqueteGuia(int $envio_id, PaqueteEmbalado $paquete)
    {
        $envio  =   EnvioVenta::findOrFail($envio_id);

        $paquete->guia_id       =   $envio->guia_id;
        $paquete->guia_serie    =   $envio->guia_serie;
        $paquete->update();

        $guia                   =   Guia::findOrFail($envio->guia_id);
        $guia->paquete_id       =   $paquete->id;
        $guia->paquete_codigo   =   $paquete->qr_codigo;
        $guia->update();
    }
}
