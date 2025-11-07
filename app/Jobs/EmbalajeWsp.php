<?php

namespace App\Jobs;

use App\Http\Services\Ventas\Ventas\VentaService;
use App\Http\Services\Whatsapp\WhatsappService;
use App\Mantenimiento\Empresa\Empresa;
use App\Ventas\Cliente;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmbalajeWsp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $paquete_id;
    private VentaService $s_venta;
    private WhatsappService $s_wsp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $paquete_id)
    {
        $this->paquete_id   =   $paquete_id;
        $this->s_venta      =   new VentaService();
        $this->s_wsp        =   new WhatsappService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $ventas     =   $this->getVentas($this->paquete_id);

            $datos       =   [];

            $folder = public_path('storage/wsp_embalaje');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            
            foreach ($ventas as $venta) {

                //====== PDF ========
                $res_pdf    =   $this->s_venta->getVoucherPdf($venta->id, 80);
                $pdf        =   $res_pdf['pdf'];
                $pdfContent =   $pdf->output();
                $pdf_name   =   $res_pdf['nombre'];

                //======== GUARDAR PDF ======
                $pdfPath = $folder . '/' . $pdf_name;
                file_put_contents($pdfPath, $pdfContent);
                $pdfUrl = url('storage/wsp_embalaje/' . $pdf_name);

                //======= MENSAJE =========
                $cliente    =   Cliente::findOrFail($venta->cliente_id);
                $mensaje    =   $this->getMessage($venta, $cliente->nombre);

                $telefono           =   trim($cliente->telefono_movil);
                if (ctype_digit($telefono) && strlen($telefono) === 9) {
                    $pdf_datos  =   ['pdf_url' => $pdfUrl, 'pdf_name' => $pdf_name];
                    $datos[]    =   ['pdf_datos' => $pdf_datos, 'telefono' => '51' . $telefono, 'mensaje' => $mensaje];
                }
            }

            $this->s_wsp->enviarNotificacionEmbalado($datos);
        } catch (Throwable $th) {
            Log::channel('wsp_embalaje')->error("Error al enviar WhatsApp desde EmbalajeJob: " . $th->getMessage());
        }
    }

    public function getVentas(int $paquete_id)
    {
        $ventas =   DB::table('paquetes_embalados_detalle as ped')
            ->join('envios_ventas as ev', 'ev.id', 'ped.envio_venta_id')
            ->join('cotizacion_documento as cd', 'cd.id', 'ev.documento_id')
            ->where('ped.paquete_embalado_id', $paquete_id)
            ->select(
                'cd.cliente_id',
                'ev.documento_id as id',
                'cd.serie',
                'cd.correlativo',
                'cd.created_at'
            )->get();
        return $ventas;
    }

    public function getMessage($venta, string $cliente_nombre): string
    {
        $empresa    =   Empresa::findOrFail(1);
        $serie      =   $venta->serie . '-' . $venta->correlativo;
        $fecha      =   Carbon::parse($venta->created_at)->format('d/m/Y');

        $mensaje    =   <<<MSG
                            📦 *ESTADO DE SU PEDIDO*
                            🛒 *SU PEDIDO ESTÁ EMBALADO*

                            🏢 *{$empresa->razon_social}*
                            🧾 RUC: {$empresa->ruc}

                            📅 *Fecha:* {$fecha}
                            🆔 *N°:* {$serie}
                            👤 *Cliente:* {$cliente_nombre}

                            📌 Pronto será enviado.
                            🤝 ¡Gracias por confiar en nosotros!
                            MSG;
        return $mensaje;
    }
}
