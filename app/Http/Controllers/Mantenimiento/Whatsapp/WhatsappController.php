<?php

namespace App\Http\Controllers\Mantenimiento\Whatsapp;

use App\Http\Controllers\Controller;
use App\Mantenimiento\Condicion;
use App\Mantenimiento\Tabla\Detalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class WhatsappController extends Controller
{
    public function index()
    {
        $this->authorize('haveaccess', 'whatsapp.index');
        return view('mantenimiento.whatsapp.index');
    }


}
