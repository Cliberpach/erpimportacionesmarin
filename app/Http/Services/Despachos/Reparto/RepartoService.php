<?php

namespace App\Http\Services\Despachos\Reparto;

use App\Mantenimiento\Empresa\Empresa;
use App\Models\Despachos\PaqueteEmbalado;
use App\Models\Despachos\Repartos\Reparto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RepartoService
{

    private RepartoRepository $s_repository;
    private RepartoValidacion $s_validacion;

    public function __construct()
    {
        $this->s_repository     =   new RepartoRepository();
        $this->s_validacion     =   new RepartoValidacion();
    }
    public function pdfBultos(int $id, int $nro_bultos): array
    {
        $empresa = Empresa::first();

        $despacho = PaqueteEmbalado::findOrFail($id);

        $pdf = Pdf::loadview('despachos.reparto.pdf.pdf_bultos', [
            'empresa'       =>  $empresa,
            'nro_bultos'    =>  $nro_bultos,
            'despacho'      =>  $despacho
        ])->setPaper('a4')
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        $fileName = $despacho->distrito . '-' .
            $despacho->cliente_nombre . '-' .
            $despacho->created_at->format('Ymd_His') . '.pdf';

        return [
            'pdf'  => $pdf,
            'name' => $fileName,
        ];
    }

    public function store(array $data):Reparto
    {
        $this->s_validacion->validacionStore($data);
        $lst_detalle = json_decode($data['lstDetalleReparto']);
        $codigo = $this->generarCodigo();
        $reparto = $this->s_repository->registrarReparto($codigo, $data);
        $this->s_repository->registrarDetalleReparto($reparto->id, $lst_detalle);
        $this->s_repository->actualizarEstadoPaquetes($lst_detalle, 'DESPACHADO', $reparto->id);
        return $reparto;
    }

    public function generarCodigo()
    {
        $nro = Reparto::count() + 1;
        $codigo = 'RT-' . str_pad($nro, 10, '0', STR_PAD_LEFT);

        return $codigo;
    }

    public function getMdlRShow(int $id): array
    {
        $paquetes   =   PaqueteEmbalado::where('reparto_id', $id)->get();
        $reparto    =   Reparto::findOrFail($id);

        return ['paquetes' => $paquetes, 'reparto' => $reparto];
    }

    public function getEnviosPorPaquete(int $paquete_id): array
    {
        $envios =   DB::table('paquetes_embalados_detalle as ped')
                    ->join('envios_ventas as ev','ev.id','ped.envio_venta_id')
                    ->select('ev.*')
                    ->where('ped.paquete_embalado_id',$paquete_id)
                    ->get();

        return  $envios->toArray();;
    }
}
