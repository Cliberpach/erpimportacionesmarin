<?php

namespace App\Http\Services\Despachos\RepartoDetalle;

use App\Http\Services\Despachos\Embalaje\EmbalajeService;
use App\Mantenimiento\Empresa\Empresa;
use App\Models\Despachos\PaqueteEmbalado;
use App\Models\Despachos\PaqueteEmbaladoDetalle;
use App\Ventas\EnvioVenta;
use Barryvdh\DomPDF\Facade\Pdf;

class RepartoDetalleService
{
    private EmbalajeService $s_embalaje;

    public function __construct() {
        $this->s_embalaje      =   new EmbalajeService();
    }

    public function pdfBultos(int $id, int $nro_bultos): array
    {
        $empresa    =   Empresa::first();

        $obs_rotulo =   '';
        $despacho   =   PaqueteEmbalado::findOrFail($id);
        $envios     =   PaqueteEmbaladoDetalle::where('paquete_embalado_id', $id)->get();

        $lst_envios =  [];
        foreach ($envios as $envioDetalle) {
            $envio          =   EnvioVenta::findOrFail($envioDetalle->envio_venta_id);

            $lst_envios[]   =   $envio;
            $obs_rotulo .= substr($envio->obs_rotulo, 0, 100 - strlen($obs_rotulo));

            if (strlen($obs_rotulo) >= 100) {
                $obs_rotulo = substr($obs_rotulo, 0, 100);
                break;
            }
        }

        $detalle_paquete = $this->s_embalaje->unirDetallesEnvios($lst_envios);

        $pdf = Pdf::loadview('despachos.reparto.pdf.pdf_va5', [
            'empresa'       =>  $empresa,
            'nro_bultos'    =>  $nro_bultos,
            'despacho'      =>  $despacho,
            'obs_rotulo'    =>  $obs_rotulo,
            'detalle_paquete' => $detalle_paquete,
        ])
            ->setPaper([0, 0, 420.94, 595.28], 'portrait')
            ->setWarnings(false);

        $fileName = $despacho->distrito . '-' .
            $despacho->cliente_nombre . '-' .
            $despacho->created_at->format('Ymd_His') . '.pdf';

        return [
            'pdf'  => $pdf,
            'name' => $fileName,
        ];
    }
}
