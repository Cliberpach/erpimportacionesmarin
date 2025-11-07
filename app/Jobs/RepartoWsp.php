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

class RepartoWsp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $reparto_id;
    private VentaService $s_venta;
    private WhatsappService $s_wsp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $reparto_id)
    {
        $this->reparto_id   =   $reparto_id;
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
            $ventas     =   $this->getVentas($this->reparto_id);

            $datos       =   [];

            $folder = public_path('storage/wsp_reparto');
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

            $this->s_wsp->enviarNotificacionReparto($datos);
        } catch (Throwable $th) {
            Log::channel('wsp_reparto')->error("Error al enviar WhatsApp desde RepartoJob: " . $th->getMessage());
        }
    }

    public function getVentas(int $reparto_id)
    {
        $ventas =   DB::table('repartos_detalle as rd')
            ->join('paquetes_embalados as pe', 'pe.id', 'rd.paquete_embalado_id')
            ->join('paquetes_embalados_detalle as ped', 'ped.paquete_embalado_id', 'pe.id')
            ->join('envios_ventas as ev', 'ev.id', 'ped.envio_venta_id')
            ->join('cotizacion_documento as cd', 'cd.id', 'ev.documento_id')
            ->where('rd.reparto_id', $reparto_id)
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
        $empresa    = Empresa::findOrFail(1);
        $serie      = $venta->serie . '-' . $venta->correlativo;
        $fecha      = Carbon::parse($venta->created_at)->format('d/m/Y');

        $mensajes = [
            <<<MSG
📦 *DETALLE DE SU PEDIDO*
✅ *Su compra ha sido procesada con éxito*

🏢 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🆔 *N°:* {$serie}
👤 *Cliente:* {$cliente_nombre}

📌 Su pedido está en camino.
🙏 ¡Gracias por su preferencia!
MSG,

            <<<MSG
🚀 *ACTUALIZACIÓN DE PEDIDO*
Su pedido ha sido enviado.

🏬 *{$empresa->razon_social}*
🔖 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
📄 *Comprobante:* {$serie}
👥 *Cliente:* {$cliente_nombre}

⚡ Pronto llegará a su destino.
🤗 ¡Gracias por confiar en nosotros!
MSG,

            <<<MSG
🛒 *PEDIDO CONFIRMADO*
🚚 *Su compra ya fue despachada*

🏢 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🔢 *N° Pedido:* {$serie}
👤 *Cliente:* {$cliente_nombre}

📌 Muy pronto recibirá su pedido.
🤝 ¡Gracias por elegirnos!
MSG,

            <<<MSG
📦 *ESTADO DE COMPRA*
✅ *Su pedido ya está en tránsito*

🏬 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🆔 *Pedido:* {$serie}
👤 *Cliente:* {$cliente_nombre}

🚚 Pronto llegará a sus manos.
🙌 ¡Gracias por su confianza!
MSG,

            <<<MSG
🛍️ *NOTIFICACIÓN DE PEDIDO*
🚚 *Su compra fue enviada*

🏢 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🔖 *N° Documento:* {$serie}
👤 *Cliente:* {$cliente_nombre}

📌 Muy pronto recibirá novedades.
🙏 ¡Gracias por preferirnos!
MSG,

            <<<MSG
📦 *ACTUALIZACIÓN DE COMPRA*
✅ *Su pedido ya fue despachado*

🏢 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🆔 *N° de Serie:* {$serie}
👤 *Cliente:* {$cliente_nombre}

🚚 Pronto llegará a su domicilio.
🤗 ¡Agradecemos su confianza!
MSG,

            <<<MSG
🛒 *ESTADO DE SU COMPRA*
🚀 *Su pedido ya salió de almacén*

🏬 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
📄 *N° Pedido:* {$serie}
👤 *Cliente:* {$cliente_nombre}

📌 Pronto estará con usted.
🙌 ¡Gracias por su preferencia!
MSG,

            <<<MSG
📢 *INFORMACIÓN DE PEDIDO*
🚚 *Su compra ha sido enviada con éxito*

🏢 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🆔 *Documento:* {$serie}
👤 *Cliente:* {$cliente_nombre}

⚡ Pronto recibirá la entrega.
🤝 ¡Gracias por confiar en nosotros!
MSG,

            <<<MSG
📦 *PEDIDO DESPACHADO*
✅ *Su compra ya fue enviada*

🏬 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🔢 *N° Serie:* {$serie}
👤 *Cliente:* {$cliente_nombre}

🚚 Su pedido está en camino.
🙌 ¡Gracias por elegirnos!
MSG,

            <<<MSG
🛍️ *CONFIRMACIÓN DE ENVÍO*
🚚 *Su pedido fue despachado correctamente*

🏢 *{$empresa->razon_social}*
🧾 RUC: {$empresa->ruc}

📅 *Fecha:* {$fecha}
🆔 *N° Documento:* {$serie}
👤 *Cliente:* {$cliente_nombre}

📌 Muy pronto estará con usted.
🤗 ¡Gracias por su preferencia!
MSG
        ];

        // Elige un mensaje al azar
        $mensaje = $mensajes[array_rand($mensajes)];

        return $mensaje;
    }
}
