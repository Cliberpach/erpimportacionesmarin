<?php

namespace App\Ventas;

use App\Mantenimiento\Condicion;
use Illuminate\Database\Eloquent\Model;
use App\Ventas\DetalleCuentaCliente;
use App\Ventas\CuentaCliente;
use App\Ventas\Documento\Detalle;
use App\Ventas\Documento\Documento;

class Nota extends Model
{
    protected $table    = 'nota_electronica';

    protected $fillable = [
        'documento_id',
        'tipDocAfectado',
        'numDocfectado',
        'codMotivo',
        'desMotivo',
        'tipoDoc',
        'fechaEmision',
        'tipoMoneda',
        'ruc_empresa',
        'empresa',
        'direccion_fiscal_empresa',
        'empresa_id',
        'cod_tipo_documento_cliente',
        'tipo_documento_cliente',
        'documento_cliente',
        'direccion_cliente',
        'cliente',
        'sunat',
        'tipo_nota',
        'correlativo',
        'serie',
        'ruta_comprobante_archivo',
        'nombre_comprobante_archivo',
        'mtoOperGravadas',
        'mtoIGV',
        'totalImpuestos',
        'mtoImpVenta',
        'ublVersion',
        'guia_tipoDoc',
        'guia_nroDoc',
        'ruta_qr',
        'hash',
        'code',
        'value',
        'estado',
        'user_id',
        'getCdrResponse',
        'getRegularizeResponse',
        'regularize',
        'cdr_response_id',
        'cdr_response_code',
        'cdr_response_description',
        'cdr_response_notes',
        'cdr_response_reference',
        'response_error_code',
        'response_error_message',
        'ruta_xml',
        'ruta_cdr',
        'nota_name',
        'pedido_id',
        'almacen_id',
        'sede_id',
        'almacen_nombre'
    ];


    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento', 'documento_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    protected static function booted()
    {
        static::created(function (Nota $nota) {
            //CREAR LOTE PRODUCTO
            $condicion = Condicion::find($nota->documento->condicion_id);
            if (strtoupper($condicion->descripcion) == 'CREDITO' || strtoupper($condicion->descripcion) == 'CRÉDITO') {
                if ($nota->documento->cuenta) {
                    $monto_notas = Nota::where('documento_id', $nota->documento_id)->sum('mtoImpVenta');
                    $monto  = $nota->documento->total - $monto_notas;
                    $cuenta_cliente = CuentaCliente::find($nota->documento->cuenta->id);
                    if ($monto > 0) {
                        $cuenta_cliente->monto = $monto;
                    } else {
                        $cuenta_cliente->monto = 0.00;
                        $cuenta_cliente->estado = 'PAGADO';
                    }

                    $monto_saldo = DetalleCuentaCliente::where('cuenta_cliente_id', $cuenta_cliente->id)->sum('monto');
                    if ($monto - $monto_saldo > 0) {
                        $cuenta_cliente->saldo = $monto - $monto_saldo;
                    } else {
                        $cuenta_cliente->saldo = 0.00;
                        $cuenta_cliente->estado = 'PAGADO';
                    }

                    $cuenta_cliente->update();
                }
            }

            $documento  = Documento::find($nota->documento->id);
            $detalles   = Detalle::where('documento_id', $nota->documento->id)->get();
            $cont = 0;

            foreach ($detalles as $detalle) {
                if ($detalle->cantidad == $detalle->detalles->sum('cantidad')) {
                    $cont = $cont + 1;
                }
            }

            if (count($detalles) == $cont) {
                $documento->sunat = '2';
                $documento->update();
            }
        });
    }
}
