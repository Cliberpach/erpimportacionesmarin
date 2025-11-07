<?php

namespace App\Http\Services\Despachos\Embalaje;

use App\Almacenes\Almacen;
use App\Almacenes\Conductor;
use App\Almacenes\Talla;
use App\Almacenes\Vehiculo;
use App\Http\Controllers\UtilidadesController;
use App\Http\Services\Ventas\Guias\GuiaService;
use App\Mantenimiento\Empresa\Empresa;
use App\Mantenimiento\MetodoEntrega\MetodoEntrega;
use App\Mantenimiento\Sedes\Sede;
use App\Models\Almacenes\Transportista\Transportista;
use App\Models\Despachos\PaqueteEmbalado;
use App\User;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\Guia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;
use Picqer\Barcode\BarcodeGeneratorPNG;

class EmbalajeService
{
    private EmbalajeRepository $s_repository;
    private EmbalajeValidaciones $s_validaciones;
    private GuiaService $s_guia;

    public function __construct()
    {
        $this->s_repository = new EmbalajeRepository();
        $this->s_validaciones = new EmbalajeValidaciones();
        $this->s_guia = new GuiaService();
    }

    public function getMdlEmbalaje(int $id): array
    {
        $data = [];

        $similares                          =   $this->s_repository->getEnviosSimilares($id);
        $data['similares']                  =   $similares;
        $data['detalle']                    =   $this->unirDetallesEnvios($similares);
        $data['envio']                      =   EnvioVenta::findOrFail($id);
        $data['empresa_envio']              =   MetodoEntrega::select('boleta_obligatorio', 'guia_obligatorio')->findOrFail($data['envio']->empresa_envio_id);
        $data['tiene_reservas_pendientes']  =   $this->s_repository->tieneReservasPendientes($data['envio']);

        return $data;
    }

    public function generarPaqueteEmbalaje(array $datos): PaqueteEmbalado
    {
        $id = $datos['id'];

        $data           =   $this->getMdlEmbalaje($id);
        $this->s_validaciones->validacionEmbalaje($data);

        $similares              =   $data['similares'];
        $empresa_envio          =   $data['empresa_envio'];
        $detalle_junto          =   $this->unirDetallesEnvios($similares);
        $datos['detalle_junto'] =   $detalle_junto;
        $datos['similares']     =   $similares;

        $codigo         =   $this->generaCodigoPaquete($detalle_junto);
        $qr_info        =   $this->generarQr($codigo);
        $barcode_info   =   $this->generarCodigoBarras($codigo);

        $paquete    =   $this->s_repository->crearPaqueteEmbalado($similares, $qr_info, $barcode_info, $id);

        $this->s_repository->agregarDetallePaqueteEmbalado($similares, $paquete->id);
        $this->s_repository->actualizarEstadoEnvios($similares, 'EMBALADO');

        if($empresa_envio->guia_obligatorio){
            $this->s_repository->enlazarPaqueteGuia($id,$paquete);
        }

        return $paquete;
    }

    public function unirDetallesEnvios($lstEnvios): array
    {
        $detalle_agrupado = [];

        foreach ($lstEnvios as $envio) {
            $envio_detalle = Detalle::where('documento_id', $envio->documento_id)->where('tipo','PRODUCTO')->get();

            foreach ($envio_detalle as $det) {
                $key = $det->producto_id . '-' . $det->color_id . '-' . $det->talla_id;

                if (!isset($detalle_agrupado[$key])) {
                    $detalle_agrupado[$key] = (object)[
                        'almacen_id'        =>  $det->almacen_id,
                        'producto_id'       =>  $det->producto_id,
                        'color_id'          =>  $det->color_id,
                        'talla_id'          =>  $det->talla_id,
                        'producto_nombre'   =>  $det->nombre_producto,
                        'color_nombre'      =>  $det->nombre_color,
                        'talla_nombre'      =>  $det->nombre_talla,
                        'modelo_nombre'     =>  $det->nombre_modelo,
                        'cantidad'          =>  (int)$det->cantidad,
                        'precio_unitario_nuevo' =>  $det->precio_unitario_nuevo
                    ];
                } else {
                    $detalle_agrupado[$key]->cantidad += (int)$det->cantidad;
                }
            }
        }

        return array_values($detalle_agrupado);
    }

