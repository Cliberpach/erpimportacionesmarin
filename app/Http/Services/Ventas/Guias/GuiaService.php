<?php

namespace App\Http\Services\Ventas\Guias;

use App\Almacenes\Conductor;
use App\Almacenes\Vehiculo;
use App\Http\Controllers\Utils\QRController;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\Sedes\Sede;
use App\Mantenimiento\Tabla\Detalle;
use App\Models\Almacenes\Transportista\Transportista;
use App\Ventas\Documento\Documento;
use App\Ventas\Guia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;

class GuiaService
{
    private GuiaValidaciones $s_validaciones;
    private GuiaCorrelativo $s_correlativo;
    private GuiaRepository $s_repositorio;

    public function __construct()
    {
        $this->s_validaciones = new GuiaValidaciones();
        $this->s_correlativo  = new GuiaCorrelativo();
        $this->s_repositorio  = new GuiaRepository();
    }

    public function store(array $data): Guia
    {
        $datos_validados    =   $this->s_validaciones->validacionStore($data);

        //========== CORRELATIVO DE LA GUÍA DEBE SER EL DE LA SEDE QUE USARÁ LA GUÍA ========
        $this->s_correlativo->comprobanteActivo($datos_validados->sede_usa_guia, $datos_validados->tipo_comprobante);

        //======== OBTENER CORRELATIVO Y SERIE ======
        $datos_correlativo          =   $this->s_correlativo->getCorrelativo($datos_validados->tipo_comprobante, $datos_validados->sede_usa_guia);
        $data['datos_correlativo']  =   $datos_correlativo;

        $motivo_traslado            =   Detalle::where('tabla_id', 34)->where('simbolo', $data['motivo_traslado'])->first();
        $data['motivo_traslado']    =   $motivo_traslado;

        //========= INSERTAR GUÍA MAESTRO ============
        $guia   =   $this->s_repositorio->insertarGuia($data, $datos_validados);
        $this->s_repositorio->insertarGuiaDetalle($data, $datos_validados, $guia);
        $this->setUrlFirmadaPdf($guia);

        //======= AMARRAR GUÍA A COMPROBANTES =========
        $this->s_repositorio->insertarGuiaComprobantes($guia, $data['envios']);

        //========== ACTUALIZAR ESTADO FACTURACIÓN A INICIADA ======
        DB::table('empresa_numeracion_facturaciones')
            ->where('empresa_id', 1)
            ->where('sede_id', $datos_validados->sede_usa_guia)
            ->where('tipo_comprobante', $datos_validados->tipo_comprobante->id)
            ->where('emision_iniciada', '0')
            ->where('estado', 'ACTIVO')
            ->update([
                'emision_iniciada'       => '1',
                'updated_at'             => Carbon::now()
            ]);

        return $guia;
    }


    /*
========= GENERAR URL FIRMADA PARA ACCESO AL PDF GUÍA SIN AUTENTICACIÓN =======
  "url_pdf" => "http://127.0.0.1:8000/pdf_show/13?signature=29e908c01e08907e9276d27802ec16c8264bf65e83e806372bf8d15877de36c3"
*/
    public function setUrlFirmadaPdf(Guia $guia)
    {
        $urlFirmada     =   URL::signedRoute('ventas.guiasremision.pdf_show', ['id' => $guia->id]);
        $guia->url_pdf  =   $urlFirmada;
        $guia->update();
    }

    public function getPdf(int $id): PDF
    {
        $guia           =   Guia::with(['documento', 'detalles'])->findOrFail($id);
        $this->setQr($guia);

        $destinatario   =   null;
        $partida        =   Sede::find($guia->punto_partida_id);

        if ($guia->motivo_traslado_simbolo === '01') { //===== VENTA ====
            $destinatario   =   DB::select('SELECT
                                    c.nombre,
                                    c.direccion,
                                    c.distrito_id,
                                    d.nombre AS departamento_nombre,
                                    p.nombre AS provincia_nombre,
                                    di.nombre AS distrito_nombre
                                    FROM clientes AS c
                                    INNER JOIN departamentos AS d ON d.id = c.departamento_id
                                    INNER JOIN provincias AS p ON p.id = c.provincia_id
                                    INNER JOIN distritos AS di ON di.id = c.distrito_id
                                    WHERE c.id = ?', [$guia->cliente_id])[0];
        }
        if ($guia->motivo_traslado_simbolo === '04') { //===== TRASLADO INTERNO ====
            $destinatario   =   Sede::find($guia->punto_llegada_id);
        }


        $vehiculo   =   null;
        $conductor  =   null;
        $transportista = null;

        if (!$guia->categoria_M1L) {
            $vehiculo   =   Vehiculo::find($guia->vehiculo_id);
            if ($guia->modalidad_traslado_simbolo === '01') { //=== TRANSPORTE PÚBLICO ====
                $transportista = Transportista::findOrFail($guia->transportista_id);
            }
            if ($guia->modalidad_traslado_simbolo === '02') { //=== TRANSPORTE PRIVADO ====
                $conductor = Conductor::findOrFail($guia->conductor_id);
            }
        }

        $empresa    =   Empresa::first();
        $sede       =   Sede::find($guia->sede_usa_guia);
        $comprobantes_afectados =   Documento::where('guia_id', $id)->select('serie', 'correlativo')->get();

        $pdf    =   PdfFacade::loadview('ventas.guias.reportes.guia', [
            'guia'          => $guia,
            'empresa'       => $empresa,
            'sede'          => $sede,
            'conductor'     => $conductor,
            'vehiculo'      => $vehiculo,
            'destinatario'  => $destinatario,
            'partida'       => $partida,
            'transportista' => $transportista,
            'comprobantes_afectados'    =>  $comprobantes_afectados
        ])->setPaper('a4')->setWarnings(false);

        return $pdf;
    }

    //======= CREAR QR PARA LA GUÍA ========
    public function setQr(Guia $guia)
    {
        $qr_nombre      =   $guia->serie . '-' . $guia->correlativo;
        $ruta_qr        =   QRController::generarQrSimple($qr_nombre, $guia->url_pdf);
        $guia->ruta_qr  =   $ruta_qr;
        $guia->update();
    }
}
