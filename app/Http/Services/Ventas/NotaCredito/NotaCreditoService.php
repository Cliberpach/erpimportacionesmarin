<?php

namespace App\Http\Services\Ventas\NotaCredito;

use App\Almacenes\Almacen;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\Nota;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Luecano\NumeroALetras\NumeroALetras;

class NotaCreditoService
{
    private CorrelativoService $s_correlativo;
    private NotaCreditoRepository $s_repository;
    private NotaCreditoPrepararDatos $s_preparar_datos;

    public function __construct()
    {
        $this->s_correlativo    =   new CorrelativoService();
        $this->s_repository     =   new NotaCreditoRepository();
        $this->s_preparar_datos =   new NotaCreditoPrepararDatos();
    }

    public function store(array $datos): Nota
    {
        $documento                  =   Documento::find($datos['documento_id']);
        $sede_id                    =   Auth::user()->sede_id;
        $almacen                    =   Almacen::find($documento->almacen_id);

        $datos_correlativo          =   $this->s_correlativo->getCorrelativo($sede_id, $documento->tipo_venta_id);
        $datos['legenda']           =   $this->convertirTotal($datos['total_nuevo']);
        $datos['sede_id']           =   $sede_id;
        $datos['almacen_id']        =   $almacen->id;
        $datos['almacen_nombre']    =   $almacen->descripcion;
        $datos['igv']               =   $documento->igv;
        $productosJSON              =   $datos['productos_tabla'];
        $productotabla              =   json_decode($productosJSON);
        $datos['lst_detalle']       =   $productotabla;

        $_datos                     =   $this->s_preparar_datos->prepararDatosStore($documento, $datos, $datos_correlativo);
        $nota                       =   $this->s_repository->registrarNotaElectronica($_datos);

        //=========== REGISTRAR DETALLE ===========
        $this->s_repository->registrarDetalleNotaElectronica($datos, $nota, $documento);

        //========== MANEJAR DATOS DE ENVÍO =========
        $this->operarDatosEnvio($nota, $documento);

        //========== ACTUALIZAR ESTADO FACTURACIÓN A INICIADA ======
        DB::table('empresa_numeracion_facturaciones')
            ->where('empresa_id', 1)
            ->where('sede_id', $sede_id)
            ->where('tipo_comprobante', $datos_correlativo->tipo_comprobante->id)
            ->where('emision_iniciada', '0')
            ->where('estado', 'ACTIVO')
            ->update([
                'emision_iniciada'       => '1',
                'updated_at'             => Carbon::now()
            ]);



        return $nota;
    }

    public function convertirTotal($total)
    {
        $formatter = new NumeroALetras();
        $convertir = $formatter->toInvoice($total, 2, 'SOLES');
        return $convertir;
    }

    public function operarDatosEnvio(Nota $nota, Documento $documento)
    {

        //========= ANULACIÓN TOTAL, PONER DATA ENVÍO EN ESTADO ANULADO =========
        if ($nota->codMotivo === '01') {
            $envio  =   EnvioVenta::where('documento_id', $documento->id)->first();
            $envio->estado  =   'ANULADO';
            $envio->update();
        }

        //=========== DEVOLUCIÓN PARCIAL ========
        if ($nota->codMotivo === '07') {

            //======== ANALIZAR DETALLES DE LA VENTA =======
            $detalles_venta =   Detalle::where('documento_id', $documento->id)->get();
            $productos      =   $detalles_venta->where('tipo', 'PRODUCTO');

            if ($productos->count() > 0) {
                $todos_anulados = $productos->every(function ($item) {
                    return $item->estado === 'ANULADO';
                });

                if ($todos_anulados) {
                    $envio = EnvioVenta::where('documento_id', $documento->id)->first();

                    if ($envio) {
                        $envio->estado = 'ANULADO';
                        $envio->update();
                    }
                }
            }
        }
    }
}
