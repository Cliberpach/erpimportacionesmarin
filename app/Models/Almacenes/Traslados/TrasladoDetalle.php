<?php

namespace App\Models\Almacenes\Traslados;

use Illuminate\Database\Eloquent\Model;

class TrasladoDetalle extends Model
{
    protected $table = 'traslados_detalle';

    protected $fillable = [
        'traslado_id',
        'almacen_id',
        'producto_id',
        'color_id',
        'talla_id',
        'almacen_nombre',
        'producto_nombre',
        'color_nombre',
        'talla_nombre',
        'cantidad',
        'tipo'
    ];

    public $timestamps = true;
}