    function generaCodigoPaquete(): string
    {
        $ultimo = PaqueteEmbalado::max('id') ?? 0;
        $nro = $ultimo + 1;

        $codigo = 'PKT-' . str_pad($nro, 10, '0', STR_PAD_LEFT);

        return $codigo;
    }


    public function generarQr($data_qr): array
    {
        $miQr = QrCode::format('svg')
            ->size(130)
            ->backgroundColor(0, 0, 0)
            ->color(255, 255, 255)
            ->margin(1)
            ->generate($data_qr);

        $folder = public_path('storage/paquetes_embalados/qrs');

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $fileName = 'qr_' . uniqid() . '.svg';
        $filePath = $folder . '/' . $fileName;

        file_put_contents($filePath, $miQr);

        return [
            'fileName'  => $fileName,
            'filePath'  => 'storage/paquetes_embalados/qrs/' . $fileName,
            'codigo'    => $data_qr
        ];
    }

    public function generarCodigoBarras($codigo)
    {
        //======== GENERAR IMG DEL COD BARRAS ========
        $generatorPNG   =   new BarcodeGeneratorPNG();
        $code           =   $generatorPNG->getBarcode($codigo, $generatorPNG::TYPE_CODE_128);
        $name           =   $codigo . '.png';

        $folder = public_path('storage/paquetes_embalados/barcodes');

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $filePath = $folder . '/' . $name;

        file_put_contents($filePath, $code);

        return [
            'fileName'  => $name,
            'filePath'  => 'storage/paquetes_embalados/barcodes/' . $name,
            'codigo'    => $codigo
        ];
    }

    /*public function prepararDatosGuia($datos): array
    {

        $venta              =   Documento::findOrFail($datos['id']);
        $almacen            =   Almacen::findOrFail($venta->almacen_id);
        $similares          =   $datos['similares'];
        $detalle_agrupado   =   collect($datos['detalle_junto']);
        $detalle_formateado =   UtilidadesController::formatearArrayDetalle($detalle_agrupado);

        $dto    =   [
            'sede_genera_guia'  =>  Auth::user()->sede_id,
            'sede_usa_guia'     =>  $almacen->sede_id,
            'cliente_destino'   =>  $venta->cliente_id,
            'venta'             =>  'MULTIVENTAS',
            'lstGuia'           =>  json_encode($detalle_formateado)
        ];

        return $dto;
    }*/

