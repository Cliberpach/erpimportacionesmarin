<?php

namespace App\Http\Services\Ventas\Guias;

use App\Http\Services\Almacen\ProductoColorTalla\ProductoColorTallaManager;
use App\Http\Services\Almacen\ProductoColorTalla\ProductoColorTallaService;
use App\Mantenimiento\Sedes\Sede;
use App\Models\Despachos\Guias\GuiaComprobante;
use App\User;
use App\Ventas\Cliente;
use App\Ventas\DetalleGuia;
use App\Ventas\Documento\Documento;
use App\Ventas\EnvioVenta;
use App\Ventas\Guia;
use Exception;
use Illuminate\Support\Facades\DB;

class GuiaRepository
{
    private ProductoColorTallaService $s_pct;

    public function __construct()
    {
        $this->s_pct    =   new ProductoColorTallaService();
    }

    public function insertarGuia(array $data, object $datos_validados): Guia
    {
        $guia                           =   new Guia();
        $guia->cantidad_productos       =   $this->calcularCantidadProductos($datos_validados->lstGuia);
        $guia->peso_productos           =   $data['peso'];
        $guia->almacen_id               =   $data['almacen'];
        $guia->fecha_emision            =   $data['fecha_emision'];

        //======== MOTIVO TRASLADO ========
        $guia->motivo_traslado_id       =   $data['motivo_traslado']->id;
        $guia->motivo_traslado_simbolo  =   $data['motivo_traslado']->simbolo;
        $guia->motivo_traslado_nombre   =   $data['motivo_traslado']->descripcion;

        //======== MODALIDAD TRASLADO =======
        if ($data['modalidad_traslado'] === '01') {
            $guia->modalidad_traslado_simbolo   =   $data['modalidad_traslado'];
            $guia->modalidad_traslado_nombre    =   'TRANSPORTE PUBLICO';
            $guia->transportista_id             =   $data['conductor'];
            $guia->transportista_tipo_doc_codigo = ltrim($datos_validados->transportista->tipo_documento_codigo, '0');
            $guia->transportista_num_doc        =   $datos_validados->transportista->nro_documento;
            $guia->transportista_rzn_social     =   $datos_validados->transportista->nombre;
            $guia->transportista_nro_mtc        =   $datos_validados->transportista->mtc;
        }
        if ($data['modalidad_traslado'] === '02') {
            $guia->modalidad_traslado_simbolo   =   $data['modalidad_traslado'];
            $guia->modalidad_traslado_nombre    =   'TRANSPORTE PRIVADO';

            if (!$datos_validados->categoria_M1L) {
                $guia->conductor_id                 =   $data['conductor'];
                $guia->conductor_tipo_doc_codigo = ltrim($datos_validados->conductor->tipo_documento_codigo, '0');
                $guia->conductor_nro_doc            =   $datos_validados->conductor->nro_documento;
                $guia->conductor_licencia           =   $datos_validados->conductor->licencia;
                $guia->conductor_nombres            =   $datos_validados->conductor->nombres;
                $guia->conductor_apellidos          =   $datos_validados->conductor->apellidos;
            }
        }

        $guia->fecha_traslado                   =   $data['fecha_traslado'];
        $guia->peso                             =   $data['peso'];
        $guia->unidad                           =   $data['unidad'];

        if ($datos_validados->categoria_M1L) {
            $guia->categoria_M1L                =   true;
        } else {
            $guia->vehiculo_id                  =   $data['vehiculo'];
            $guia->vehiculo_placa               =   $datos_validados->vehiculo->placa;
        }

        //======= PUNTO PARTIDA =====
        $sede_partida                           =   Sede::find($data['sede_usa_guia']);
        $guia->punto_partida_id                 =   $data['sede_usa_guia'];
        $guia->ubigeo_partida                   =   $sede_partida->distrito_id;
        $guia->direccion_partida                =   $sede_partida->direccion;

        //======== PUNTO LLEGADA ======
        //======= TRASLADO ENTRE ESTABLECIMIENTOS ======
        if ($data['motivo_traslado']->simbolo === '04') {
            $sede_llegada                           =   Sede::find($data['sede_genera_guia']);
            $guia->punto_llegada_id                 =   $data['sede_genera_guia'];
            $guia->ubigeo_llegada                   =   $sede_llegada->distrito_id;
            $guia->direccion_llegada                =   $sede_llegada->direccion;
        }

        //======== VENTA ======
        if ($data['motivo_traslado']->simbolo === '01') {
            $cliente                                =   Cliente::find($data['cliente']);

            if (!$cliente->distrito_id) {
                throw new Exception("EL CLIENTE NO TIENE UBIGEO!!!");
            }
            if (!$cliente->direccion) {
                throw new Exception("EL CLIENTE NO TIENE DIRECCIÓN!!!");
            }

            $guia->cliente_id                       =   $cliente->id;
            $guia->ubigeo_llegada                   =   $cliente->distrito_id;
            $guia->direccion_llegada                =   $cliente->direccion;
        }

        $guia->sede_genera_guia     =   $data['sede_genera_guia'];
        $guia->sede_usa_guia        =   $data['sede_usa_guia'];

        $registrador                =   User::find($data['registrador_id']);
        $guia->registrador_id       =   $data['registrador_id'];
        $guia->registrador_nombre   =   $registrador->usuario;
        $guia->empresa_id           =   1;

        $guia->correlativo          =   $data['datos_correlativo']->correlativo;
        $guia->serie                =   $data['datos_correlativo']->serie;

        //========= EN CASO SEA TRASLADO =======
        if (isset($data['traslado'])) {
            $guia->traslado_id  =   $data['traslado'];
        }

        //====== EN CASO DE VENTA =====
        if (isset($data['venta'])) {
            $guia->documento_id  =   $data['venta'];
        }

        $guia->save();
        return $guia;
    }

