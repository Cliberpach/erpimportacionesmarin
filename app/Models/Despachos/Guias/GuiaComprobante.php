<?php

namespace App\Models\Despachos\Guias;

use Illuminate\Database\Eloquent\Model;

class GuiaComprobante extends Model
{
    protected $table = 'guia_comprobantes';
    public $timestamps = true;
    protected $guarded = [''];

}
