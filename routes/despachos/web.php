<?php

use App\Http\Controllers\Ventas\GuiaController;
use Illuminate\Support\Facades\Route;

Route::prefix('despachos')->group(function () {

    Route::prefix('embalaje')->group(function () {

        Route::get('/', 'Despachos\EmbalajeController@index')->name('despachos.embalaje.index');
        Route::get('/getEmbalaje', 'Despachos\EmbalajeController@getEmbalaje')->name('despachos.embalaje.getEmbalaje');
        Route::get('/showDetalles/{documento_id}', 'Despachos\EmbalajeController@showDetalles')->name('despachos.embalaje.showDetalles');
        Route::get('get-mdl-embalaje/{id}', 'Despachos\EmbalajeController@getMdlEmbalaje')->name('despachos.embalaje.getMdlEmbalaje');
        Route::post('/generar-paquete', 'Despachos\EmbalajeController@generarPaqueteEmbalaje')->name('despachos.embalaje.generarPaqueteEmbalaje');
        Route::get('get-despacho-by-id/{id}', 'Despachos\EmbalajeController@getDespachoById')->name('despachos.embalaje.getDespachoById');
        Route::get('getPdf', 'Despachos\EmbalajeController@getPdf')->name('despachos.embalaje.getPdf');
        Route::get('getExcel', 'Despachos\EmbalajeController@getExcel')->name('despachos.embalaje.getExcel');

        Route::get('create-guia-envio/{envio_id}', 'Despachos\EmbalajeController@createGuiaEnvio')->name('despachos.embalaje.createGuiaEnvio');
        Route::post('store-guia-envio/', 'Despachos\EmbalajeController@storeGuiaEnvio')->name('despachos.embalaje.storeGuiaEnvio');

        Route::post('/setFallado', 'Despachos\EmbalajeController@setFallado')->name('despachos.embalaje.setFallado');
        Route::post('/setReserva', 'Despachos\EmbalajeController@setReserva')->name('despachos.embalaje.setReserva');
        Route::post('/setRevision', 'Despachos\EmbalajeController@setRevision')->name('despachos.embalaje.setRevision');

        Route::get('/pdfBultos/{documento_id}/{despacho_id}/{nro_bultos}', 'Ventas\DespachoController@pdfBultos')->name('ventas.despachos.pdfBultos');

        Route::post('/setDespacho', 'Ventas\DespachoController@setDespacho')->name('ventas.despachos.setDespacho');
        Route::get('get-despacho/{documento_id}', 'Ventas\DespachoController@getDespacho')->name('ventas.despachos.getDespacho');
        Route::post('/updateDespacho', 'Ventas\DespachoController@updateDespacho')->name('ventas.despachos.updateDespacho');
        Route::post('/eliminarDespacho', 'Ventas\DespachoController@eliminarDespacho')->name('ventas.despachos.eliminarDespacho');
        Route::post('actualizarDespacho', 'Ventas\DespachoController@actualizarDespacho')->name('ventas.despachos.actualizarDespacho');
        Route::post('/store', 'Ventas\DespachoController@store')->name('ventas.despachos.store');
    });

    Route::prefix('reparto')->group(function () {
        Route::get('/', 'Despachos\RepartoController@index')->name('despachos.reparto.index');
        Route::get('/getRepartos', 'Despachos\RepartoController@getRepartos')->name('despachos.reparto.getRepartos');
        Route::get('/show', 'Despachos\RepartoController@show')->name('despachos.reparto.show');
        Route::get('/create', 'Despachos\RepartoController@create')->name('despachos.reparto.create');
        Route::get('/getPaquetesEmbaladosPendientes', 'Despachos\RepartoController@getPaquetesEmbaladosPendientes')->name('despachos.reparto.getPaquetesEmbaladosPendientes');
        Route::post('/store', 'Despachos\RepartoController@store')->name('despachos.reparto.store');
        Route::get('/getMdlRShow/{id}', 'Despachos\RepartoController@getMdlRShow')->name('despachos.reparto.getMdlRShow');
        Route::get('/getEnvios/{paquete_id}', 'Despachos\RepartoController@getEnviosPorPaquete')->name('despachos.reparto.getEnviosPorPaquete');
    });

    Route::prefix('reparto_detalle')->group(function () {
        Route::get('/', 'Despachos\RepartoDetalleController@index')->name('despachos.reparto_detalle.index');
        Route::get('/getRepartoDetalle', 'Despachos\RepartoDetalleController@getRepartoDetalle')->name('despachos.reparto_detalle.getRepartoDetalle');
        // Route::get('/show', 'Despachos\RepartoController@show')->name('despachos.reparto.show');
        Route::get('/pdfBultos/{id}/{nro_bultos}', 'Despachos\RepartoDetalleController@pdfBultos')->name('despachos.reparto_detalle.pdfBultos');
        // Route::get('/create', 'Despachos\RepartoController@create')->name('despachos.reparto.create');
        // Route::get('/getPaquetesEmbaladosPendientes', 'Despachos\RepartoController@getPaquetesEmbaladosPendientes')->name('despachos.reparto.getPaquetesEmbaladosPendientes');
        // Route::post('/store', 'Despachos\RepartoController@store')->name('despachos.reparto.store');
    });

    Route::prefix('guiasremision')->group(function () {

        Route::get('index', 'Ventas\GuiaController@index')->name('ventas.guiasremision.index');
        Route::get('getGuia', 'Ventas\GuiaController@getGuias')->name('ventas.getGuia');
        //Route::get('create/{id}', 'Ventas\GuiaController@create')->name('ventas.guiasremision.create');
        Route::get('create', 'Ventas\GuiaController@create')->name('ventas.guiasremision.create');
        Route::post('store', 'Ventas\GuiaController@store')->name('ventas.guiasremision.store');
        Route::put('update/{id}', 'Ventas\GuiaController@update')->name('ventas.guiasremision.update');
        Route::post('destroy', 'Ventas\GuiaController@destroy')->name('ventas.guiasremision.delete');
        Route::get('show/{id}', 'Ventas\GuiaController@show')->name('ventas.guiasremision.show');
        Route::get('reporte/{id}', 'Ventas\GuiaController@report')->name('ventas.guiasremision.reporte');
        Route::get('tiendaDireccion/{id}', 'Ventas\GuiaController@tiendaDireccion')->name('ventas.guiasremision.tienda_direccion');
        Route::post('sunat/guia', 'Ventas\GuiaController@sunat')->name('ventas.guiasremision.sunat');
        Route::post('consulta_ticket/guia/', 'Ventas\GuiaController@consulta_ticket')->name('ventas.guiasremision.consultar');
        Route::get('getXml/{guia_id}', 'Ventas\GuiaController@getXml')->name('ventas.guiasremision.getXml');
        Route::get('getCdr/{guia_id}', 'Ventas\GuiaController@getCdr')->name('ventas.guiasremision.getCdr');
        Route::get('/getProductos/', 'Ventas\GuiaController@getProductos')->name('ventas.guiasremision.getProductos');
        Route::get('/getColoresTallas/{almacen_id}/{producto_id}', 'Ventas\GuiaController@getColoresTallas')->name('ventas.guiasremision.getColoresTallas');
    });

});

Route::get('/pdf_show/{id}', [GuiaController::class, 'pdf_show'])
->name('ventas.guiasremision.pdf_show')
->middleware('signed')
->withoutMiddleware('auth');
