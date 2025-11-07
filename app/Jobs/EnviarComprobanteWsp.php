<?php

namespace App\Jobs;

use App\Http\Services\Ventas\Ventas\VentaManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnviarComprobanteWsp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ventaId;
    protected $size;
    protected VentaManager $venta_manager;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ventaId, $size = 80)
    {
        $this->ventaId          =   $ventaId;
        $this->size             =   $size;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::channel('wsp_venta')->error("ENVIO WSP ".$this->ventaId.'-'.$this->size);
            $this->venta_manager    =   new VentaManager();
            $this->venta_manager->enviarPdfWsp($this->ventaId, $this->size);
        } catch (Throwable $th) {
            Log::channel('wsp_venta')->error("Error al enviar WhatsApp desde Job: " . $th->getMessage());
        }
    }
}