    public function getGuiaCreateEnvio(int $envio_id): array
    {
        $datos              =   $this->getMdlEmbalaje($envio_id);
        $similares          =   $datos['similares'];
        $detalle_junto      =   $this->unirDetallesEnvios($similares);
        $detalle_agrupado   =   collect($detalle_junto);
        $venta_detalle      =   UtilidadesController::formatearArrayDetalle($detalle_agrupado);

        $envio              =   EnvioVenta::findOrFail($envio_id);
        $venta              =   Documento::find($envio->documento_id);
        $almacen_origen     =   Almacen::find($venta->almacen_id);

        $sede_id            =   Auth::user()->sede_id;

        $sede_origen        =   Sede::find($almacen_origen->sede_id);
        $sede_documento     =   Sede::find($venta->sede_id);

        $cliente            =   DB::select('SELECT
                                c.id,
                                c.direccion,
                                c.tipo_documento,
                                c.documento,
                                c.nombre,
                                d.nombre AS departamento_nombre,
                                pr.nombre AS provincia_nombre,
                                di.nombre AS distrito_nombre,
                                c.distrito_id
                                FROM clientes AS c
                                INNER JOIN departamentos AS d ON d.id = c.departamento_id
                                INNER JOIN provincias AS pr ON pr.id = c.provincia_id
                                INNER JOIN distritos AS di ON di.id = c.distrito_id
                                WHERE c.id = ?', [$venta->cliente_id])[0];

        $almacenes          =   Almacen::where('estado', 'ACTIVO')->get();

        $registrador        =   User::find(Auth::user()->id);
        $tallas             =   Talla::where('estado', 'ACTIVO')->get();
        $empresas           =   Empresa::where('estado', 'ACTIVO')->get();
        $conductores        =   Conductor::where('estado', 'ACTIVO')->get();
        $transportistas     =   Transportista::where('estado', 'ACTIVO')->get();
        $vehiculos          =   Vehiculo::where('estado', 'ACTIVO')->get();

        $tipos_documento    =   DB::select('SELECT
                                td.*
                                from tabladetalles as td
                                where td.tabla_id = 3');

        $sedes              =   Sede::where('estado', 'ACTIVO')->where('id', '<>', $sede_id)->get();

        $motivos_traslado   =   DB::select('SELECT
                                td.*
                                from tabladetalles as td
                                where
                                td.tabla_id = 34
                                AND td.simbolo IN ("01","04")');

        return [
            'similares'         => $similares,
            'detalle_junto'     => $detalle_junto,
            'detalle_agrupado'  => $detalle_agrupado,
            'venta_detalle'     => $venta_detalle,

            'envio'             => $envio,
            'venta'             => $venta,
            'almacen_origen'    => $almacen_origen,
            'sede_origen'       => $sede_origen,
            'sede_documento'    => $sede_documento,

            'cliente'           => $cliente,
            'almacenes'         => $almacenes,
            'registrador'       => $registrador,
            'tallas'            => $tallas,
            'empresas'          => $empresas,
            'conductores'       => $conductores,
            'transportistas'    => $transportistas,
            'vehiculos'         => $vehiculos,

            'tipos_documento'   => $tipos_documento,
            'sedes'             => $sedes,
            'motivos_traslado'  => $motivos_traslado,

            'sede_id'           => $sede_id,
            'ventas'            => $similares
        ];
    }

    public function storeGuiaEnvio(array $datos):Guia
    {
        $dto    =   $this->prepararDatosGuia($datos);
        $guia   =   $this->s_guia->store($dto);

        return $guia;
    }

    public function prepararDatosGuia(array $datos)
    {
        $envio_id           =   $datos['envio_id'];
        $envio              =   EnvioVenta::findOrFail($envio_id);
        $venta              =   Documento::find($envio->documento_id);
        $almacen            =   Almacen::findOrFail($venta->almacen_id);
        $sede_genera_guia   =   $venta->sede_id;
        $sede_usa_guia      =   $almacen->sede_id;

        $data                   =   $this->getMdlEmbalaje($envio_id);
        $similares              =   $data['similares'];
        $datos['similares']     =   $similares;

        $this->s_validaciones->validacionGuiaStore($datos);

        $detalle_junto          =   $this->unirDetallesEnvios($similares);
        $datos['detalle_junto'] =   $detalle_junto;
        $datos['similares']     =   $similares;
        $detalle_agrupado       =   collect($datos['detalle_junto']);
        $detalle_formateado     =   UtilidadesController::formatearArrayDetalle($detalle_agrupado);

        $dto    =   [
            'sede_id'               =>  $sede_usa_guia,
            'sede_genera_guia'      =>  $sede_usa_guia,
            'sede_usa_guia'         =>  $sede_usa_guia,
            'modalidad_traslado'    =>  $datos['modalidad_traslado'],
            'categoria_M1L'         =>  isset($datos['categoria_M1L']) ? true : false,
            'almacen'               =>  $venta->almacen_id,
            'conductor'             =>  $datos['conductor'],
            'motivo_traslado'       =>  $datos['motivo_traslado'],
            "peso"                  =>  "0.1",
            'fecha_emision'         =>  $datos['fecha_emision'],
            'fecha_traslado'        =>  $datos['fecha_traslado'],
            'unidad'                =>  $datos['unidad'],
            'vehiculo'              =>  $datos['vehiculo'],
            'cliente'               =>  $venta->cliente_id,
            'registrador_id'        =>  Auth::user()->id,
            'envio'                 =>  $envio_id,
            'envios'                =>  $similares,
            'lstGuia'               =>  json_encode($detalle_formateado)
        ];
        return $dto;
    }
}
