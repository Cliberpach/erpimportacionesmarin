<?php

namespace App\Models\Almacenes\Traslados;

use Illuminate\Database\Eloquent\Model;

class Traslado extends Model
{
    protected $table    =   'traslados';
    protected $fillable = [
        'almacen_origen_id',
        'almacen_destino_id',
        'observacion',
        'sede_origen_id',
        'sede_destino_id',
        'fecha_traslado',
        'registrador_id',
        'registrador_nombre',
        'aprobador_id',
        'aprobador_nombre',
        'estado',
        'guia_id',
        'anulacion_usuario_id',
        'anulacion_usuario_nombre',
        'anulacion_fecha',
        'venta_id',
        'venta_serie',
        'usuario_envio_id',
        'usuario_envio_nombre',
        'fecha_envio',
        'usuario_entrega_id',
        'usuario_entrega_nombre',
        'fecha_entrega',
        'fecha_aprobacion',
        'envio_venta_id'
    ];
    public $timestamps  =   true;
}