    public function insertarGuiaDetalle(array $data, object $datos_validados, Guia $guia)
    {
        //========== GRABAR DETALLE =======
        foreach ($datos_validados->lstGuia as  $producto) {
            foreach ($producto->tallas as  $talla) {

                //====== COMPROBAR SI EXISTE EL PRODUCTO COLOR TALLA EN EL ALMACÉN =====
                $existe =   $this->s_pct->getProductoColorTalla($data['almacen'], $producto->producto_id, $producto->color_id, $talla->talla_id);

                if (!$existe) {
                    throw new Exception($producto->producto_nombre . '-' . $producto->color_nombre . '-' . $talla->talla_nombre . ', NO EXISTE EN EL ALMACÉN!!!');
                }

                //======= VALIDAR STOCKS EN CASO NO SEA TRASLADO ======
                if (!isset($data['traslado']) && !isset($data['venta']) && !isset($data['envio'])) {
                    if (($talla->cantidad > $existe->stock) || ($talla->cantidad > $existe->stock_logico)) {
                        throw new Exception($producto->producto_nombre . '-' . $producto->color_nombre . '-' . $talla->talla_nombre . ', STOCK INSUFICIENTE!!!');
                    }
                }

                $guia_detalle                   =   new DetalleGuia();
                $guia_detalle->guia_id          =   $guia->id;
                $guia_detalle->almacen_id       =   $guia->almacen_id;
                $guia_detalle->producto_id      =   $producto->producto_id;
                $guia_detalle->color_id         =   $producto->color_id;
                $guia_detalle->talla_id         =   $talla->talla_id;
                $guia_detalle->cantidad         =   $talla->cantidad;
                $guia_detalle->codigo_producto  =   $existe->producto_codigo;
                $guia_detalle->nombre_modelo    =   $existe->modelo_nombre;
                $guia_detalle->nombre_producto  =   $existe->producto_nombre;
                $guia_detalle->nombre_color     =   $existe->color_nombre;
                $guia_detalle->nombre_talla     =   $existe->talla_nombre;
                $guia_detalle->unidad           =   'NIU';
                $guia_detalle->save();

                //===== ACTUALIZANDO STOCK SOLO SI LA GUÍA NO DEPENDE DE OTRO DOCUMENTO ===========
                if (!isset($data['traslado']) && !isset($data['venta']) && !isset($data['envio'])) {
                    DB::update(
                        'UPDATE producto_color_tallas
                        SET stock = stock - ?, stock_logico = stock_logico - ?
                        WHERE
                        almacen_id = ?
                        AND producto_id = ?
                        AND color_id = ?
                        AND talla_id = ?',
                        [
                            $talla->cantidad,
                            $talla->cantidad,
                            $guia->almacen_id,
                            $producto->producto_id,
                            $producto->color_id,
                            $talla->talla_id
                        ]
                    );
                }
            }
        }
    }

    public function insertarGuiaComprobantes(Guia $guia, $envios)
    {
        foreach ($envios as $envio) {
            if ($envio->tipo_venta_id == 129) {
                $guia_comprobante                       =   new GuiaComprobante();
                $guia_comprobante->guia_remision_id     =   $guia->id;
                $guia_comprobante->comprobante_id       =   $envio->convert_en_id;
                $guia_comprobante->comprobante_serie    =   $envio->convert_en_serie;
                $guia_comprobante->save();

                $venta_bd           =   Documento::findOrFail($envio->convert_en_id);
                $venta_bd->guia_id  =   $guia->id;
                $venta_bd->update();
            } else {
                $guia_comprobante                       =   new GuiaComprobante();
                $guia_comprobante->guia_remision_id     =   $guia->id;
                $guia_comprobante->comprobante_id       =   $envio->documento_id;
                $guia_comprobante->comprobante_serie    =   $envio->documento_nro;
                $guia_comprobante->save();

                $venta_bd           =   Documento::findOrFail($envio->documento_id);
                $venta_bd->guia_id  =   $guia->id;
                $venta_bd->update();
            }

            $envio              =   EnvioVenta::findOrFail($envio->id);
            if (!$envio) {
                throw new Exception("LA VENTA " . $envio->documento_nro . " NECESITA DATOS DE ENVÍO PARA GENERAR LA GUÍA");
            }

            $envio->guia_id     =   $guia->id;
            $envio->guia_serie  =   $guia->serie . '-' . $guia->correlativo;
            $envio->update();
        }
    }

    public function calcularCantidadProductos($lstProductos)
    {
        $cantidad   =   0;
        foreach ($lstProductos as $producto) {
            foreach ($producto->tallas as $talla) {
                $cantidad   +=  $talla->cantidad;
            }
        }
        return $cantidad;
    }
}
