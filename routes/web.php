<?php

use App\Events\NotifySunatEvent;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth()->user()) {
        return view('home');
    } else {
        return view('auth.login');
    }
});

Auth::routes();

Route::group(
    [
        'middleware' => 'auth',
        // 'middleware' => 'Cors'
    ],
    function () {
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/home/dashboard', 'HomeController@dashboard')->name('home.dashboard');

        Route::get('logout', 'Auth\LoginController@logout')->name('logout');

        //Parametro
        Route::get('parametro/getApiruc/{ruc}', 'ParametroController@apiRuc')->name('getApiruc');
        Route::get('parametro/getApidni/{dni}', 'ParametroController@apiDni')->name('getApidni');
        Route::get('parametro/notifications', 'ParametroController@notifications')->name('getNotifications');

        //======== RESTAURAR STOCK ======
        Route::post('restaurar-stock/', 'ParametroController@restaurarStock')->name('restaurarStock');
        Route::get('descargar-bd/', 'ParametroController@descargarBD')->name('descargarBD');

        // Mantenimiento




        //Configuracion
        Route::prefix('configuracion')->group(function () {
            Route::get('index', 'Configuracion\ConfiguracionController@index')->name('configuracion.index');
            Route::put('update/{id}', 'Configuracion\ConfiguracionController@update')->name('configuracion.update');
            Route::put('/empresa/update', 'Configuracion\ConfiguracionController@codigo')->name('configuracion.empresa.update');
            Route::post('/changePassword', 'Configuracion\ConfiguracionController@changePasswordMaster')->name('changePasswordMaster');
            Route::post('/resumenes/envio', 'Configuracion\ConfiguracionController@resumenesEnvio')->name('configuracion.resumenes.envio');
            Route::post('/greenter/modo', 'Configuracion\ConfiguracionController@setGreenterModo')->name('configuracion.greenter.modo');
            Route::post('/cuentasBancarias/modo', 'Configuracion\ConfiguracionController@cuentasBancariasModo')->name('configuracion.cuentasBancarias.modo');
        });

        //Users
        Route::prefix('users')->group(function () {
            Route::get('/', 'Seguridad\UserController@index')->name('user.index');
            Route::get('destroy/{id}', 'Seguridad\UserController@destroy')->name('user.destroy');
            Route::get('create', 'Seguridad\UserController@create')->name('user.create');
            Route::post('store', 'Seguridad\UserController@store')->name('user.store');
            Route::get('edit/{id}', 'Seguridad\UserController@edit')->name('user.edit');
            Route::put('update/{id}', 'Seguridad\UserController@update')->name('user.update');
            Route::get('show/{id}', 'Seguridad\UserController@show')->name('user.show');
        });

        //Roles
        Route::prefix('roles')->group(function () {
            Route::get('/', 'Seguridad\RoleController@index')->name('role.index');
            Route::get('destroy/{id}', 'Seguridad\RoleController@destroy')->name('role.destroy');
            Route::get('create', 'Seguridad\RoleController@create')->name('role.create');
            Route::post('store', 'Seguridad\RoleController@store')->name('role.store');
            Route::get('edit/{id}', 'Seguridad\RoleController@edit')->name('role.edit');
            Route::put('update/{id}', 'Seguridad\RoleController@update')->name('role.update');
            Route::get('show/{id}', 'Seguridad\RoleController@show')->name('role.show');
            Route::get('getTable', 'Seguridad\RoleController@getTable')->name('role.getTable');
        });




        //Compras
        //Proveedores
        Route::prefix('compras/proveedores')->group(function () {
            Route::get('index', 'Compras\ProveedorController@index')->name('compras.proveedor.index');
            Route::get('getProvider', 'Compras\ProveedorController@getProvider')->name('getProvider');
            Route::get('create', 'Compras\ProveedorController@create')->name('compras.proveedor.create');
            Route::post('store', 'Compras\ProveedorController@store')->name('compras.proveedor.store');
            Route::get('edit/{id}', 'Compras\ProveedorController@edit')->name('compras.proveedor.edit');
            Route::get('show/{id}', 'Compras\ProveedorController@show')->name('compras.proveedor.show');
            Route::put('update/{id}', 'Compras\ProveedorController@update')->name('compras.proveedor.update');
            Route::get('destroy/{id}', 'Compras\ProveedorController@destroy')->name('compras.proveedor.destroy');
        });
        //Ordenes de Compra
        Route::prefix('compras/ordenes')->group(function () {
            Route::get('index', 'Compras\OrdenController@index')->name('compras.orden.index');
            Route::get('getOrder', 'Compras\OrdenController@getOrder')->name('getOrder');
            Route::get('create', 'Compras\OrdenController@create')->name('compras.orden.create');
            Route::post('store', 'Compras\OrdenController@store')->name('compras.orden.store');
            Route::get('edit/{id}', 'Compras\OrdenController@edit')->name('compras.orden.edit');
            Route::get('show/{id}', 'Compras\OrdenController@show')->name('compras.orden.show');
            Route::put('update/{id}', 'Compras\OrdenController@update')->name('compras.orden.update');
            Route::get('destroy/{id}', 'Compras\OrdenController@destroy')->name('compras.orden.destroy');
            Route::get('reporte/{id}', 'Compras\OrdenController@report')->name('compras.orden.reporte');
            Route::get('email/{id}', 'Compras\OrdenController@email')->name('compras.orden.email');
            Route::get('concretada/{id}', 'Compras\OrdenController@concretized')->name('compras.orden.concretada');
            Route::get('consultaEnvios/{id}', 'Compras\OrdenController@send')->name('compras.orden.envios');
            Route::get('documento/{id}', 'Compras\OrdenController@document')->name('compras.orden.documento');
            Route::get('nuevodocumento/{id}', 'Compras\OrdenController@newdocument')->name('compras.orden.nuevodocumento');
            Route::get('confirmarEliminar/{id}', 'Compras\OrdenController@confirmDestroy')->name('compras.orden.confirmDestroy');
            Route::get('dolar', 'Compras\OrdenController@dolar')->name('compras.orden.dolar');

            Route::get('getProductosByModelo/{modelo_id}', 'Compras\OrdenController@getProductosByModelo')->name('compras.orden.getProductosByModelo');
        });
        //Documentos
        Route::prefix('compras/documentos')->group(function () {
            Route::get('/index', 'Compras\DocumentoController@index')->name('compras.documento.index');
            Route::get('/getDocument', 'Compras\DocumentoController@getDocument')->name('getDocument');
            Route::get('/create', 'Compras\DocumentoController@create')->name('compras.documento.create');
            Route::post('/store', 'Compras\DocumentoController@store')->name('compras.documento.store');
            Route::post('/consulta-store', 'Compras\DocumentoController@comprobante_store')->name('compras.documento.consulta_store');
            Route::post('/consulta-update', 'Compras\DocumentoController@comprobante_update')->name('compras.documento.consulta_update');
            Route::get('/edit/{id}', 'Compras\DocumentoController@edit')->name('compras.documento.edit');
            Route::put('/update/{id}', 'Compras\DocumentoController@update')->name('compras.documento.update');
            Route::get('/destroy/{id}', 'Compras\DocumentoController@destroy')->name('compras.documento.destroy');
            Route::get('/show/{id}', 'Compras\DocumentoController@show')->name('compras.documento.show');
            Route::get('/getProduct', 'Compras\DocumentoController@getProduct')->name('compras.documento.getProduct');
            Route::get('/reporte/{id}', 'Compras\DocumentoController@report')->name('compras.documento.reporte');

            Route::get('/tipoPago/{id}', 'Compras\DocumentoController@TypePay')->name('compras.documento.tipo_pago.existente');
        });

        //NOTAS DE CREDITO COMPRAS
        Route::prefix('compras/notas')->group(function () {
            Route::get('index/{id}', 'Compras\NotaController@index')->name('compras.notas');
            Route::get('create', 'Compras\NotaController@create')->name('compras.notas.create');
            Route::post('store', 'Compras\NotaController@store')->name('compras.notas.store');
            Route::get('getNotes/{id}', 'Compras\NotaController@getNotes')->name('compras.getNotes');
            Route::get('getDetalles/{id}', 'Compras\NotaController@getDetalles')->name('compras.getDetalles');
            Route::get('show/{id}', 'Compras\NotaController@show')->name('compras.notas.show');
            Route::get('show_dev/{id}', 'Compras\NotaController@show_dev')->name('compras.notas_dev.show');
        });

        //======= VENTAS ========
        require __DIR__ . '/ventas/web.php';

        //PEDIDOS-PEDIDOS
        require __DIR__ . '/pedidos/web.php';

        //Despachos
        require __DIR__ . '/despachos/web.php';

        //Almacénes
        require __DIR__ . '/almacenes/web.php';

        //Reservas
        Route::prefix('ventas/reservas')->group(function () {

            Route::get('/', 'Ventas\ReservaController@index')->name('ventas.reservas.index');
            Route::get('/getTable', 'Ventas\ReservaController@getTable')->name('ventas.reservas.getTable');
            Route::get('/showDetalles/{documento_id}', 'Ventas\ReservaController@showDetalles')->name('ventas.reservas.showDetalles');
            // Route::get('/pdfBultos/{documento_id}/{despacho_id}/{nro_bultos}', 'Ventas\DespachoController@pdfBultos')->name('ventas.despachos.pdfBultos');
            // Route::post('/setEmbalaje', 'Ventas\DespachoController@setEmbalaje')->name('ventas.despachos.setEmbalaje');
            // Route::post('/setDespacho', 'Ventas\DespachoController@setDespacho')->name('ventas.despachos.setDespacho');
            // Route::get('get-despacho/{documento_id}','Ventas\DespachoController@getDespacho')->name('ventas.despachos.getDespacho');
            // Route::post('/updateDespacho', 'Ventas\DespachoController@updateDespacho')->name('ventas.despachos.updateDespacho');
            // Route::post('/eliminarDespacho', 'Ventas\DespachoController@eliminarDespacho')->name('ventas.despachos.eliminarDespacho');

        });

        //COMPROBANTES ELECTRONICOS
        Route::prefix('comprobantes/electronicos')->group(function () {
            Route::get('/', 'Ventas\Electronico\ComprobanteController@index')->name('ventas.comprobantes');
            Route::get('getVouchers', 'Ventas\Electronico\ComprobanteController@getVouchers')->name('ventas.getVouchers');
            Route::get('sunat/{id}', 'Ventas\Electronico\ComprobanteController@sunat')->name('ventas.documento.sunat');
            Route::get('sunat-contingencia/{id}', 'Ventas\Electronico\ComprobanteController@sunatContingencia')->name('ventas.documento.sunat.contingencia');
            Route::get('contingencia/{id}', 'Ventas\Electronico\ComprobanteController@convertirContingencia')->name('ventas.documento.contingencia');
            Route::get('cdr/{id}', 'Ventas\Electronico\ComprobanteController@cdr')->name('ventas.documento.cdr');
            Route::post('/envio', 'Ventas\Electronico\ComprobanteController@email')->name('ventas.documento.envio');
        });

        //NOTAS DE CREDITO / DEBITO
        Route::prefix('notas/electronicos')->group(function () {
            Route::get('index/{id}', 'Ventas\Electronico\NotaController@index')->name('ventas.notas');
            Route::get('index_dev/{id}', 'Ventas\Electronico\NotaController@index_dev')->name('ventas.notas_dev');
            Route::get('create', 'Ventas\Electronico\NotaController@create')->name('ventas.notas.create');
            Route::post('store', 'Ventas\Electronico\NotaController@store')->name('ventas.notas.store');
            Route::get('getNotes/{id}', 'Ventas\Electronico\NotaController@getNotes')->name('ventas.getNotes');
            Route::get('getDetalles/{id}', 'Ventas\Electronico\NotaController@getDetalles')->name('ventas.getDetalles');
            Route::get('show/{id}', 'Ventas\Electronico\NotaController@show')->name('ventas.notas.show');
            Route::get('show_dev/{id}', 'Ventas\Electronico\NotaController@show_dev')->name('ventas.notas_dev.show');
            Route::get('sunat/{id}/{type_response?}', 'Ventas\Electronico\NotaController@sunat')->name('ventas.notas.sunat');
        });


        Route::prefix('modeloExcel')->group(function () {
            Route::get('cliente', 'ModeloExcelController@cliente')->name('ModeloExcel.cliente');
            Route::get('categoria', 'ModeloExcelController@categoria')->name('ModeloExcel.categoria');
            Route::get('modelo', 'ModeloExcelController@modelo')->name('ModeloExcel.modelo');
            Route::get('marca', 'ModeloExcelController@marca')->name('ModeloExcel.marca');
            Route::get('producto', 'ModeloExcelController@producto')->name('ModeloExcel.producto');
            Route::get('proveedor', 'ModeloExcelController@proveedor')->name('ModeloExcel.proveedor');
        });

        Route::prefix('importExcel')->group(function () {
            Route::post('cliente', 'ImportExcelController@uploadcliente')->name('ImportExcel.uploadcliente');
            Route::post('categoria', 'ImportExcelController@uploadcategoria')->name('ImportExcel.uploadcategoria');
            Route::post('modelo', 'ImportExcelController@uploadmodelo')->name('ImportExcel.uploadmodelo');
            Route::post('marca', 'ImportExcelController@uploadmarca')->name('ImportExcel.uploadmarca');
            Route::post('producto', 'ImportExcelController@uploadproducto')->name('ImportExcel.uploadproducto');
            Route::post('proveedor', 'ImportExcelController@uploadproveedor')->name('ImportExcel.uploadproveedor');
        });



        Route::prefix("consultas/ajax")->group(function () {
            Route::get("tipo-documentos", "ConsultasAjaxController@getTipoDocumentos")->name("consulta.ajax.getTipoDocumentos");
            Route::get("tipo-clientes", "ConsultasAjaxController@tipoClientes")->name("consulta.ajax.tipoClientes");
            Route::get("departamentos", "ConsultasAjaxController@getDepartamentos")->name("consulta.ajax.getDepartamentos");
            Route::get("codigo-precio-menor", "ConsultasAjaxController@getCodigoPrecioMenor")->name("consulta.ajax.getCodigoPrecioMenor");
            Route::get("tipo-envios", "ConsultasAjaxController@getTipoEnvios")->name("consulta.ajax.getTipoEnvios");
            Route::get("get-empresas-envio/{tipo_envio}", "ConsultasAjaxController@getEmpresasEnvio")->name("consulta.ajax.getEmpresasEnvio");
            Route::get("get-sedes-envio/{empresa_envio_id}/{ubigeo}", "ConsultasAjaxController@getSedesEnvio")->name("consulta.ajax.getSedesEnvio");
            Route::get("get-origenes-ventas", "ConsultasAjaxController@getOrigenesVentas")->name("consulta.ajax.getOrigenesVentas");
            Route::get("get-tipos-pago-envio", "ConsultasAjaxController@getTiposPagoEnvio")->name("consulta.ajax.getTiposPagoEnvio");
        });
    }
);

