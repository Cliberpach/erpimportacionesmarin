<?php

use Illuminate\Support\Facades\Route;

Route::prefix('consultas')->group(function () {


    // Consultas - Documentos
    Route::prefix('documentos')->group(function () {
        Route::get('index', 'Consultas\DocumentoController@index')->name('consultas.documento.index');
        Route::get('getTable', 'Consultas\DocumentoController@getTable')->name('consultas.documento.getTable');
        Route::get('getDownload', 'Consultas\DocumentoController@getDownload')->name('consultas.documento.getDownload');
        Route::get('convertir/{id}', 'Consultas\DocumentoController@convertir')->name('consultas.documento.convertir');
        Route::get('getExcel', 'Consultas\DocumentoController@getExcel')->name('consultas.documento.getExcel');
    });

    // Consultas - Ventas - Documentos
    Route::prefix('ventas/documentos')->group(function () {

        Route::get('index', 'Consultas\Ventas\DocumentoController@index')->name('consultas.ventas.documento.index');
        Route::post('getTable', 'Consultas\Ventas\DocumentoController@getTable')->name('consultas.ventas.documento.getTable');
    });

    // Consultas - Ventas - Documentos - NO
    Route::prefix('ventas/documentos-no')->group(function () {

        Route::get('index', 'Consultas\Ventas\NoEnviadosController@index')->name('consultas.ventas.documento.no.index');
        Route::post('getTable', 'Consultas\Ventas\NoEnviadosController@getTable')->name('consultas.ventas.documento.no.getTable');
        Route::get('edit/{id}', 'Consultas\Ventas\NoEnviadosController@edit')->name('consultas.ventas.documento.no.edit');
        Route::post('update/{id}', 'Consultas\Ventas\NoEnviadosController@update')->name('consultas.ventas.documento.no.update');

        Route::get('getLot/{id}', 'Consultas\Ventas\NoEnviadosController@getLot')->name('consultas.ventas.documento.no.getLot');
        Route::get('getLotRecientes/{id}', 'Consultas\Ventas\NoEnviadosController@getLotRecientes')->name('consultas.ventas.documento.no.getLotRecientes');
        Route::post('cantidad/', 'Consultas\Ventas\NoEnviadosController@quantity')->name('consultas.ventas.documento.no.cantidad');
        Route::post('devolver/cantidad', 'Consultas\Ventas\NoEnviadosController@returnQuantity')->name('consultas.ventas.documento.no.devolver.cantidades');
        // Route::post('devolver/cantidadedit', 'Consultas\Ventas\NoEnviadosController@returnQuantityEdit')->name('consultas.ventas.documento.no.devolver.cantidadesedit');
        // Route::post('updateQuantityEdit', 'Consultas\Ventas\NoEnviadosController@updateQuantityEdit')->name('consultas.ventas.documento.no.updateQuantityEdit');
        // Route::post('devolver/lotesinicio', 'Consultas\Ventas\NoEnviadosController@returnQuantityLoteInicio')->name('consultas.ventas.documento.no.devolver.lotesinicio');
        Route::post('obtener/lote', 'Consultas\Ventas\NoEnviadosController@returnLote')->name('consultas.ventas.documento.no.obtener.lote');
        Route::post('update/lote/edit', 'Consultas\Ventas\NoEnviadosController@updateLote')->name('consultas.ventas.documento.no.update.lote');
    });

    // Consultas - Ventas - Cotizaciones
    Route::prefix('ventas/cotizaciones')->group(function () {

        Route::get('index', 'Consultas\Ventas\CotizacionController@index')->name('consultas.ventas.cotizacion.index');
        Route::post('getTable', 'Consultas\Ventas\CotizacionController@getTable')->name('consultas.ventas.cotizacion.getTable');
    });

    // Consultas - Alertas
    Route::prefix('ventas/alertas')->group(function () {

        Route::get('envio', 'Consultas\Ventas\AlertaController@envio')->name('consultas.ventas.alerta.envio');
        Route::get('getTableEnvio', 'Consultas\Ventas\AlertaController@getTableEnvio')->name('consultas.ventas.alerta.getTableEnvio');
        Route::get('sunat/{id}', 'Consultas\Ventas\AlertaController@sunat')->name('consultas.ventas.alerta.sunat');
        Route::get('anular-venta/{id}', 'Consultas\Ventas\AlertaController@anularVenta')->name('consultas.ventas.alerta.anularVenta');

        Route::get('regularize', 'Consultas\Ventas\AlertaController@regularize')->name('consultas.ventas.alerta.regularize');
        Route::get('getTableRegularize', 'Consultas\Ventas\AlertaController@getTableRegularize')->name('consultas.ventas.alerta.getTableRegularize');
        Route::get('cdr/{id}', 'Consultas\Ventas\AlertaController@cdr')->name('consultas.ventas.alerta.cdr');

        Route::get('notas', 'Consultas\Ventas\AlertaController@notas')->name('consultas.ventas.alerta.notas');
        Route::get('getTableNotas', 'Consultas\Ventas\AlertaController@getTableNotas')->name('consultas.ventas.alerta.getTableNotas');
        Route::post('sunat_notas', 'Consultas\Ventas\AlertaController@sunat_notas')->name('consultas.ventas.alerta.sunat_notas');

        Route::get('guias', 'Consultas\Ventas\AlertaController@guias')->name('consultas.ventas.alerta.guias');
        Route::get('getTableguias', 'Consultas\Ventas\AlertaController@getTableGuias')->name('consultas.ventas.alerta.getTableGuias');
        Route::get('sunat_guias/{id}', 'Consultas\Ventas\AlertaController@sunat_guias')->name('consultas.ventas.alerta.sunat_guias');

        Route::get('retenciones', 'Consultas\Ventas\AlertaController@retenciones')->name('consultas.ventas.alerta.retenciones');
        Route::get('getTableretenciones', 'Consultas\Ventas\AlertaController@getTableRetenciones')->name('consultas.ventas.alerta.getTableRetenciones');
        Route::get('sunat_retenciones/{id}', 'Consultas\Ventas\AlertaController@sunat_retenciones')->name('consultas.ventas.alerta.sunat_retenciones');
    });

    // Consultas - Compras - Ordenes
    Route::prefix('compras/cotizaciones')->group(function () {

        Route::get('index', 'Consultas\Compras\OrdenController@index')->name('consultas.compras.orden.index');
        Route::post('getTable', 'Consultas\Compras\OrdenController@getTable')->name('consultas.compras.orden.getTable');
    });

    // Consultas - Compras - Documentos
    Route::prefix('compras/documentos')->group(function () {

        Route::get('index', 'Consultas\Compras\DocumentoController@index')->name('consultas.compras.documento.index');
        Route::post('getTable', 'Consultas\Compras\DocumentoController@getTable')->name('consultas.compras.documento.getTable');
    });

    // Consultas - Cuentas - Proveedores
    Route::prefix('cuentas/proveedores')->group(function () {

        Route::get('index', 'Consultas\Cuentas\ProveedorController@index')->name('consultas.cuentas.proveedor.index');
        Route::post('getTable', 'Consultas\Cuentas\ProveedorController@getTable')->name('consultas.cuentas.proveedor.getTable');
    });

    // Consultas - Cuentas - Clientes
    Route::prefix('cuentas/clientes')->group(function () {

        Route::get('index', 'Consultas\Cuentas\ClienteController@index')->name('consultas.cuentas.cliente.index');
        Route::post('getTable', 'Consultas\Cuentas\ClienteController@getTable')->name('consultas.cuentas.cliente.getTable');
    });

    // Consultas - Notas - Salida
    Route::prefix('notas/salidad')->group(function () {

        Route::get('index', 'Consultas\Notas\SalidadController@index')->name('consultas.notas.salidad.index');
        Route::post('getTable', 'Consultas\Notas\SalidadController@getTable')->name('consultas.notas.salidad.getTable');
    });

    // Consultas - Notas - Ingreso
    Route::prefix('notas/ingreso')->group(function () {

        Route::get('index', 'Consultas\Notas\IngresoController@index')->name('consultas.notas.ingreso.index');
        Route::post('getTable', 'Consultas\Notas\IngresoController@getTable')->name('consultas.notas.ingreso.getTable');
    });


    // Consultas - Kardex - Salida -Ventas
    Route::prefix('kardex/salidas')->group(function () {

        Route::get('index-V', 'Consultas\Kardex\SalidaController@ventas')->name('consultas.kardex.ventas.index');
        Route::post('getTableVentas', 'Consultas\Kardex\SalidaController@getTableVentas')->name('consultas.kardex.ventas.getTable');

        Route::get('index-N', 'Consultas\Kardex\SalidaController@notas')->name('consultas.kardex.notas.index');
        Route::post('getTableNotas', 'Consultas\Kardex\SalidaController@getTableNotas')->name('consultas.kardex.notas.getTable');
    });

    // Consultas - Caja - Utilidad
    Route::prefix('caja/utilidad')->group(function () {

        Route::get('index', 'Consultas\Caja\UtilidadController@index')->name('consultas.caja.utilidad.index');
        Route::post('getTable', 'Consultas\Caja\UtilidadController@getTable')->name('consultas.caja.utilidad.getTable');
    });

    // Cosultas - Caja - Egreso
    Route::prefix('pos/egreso')->group(function () {

        Route::get('index', 'Consultas\Pos\EgresoController@index')->name('consultas.pos.egreso.index');
        Route::post('getTable', 'Consultas\Pos\EgresoController@getTable')->name('consultas.pos.egreso.getTable');
    });

    // Consultas - Utilidad
    Route::prefix('utilidad')->group(function () {

        Route::get('index', 'Consultas\UtilidadController@index')->name('consultas.utilidad.index');
        Route::get('getDatos/{mes}/{anio}', 'Consultas\UtilidadController@getDatos')->name('consultas.utilidad.getDatos');
    });


    // Consultas - CONTABILIDAD
    Route::prefix('contabilidad')->group(function () {

        Route::get('index', 'Consultas\ContabilidadController@index')->name('consultas.contabilidad.index');
        Route::post('getTable', 'Consultas\ContabilidadController@getTable')->name('consultas.contabilidad.getTable');
        Route::get('getDownload', 'Consultas\ContabilidadController@getDownload')->name('consultas.contabilidad.getDownload');

        // Route::get('getDownload','Consultas\DocumentoController@getDownload')->name('consultas.documento.getDownload');
        // Route::get('convertir/{id}','Consultas\DocumentoController@convertir')->name('consultas.documento.convertir');

    });
});
