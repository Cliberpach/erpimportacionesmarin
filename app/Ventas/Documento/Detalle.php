<?php

namespace App\Ventas\Documento;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Detalle extends Model
{
    protected $table = 'cotizacion_documento_detalles';
    protected $guarded = [''];


    public function detalles()
    {
        return $this->hasMany('App\Ventas\NotaDetalle', 'detalle_id', 'id');
    }

    public function documento()
    {
        return $this->belongsTo('App\Ventas\Documento\Documento');
    }

    public function lote()
    {
        return $this->belongsTo('App\Almacenes\LoteProducto', 'lote_id');
    }

    public function producto()
    {
        return $this->belongsTo('App\Almacenes\Producto');
    }

    protected static function booted()
    {
        static::created(function (Detalle $detalle) {

            $producto_color_talla = DB::table('producto_color_tallas')
                                    ->where('producto_id', $detalle->producto_id)
                                    ->where('color_id', $detalle->color_id)
                                    ->where('talla_id', $detalle->talla_id)
                                    ->first();
        });
    }
}