require __DIR__ . '/mantenimiento/web.php';
require __DIR__ . '/cajas/web.php';
require __DIR__ . '/cuentas/web.php';
require __DIR__ . '/kardex/web.php';
require __DIR__ . '/reportes/web.php';
require __DIR__ . '/consultas/web.php';

Route::get('ventas/documentos/comprobante/{id}/{size}', 'Ventas\DocumentoController@voucher')->name('ventas.documento.comprobante');
Route::get('ventas/documentos/xml/{id}', 'Ventas\DocumentoController@xml')->name('ventas.documento.xml');
Route::get('/buscar', 'BuscarController@index');
Route::post('/getDocument', 'BuscarController@getDocumento')->name('buscar.getDocument');

Route::get('ruta', function () {
    $dato = "Message";
    broadcast(new NotifySunatEvent($dato));
    return 'ok';
    actualizarStockProductos();
    return "okkk";
    $comprobante = array(
        'ruc' => '11111111111',
        'tipo' => '',
        'serie' => '',
        'correlativo' => '',
        'fecha_emision' => '',
        'total' => ''
    );
    $comprobante = json_encode($comprobante, false);
    $comprobante = json_decode($comprobante, false);
    $data = consultaCrd($comprobante);
    $data = json_decode($data);
    return $data->message;
    return '<div style="width:100%; height: 100vh;text-align:center;"><h1 style="font-size: 350px;">SISCOM</h1></div';
});

