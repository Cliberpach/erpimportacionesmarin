<?php

use Illuminate\Support\Facades\Route;

Route::prefix('reportes')->group(function () {

    // Reportes - Producto - informe
    Route::prefix('producto')->group(function () {

        Route::get('informe', 'Reportes\ProductoController@informe')->name('reporte.producto.informe');
        Route::get('llenarCompras/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarCompras')->name('reporte.producto.llenarCompras');
        Route::get('llenarVentas/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarVentas')->name('reporte.producto.llenarVentas');
        Route::get('llenarNotasCredito/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarNotasCredito')->name('reporte.producto.llenarNotasCredito');
        Route::get('llenarSalidas/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarSalidas')->name('reporte.producto.llenarSalidas');
        Route::get('llenarIngresos/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarIngresos')->name('reporte.producto.llenarIngresos');
        Route::get('llenarTrasladoSalida/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarTrasladoSalida')->name('reporte.producto.llenarTrasladoSalida');
        Route::get('llenarTrasladoIngreso/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@llenarTrasladoIngreso')->name('reporte.producto.llenarTrasladoIngreso');

        Route::post('updateIngreso', 'Reportes\ProductoController@updateIngreso')->name('reporte.producto.updateIngreso');
        // Route::get('getTable', 'Reportes\ProductoController@getTable')->name('reporte.producto.getTable');
        Route::get('getProductos', 'Reportes\ProductoController@getProductos')->name('reporte.producto.getProductos');
        Route::get('excelProductos', 'Reportes\ProductoController@excelProductos')->name('reporte.producto.excelProductos');
        Route::post('obtenerBarCode', 'Reportes\ProductoController@obtenerBarCode')->name('reporte.producto.obtenerBarCode');
        Route::get('getAdhesivos/{producto_id}/{color_id}/{talla_id}', 'Reportes\ProductoController@getAdhesivos')->name('reporte.producto.getAdhesivos');
    });

    // Reportes - Producto - stock valorizado
    Route::prefix('producto/stock-valorizado')->group(function () {

        Route::get('index', 'Reportes\StockValorizadoController@index')->name('reporte.producto.stockvalorizado.index');
        Route::get('getTable', 'Reportes\StockValorizadoController@getTable')->name('reporte.producto.stockvalorizado.getTable');
    });

    // Reportes - Pos - CajaDiaria
    Route::prefix('pos')->group(function () {

        Route::get('cajadiaria', 'Reportes\Pos\CajaController@index')->name('reporte.pos.cajadiaria');
        Route::post('cajadiaria/getTable', 'Reportes\Pos\CajaController@getTable')->name('reporte.pos.cajadiaria.getTable');
        Route::get('cajadiaria/getExcel', 'Reportes\Pos\CajaController@getExcel')->name('reporte.pos.cajadiaria.getExcel');

        Route::get('egreso', 'Reportes\Pos\EgresoController@index')->name('reporte.pos.egreso');
        Route::post('egreso/getTable', 'Reportes\Pos\EgresoController@getTable')->name('reporte.pos.egreso.getTable');
        Route::get('egreso/getExcel', 'Reportes\Pos\EgresoController@getExcel')->name('reporte.pos.egreso.getExcel');
    });

    // Reportes - Ventas - Documento
    Route::prefix('ventas')->group(function () {

        Route::get('documento', 'Reportes\Ventas\DocumentoController@index')->name('reporte.ventas.documento');
        Route::post('documento/getTable', 'Reportes\Ventas\DocumentoController@getTable')->name('reporte.ventas.documento.getTable');
        Route::get('documento/getExcel', 'Reportes\Ventas\DocumentoController@getExcel')->name('reporte.ventas.documento.getExcel');
    });

    // Reportes - Compras - Documento
    Route::prefix('compras')->group(function () {

        Route::get('documento', 'Reportes\Compras\DocumentoController@index')->name('reporte.compras.documento');
        Route::post('documento/getTable', 'Reportes\Compras\DocumentoController@getTable')->name('reporte.compras.documento.getTable');
        Route::get('documento/getExcel', 'Reportes\Compras\DocumentoController@getExcel')->name('reporte.compras.documento.getExcel');
    });

    // Reportes - Cuentas - Proveedor
    Route::prefix('cuentas')->group(function () {

        Route::get('proveedor', 'Reportes\Cuentas\ProveedorController@index')->name('reporte.cuentas.proveedor');
        Route::post('proveedor/getTable', 'Reportes\Cuentas\ProveedorController@getTable')->name('reporte.cuentas.proveedor.getTable');
        Route::get('proveedor/getExcel', 'Reportes\Cuentas\ProveedorController@getExcel')->name('reporte.cuentas.proveedor.getExcel');
    });

    // Reportes - Cuentas - Cliente
    Route::prefix('cuentas')->group(function () {

        Route::get('cliente', 'Reportes\Cuentas\ClienteController@index')->name('reporte.cuentas.cliente');
        Route::post('cliente/getTable', 'Reportes\Cuentas\ClienteController@getTable')->name('reporte.cuentas.cliente.getTable');
        Route::get('cliente/getExcel', 'Reportes\Cuentas\ClienteController@getExcel')->name('reporte.cuentas.cliente.getExcel');
    });

    // Reportes - Notas - Ingreso
    Route::prefix('notas')->group(function () {

        Route::get('ingreso', 'Reportes\Notas\IngresoController@index')->name('reporte.notas.ingreso');
        Route::post('ingreso/getTable', 'Reportes\Notas\IngresoController@getTable')->name('reporte.notas.ingreso.getTable');
        Route::get('ingreso/getExcel', 'Reportes\Notas\IngresoController@getExcel')->name('reporte.notas.ingreso.getExcel');
    });

    // Reportes - Notas - Salida
    Route::prefix('notas')->group(function () {

        Route::get('salida', 'Reportes\Notas\SalidaController@index')->name('reporte.notas.salida');
        Route::post('salida/getTable', 'Reportes\Notas\SalidaController@getTable')->name('reporte.notas.salida.getTable');
        Route::get('salida/getExcel', 'Reportes\Notas\SalidaController@getExcel')->name('reporte.notas.salida.getExcel');
    });
});
