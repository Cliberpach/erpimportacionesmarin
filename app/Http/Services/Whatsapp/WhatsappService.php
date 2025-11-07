<?php

namespace App\Http\Services\Whatsapp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsappService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function enviarMensaje(array $numeros, string $mensaje, string $pdf_url, string $pdf_name)
    {

        Log::channel('wsp_venta')->info('Números recibidos', ['numeros' => $numeros]);

        $url = config('services.wsp.url') . '/send-message';
        Log::channel('wsp_venta')->info($url);

        $numeros = array_filter($numeros, function ($numero) {
            return $numero && strlen($numero) === 9 && ctype_digit($numero);
        });

        if (empty($numeros)) {
            return null;
        }

        $numerosConPrefijo = array_map(fn($n) => '51' . $n, $numeros);
        Log::channel('wsp_venta')->info('Números listos para enviar', ['numeros' => $numerosConPrefijo]);


        try {
            $response = Http::post($url, [
                'numbers'   => $numerosConPrefijo,
                'message'   => $mensaje,
                'pdf_url'   => $pdf_url,
                'pdf_name'  => $pdf_name
            ]);

            Log::channel('wsp_venta')->info('Respuesta WSP', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $th) {
            Log::channel('wsp_venta')->error('Error al enviar WSP VENTA: ' . $th->getMessage());
        } finally {
            $filePath = public_path('storage/wsp_ventas/' . $pdf_name);
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::channel('wsp_venta')->info("PDF eliminado: {$pdf_name}");
            }
        }

        return $response;
    }

    public function enviarNotificacionEmbalado(array $datos)
    {
        $url = config('services.wsp.url') . '/send-embalaje';

        try {
            $response = Http::post($url, $datos);

            Log::channel('wsp_embalaje')->info('Respuesta WSP Embalaje', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response;
        } catch (Throwable $th) {
            Log::channel('wsp_embalaje')->error('Error al enviar Notificación Embalaje: ' . $th->getMessage());
            return null;
        }
    }

    public function enviarNotificacionReparto(array $datos)
    {
        $url = config('services.wsp.url') . '/send-reparto';
        $response = Http::post($url, $datos);
    }
}