Route::post('/liberar_colaborador', 'Pos\CajaController@retirarColaborades')->name('Caja.liberarColaborades');
Route::get('/get-colaborades/{id}', 'Pos\CajaController@getColaborades')->name('Caja.getColaborades');
Route::get('/get-producto-by-modelo/{modelo_id}', 'Almacenes\ProductoController@getProductosByModelo'); //VENTAS-NOTA SALIDA
Route::get('/get-stocklogico/{almacen_id}/{producto_id}/{color_id}/{talla_id}', 'Almacenes\ProductoController@getStockLogico');

Route::prefix('utilidades')->group(function () {
    Route::get('getProductos', 'UtilidadesController@getProductos')->name('utilidades.getProductos');
    Route::get('getColores', 'UtilidadesController@getColores')->name('utilidades.getColores');

    Route::get('consultarDocumento', 'UtilidadesController@consultarDocumento')->name('utilidades.consultarDocumento');
    Route::get('getClientes', 'Ventas\ClienteController@getClientes')->name('utilidades.getClientes');
    Route::get('getProductosTodos', 'Almacenes\ProductoController@getProductosTodos')->name('utilidades.getProductosTodos');
    Route::get('getProductosConStock', 'Almacenes\ProductoController@getProductosConStock')->name('utilidades.getProductosConStock');
    Route::get('getColoresTalla/{almacen_id}/{producto_id}', 'Almacenes\ProductoController@getColoresTalla')->name('utilidades.getColoresTalla');
    Route::get('validarCantidad', 'UtilidadesController@validarCantidad')->name('utilidades.validarCantidad');
    Route::get('getCajaMovimiento', 'UtilidadesController@getCajaMovimiento')->name('utilidades.getCajaMovimiento');
    Route::get('get-cuentas-metodo/{metodo_pago}', 'UtilidadesController@getCuentasPorMetodoPago')->name('utilidades.getCuentasPorMetodoPago');
});
