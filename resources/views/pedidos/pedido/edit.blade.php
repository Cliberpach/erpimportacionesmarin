@extends('layout')
@section('content')

    @include('ventas.cotizaciones.modal-cliente')
    @include('ventas.documentos.editar.modals.mdl_envio')

@section('pedidos-active', 'active')
@section('pedido-active', 'active')


<div class="row wrapper border-bottom white-bg page-heading">

    <div class="col-lg-7 col-md-7">
        <h2 style="text-transform:uppercase"><b>Modificar Reserva #{{ $pedido->id }}</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Reservas</strong>
            </li>
        </ol>
    </div>

    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 mb-3 mt-3 d-flex align-items-center justify-content-center">
        <div class="alert alert-warning alert-dismissible fade show m-0" role="alert">
            <strong>Holy {{ Auth::user()->usuario }}!</strong> Las reservas ATENDIDAS no pueden cambiarse de almacén!!!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

{{-- 🚨 ALERTA DE INFORMACIÓN --}}
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded" role="alert">
            <h5 class="mb-3">
                <i class="fas fa-edit mr-2"></i>
                <strong>IMPORTANTE - EDITAR RESERVAS</strong>
            </h5>
            <ul class="mb-0 pl-3">
                <li>
                    <i class="fas fa-shipping-fast text-success mr-1"></i>
                    Los <strong>datos de envío</strong> solo pueden ser modificados
                    mientras el envío tenga estado
                    <span class="badge badge-secondary">PENDIENTE</span>.
                </li>
                <li>
                    <i class="fas fa-plus-circle text-primary mr-1"></i>
                    Al <strong>agregar productos</strong>, si hay stock se descontará,
                    de lo contrario quedarán en estado
                    <span class="badge badge-warning">EN ESPERA</span>.
                </li>
                <li>
                    <i class="fas fa-minus-circle text-danger mr-1"></i>
                    Al <strong>quitar productos</strong>:
                    <ul class="pl-3 mt-1">
                        <li>
                            <i class="fas fa-undo text-info mr-1"></i>
                            Si estaban <span class="badge badge-info">SEPARADOS</span>,
                            el stock se devuelve.
                        </li>
                        <li>
                            <i class="fas fa-clock text-warning mr-1"></i>
                            Si estaban <span class="badge badge-warning">EN ESPERA</span>,
                            se eliminan sin problema.
                        </li>
                        <li>
                            <i class="fas fa-industry text-danger mr-1"></i>
                            Si están en <span class="badge badge-danger">PRODUCCIÓN</span>,
                            <strong>no podrán eliminarse</strong>.
                        </li>
                    </ul>
                </li>
                <li>
                    <i class="fas fa-cash-register text-success mr-1"></i>
                    Si agregas productos y ya hubo pagos registrados,
                    el <strong>total del ticket</strong> y la <strong>cuenta del cliente</strong>
                    se actualizarán.
                </li>
                <li>
                    <i class="fas fa-exclamation-triangle text-danger mr-1"></i>
                    Si quitas productos y el nuevo total queda menor a lo ya pagado,
                    se mostrará un <strong>error</strong> y no se permitirá la acción.
                </li>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>


<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-12">
                            @include('pedidos.pedido.forms.form_pedido_edit')
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group row">
                                <div class="col-md-6 text-left">
                                    <i class="fa fa-exclamation-circle leyenda-required"></i> <small
                                        class="leyenda-required">Los campos marcados con asterisco
                                        (<label class="required"></label>) son obligatorios.</small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="{{ route('pedidos.pedido.index') }}" id="btn_cancelar"
                                        class="btn btn-w-m btn-default">
                                        <i class="fa fa-arrow-left"></i> Regresar
                                    </a>

                                    <button type="submit" id="btn_grabar" form="formActualizarPedido"
                                        class="btn btn-w-m btn-success">
                                        <i class="fa fa-save"></i> Grabar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
@push('styles')
<style>
    .search-length-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .buttons-container {
        display: flex;
        justify-content: end;
    }


    .custom-button {
        background-color: #ffffff !important;
        color: #000000 !important;
        border: 1px solid #dcdcdc !important;
        border-radius: 5px;
        padding: 8px 16px;
        font-size: 14px;
        margin: 8px 0px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: background-color 3s, color 3s;
    }

    .custom-button:hover {
        background-color: #d7e9fb !important;
        color: #000000 !important;
        border-color: #d7e9fb !important;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    let precioVentaOriginal = null;
    let precioVentaFinal = null;
    let porcentajeDescuentoPrecioVenta = null;

    const precioOriginalSpan = document.getElementById('precio_original_span');
    const precioOriginalText = document.getElementById('precio_original_text');
    const descuentoSpan = document.getElementById('descuento_span');

    const tfootSubtotal = document.querySelector('.subtotal');
    const tfootTotal = document.querySelector('.total');
    const tfootIgv = document.querySelector('.igv');
    const tfootTotalPagar = document.querySelector('.total-pagar');
    const tfootDescuento = document.querySelector('.descuento');
    const tfootEmbalaje = document.querySelector('.embalaje');
    const tfootEnvio = document.querySelector('.envio');

    const amountsPedido = {
        subtotal: 0,
        embalaje: 0,
        envio: 0,
        total: 0,
        igv: 0,
        totalPagar: 0,
        monto_descuento: 0
    }


    let noEditTotal = 0;

    let pedidos_data_table = null;
    let carrito = [];
    let dataTableStocksPedido = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadSelect2();
        cargarProductosPrevios();
        //pintarTablePedidoAtencionHistorial();
        events();
        iniciarSelectsMdlEnvio();
        eventsCliente();
        eventsModalEnvio();
        setDatosDefault();
        setDatosEnvioPrevio();
    })

    function events() {

        const cantPagos = @json($cant_pagos);

        if (cantPagos == 0) {
            document.getElementById('img_pago_1').addEventListener('change', (e) => {
                accionImgPago1(e);
            });
        }

        //====== ELIMINAR ITEM =========
        document.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-product')) {

                toastr.clear();
                const productoId = e.target.getAttribute('data-producto');
                const colorId = e.target.getAttribute('data-color');

                //======== OBTENIENDO TODOS LOS PRODUCTOS DE ESE PRODUCTOID Y COLORID =======
                const lstProductos = [];
                carrito.forEach((producto) => {
                    producto.tallas.forEach((talla) => {

                        if (producto.producto_id == productoId && producto.color_id ==
                            colorId) {
                            const item = {
                                producto_id: producto.producto_id,
                                color_id: producto.color_id,
                                talla_id: talla.talla_id,
                                producto_nombre: producto.producto_nombre,
                                color_nombre: producto.color_nombre,
                                talla_nombre: talla.talla_nombre,
                                cantidad: 0
                            }
                            lstProductos.push(item);
                        }

                    })
                })

                mostrarAnimacion();
                const resValidacion = await validarCantAtendProductos(lstProductos);
                if (!resValidacion) {
                    return;
                }

                const cantProductosEliminados = eliminarProducto(lstProductos);

                if (cantProductosEliminados > 0) {
                    calcularSubTotal(carrito);
                    calcularMontos();
                    pintarDetallePedido(carrito);
                    clearInputsCantidad();
                    loadCarrito();

                    toastr.info('SE ELIMINARON SOLO LOS PRODUCTOS CUYA CANTIDAD ATENDIDA NO SEA AFECTADA');
                } else {
                    toastr.error('NO SE PUDO ELIMINAR NINGÚN PRODUCTO DE LA FILA');
                }

                ocultarAnimacion();

            }
        })

        //====== SUBMIT FORM PEDIDOS =======
        document.querySelector('#formActualizarPedido').addEventListener('submit', (e) => {

            e.preventDefault();
            //===== VALIDAR FECHA =====
            const correcto = validarDatosPedido();

            if (correcto) {
                actualizarPedido(e.target);
            }

        })


        //===== VALIDAR CONTENIDO DE INPUTS CANTIDAD ========
        //===== VALIDAR TFOOTS EMBALAJE Y ENVIO ======
        document.addEventListener('input', (e) => {

            if (e.target.classList.contains('inputCantidad')) {
                e.target.value = e.target.value.replace(/^0+|[^0-9]/g, '');
            }

            if (e.target.classList.contains('detailDescuento')) {
                //==== CONTROLANDO DE QUE EL VALOR SEA UN NÚMERO ====
                const valor = event.target.value;
                const producto_id = e.target.getAttribute('data-producto-id');
                const color_id = e.target.getAttribute('data-color-id');

                //==== SI EL INPUT ESTA VACÍO ====
                if (valor.trim().length === 0) {
                    calcularDescuento(producto_id, color_id, 0);
                    return;
                }

                //===== EXPRESION REGULAR PARA EVITAR CARACTERES NO NUMÉRICOS EN LA CADENA ====
                const regex = /^[0-9]+(\.[0-9]{0,2})?$/;
                //==== BORRAR CARACTER NO NUMÉRICO ====
                if (!regex.test(valor)) {
                    event.target.value = valor.slice(0, -1);
                    return;
                }

                //==== EN CASO SEA NUMÉRICO ====
                let porcentaje_desc = parseFloat(event.target.value);

                //==== EL MÁXIMO DESCUENTO ES 100% ====
                if (porcentaje_desc > 100) {
                    event.target.value = 100;
                    porcentaje_desc = event.target.value;
                }

                //==== CALCULAR DESCUENTO ====
                calcularDescuento(producto_id, color_id, porcentaje_desc)
            }

            //====== EMBALAJE Y ENVÍO =======
            if (e.target.classList.contains('embalaje') || e.target.classList.contains('envio')) {
                // Eliminar ceros a la izquierda, excepto si es el único carácter en el campo o si es seguido por un punto decimal y al menos un dígito
                e.target.value = e.target.value.replace(
                    /^0+(?=\d)|(?<=\D)0+(?=\d)|(?<=\d)0+(?=\.)|^0+(?=[1-9])/g, '');

                // Evitar que el primer carácter sea un punto
                e.target.value = e.target.value.replace(/^(\.)/, '');

                // Reemplazar todo excepto los dígitos y el punto decimal
                e.target.value = e.target.value.replace(/[^\d.]/g, '');

                // Reemplazar múltiples puntos decimales con uno solo
                e.target.value = e.target.value.replace(/(\..*)\./g, '$1');

                calcularMontos();
            }
        })

        //======== AGREGAR PRODUCTO AL DETALLE =====
        document.querySelector('#btn_agregar_detalle').addEventListener('click', async () => {

            toastr.clear();

            const validacion = validacionAgregarProducto();
            if (!validacion) {
                ocultarAnimacion();
                return;
            };

            const inputProductos = obtenerProductos();
            res_validacion = await validarCantAtendProductos(inputProductos);

            if (!res_validacion) {
                return;
            }

            agregarProducto(inputProductos);
            reordenarCarrito();
            calcularSubTotal(carrito);
            pintarDetallePedido(carrito);

            carrito.forEach((c) => {
                calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
            })

            //===== RECALCULANDO MONTOS =====
            calcularMontos();

            toastr.info("PRODUCTOS AGREGADOS");

        })

        //=========== MODAL DESPACHO =========
        document.querySelector('.btn-envio').addEventListener('click', () => {
            accionOpenMdlEnvio();
        })

    }

    async function validarCantAtendProductos(inputProductos) {
        try {
            mostrarAnimacion();
            const res = await axios.post(route('pedidos.pedido.validarCantidadAtendida'), {
                pedido_id: @json($pedido->id),
                lstProductos: JSON.stringify(inputProductos)
            })

            if (res.data.success) {
                toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }
            return res.data.success;
        } catch (error) {
            toastr.error(error, 'ERROR AL VALIDAR CANTIDADES DE PRODUCTOS');
            return false;
        } finally {
            ocultarAnimacion();
        }
    }

    //====== SELECT2 =======
    function loadSelect2() {
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });

        $(".select2_modal_cliente").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            width: '100%'
        });

        $('#producto').select2({
            width: '100%',
            placeholder: "Buscar producto...",
            allowClear: true,
            language: {
                inputTooShort: function(args) {
                    var min = args.minimum;
                    return "Por favor, ingrese " + min + " o más caracteres";
                },
                searching: function() {
                    return "BUSCANDO...";
                },
                noResults: function() {
                    return "No se encontraron productos";
                }
            },
            ajax: {
                url: '{{ route('pedidos.pedido.getProductos') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        almacen_id: $('#almacen').val(),
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    if (data.success) {
                        params.page = params.page || 1;
                        const productos = data.productos;
                        return {
                            results: productos.map(item => ({
                                id: item.producto_id,
                                text: item.producto_completo
                            })),
                            pagination: {
                                more: data.more
                            }
                        };
                    } else {
                        toastr.error(data.message, 'ERROR EN EL SERVIDOR');
                        return {
                            results: []
                        }
                    }

                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: function(data) {
                if (data.loading) {
                    return $(
                        '<span><i style="color:blue;" class="fa fa-spinner fa-spin"></i> Buscando...</span>'
                    );
                }
                return data.text;
            },
        });

        $('#cliente').select2({
            width: '100%',
            placeholder: "Buscar Cliente...",
            allowClear: true,
            language: {
                inputTooShort: function(args) {
                    var min = args.minimum;
                    return "Por favor, ingrese " + min + " o más caracteres";
                },
                searching: function() {
                    return "BUSCANDO...";
                },
                noResults: function() {
                    return "No se encontraron clientes";
                }
            },
            ajax: {
                url: '{{ route('utilidades.getClientes') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    if (data.success) {
                        params.page = params.page || 1;
                        const clientes = data.clientes;
                        return {
                            results: clientes.map(item => ({
                                id: item.id,
                                text: item.descripcion,
                                telefono: item.telefono_movil
                            })),
                            pagination: {
                                more: data.more
                            }
                        };
                    } else {
                        toastr.error(data.message, 'ERROR EN EL SERVIDOR');
                        return {
                            results: []
                        }
                    }

                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: function(data) {
                if (data.loading) {
                    return $(
                        '<span><i style="color:blue;" class="fa fa-spinner fa-spin"></i> Buscando...</span>'
                    );
                }
                const $option = $('<span>', {
                    text: data.text
                }).attr('data-telefono', data.telefono || '');

                return $option;
                //return data.text;
            },
        });
    }

    //========= SWAL ======
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger',
        },
        buttonsStyling: false
    })

    function obtenerProductos() {

        const lstProductos = [];
        const inputsCantidad = document.querySelectorAll('.inputCantidad');

        inputsCantidad.forEach((inputCantidad) => {
            const producto = formarProducto(inputCantidad);
            lstProductos.push(producto);
        })
        return lstProductos;

    }

    function agregarProducto() {
        const inputsCantidad = document.querySelectorAll('.inputCantidad');

        for (const ic of inputsCantidad) {

            const cantidad = ic.value ? ic.value : null;
            if (cantidad) {

                const producto = formarProducto(ic);
                const indiceExiste = carrito.findIndex(p => p.producto_id == producto.producto_id && p.color_id ==
                    producto.color_id);

                //===== PRODUCTO NUEVO =====
                if (indiceExiste == -1) {
                    const objProduct = {
                        producto_id: producto.producto_id,
                        color_id: producto.color_id,
                        modelo_nombre: producto.modelo_nombre,
                        producto_nombre: producto.producto_nombre,
                        producto_codigo: producto.producto_codigo,
                        color_nombre: producto.color_nombre,
                        precio_venta: producto.precio_venta,
                        monto_descuento: producto.monto_descuento,
                        porcentaje_descuento: producto.porcentaje_descuento,
                        precio_venta_nuevo: producto.precio_venta_nuevo,
                        subtotal_nuevo: 0,
                        tallas: [{
                            talla_id: producto.talla_id,
                            talla_nombre: producto.talla_nombre,
                            cantidad: producto.cantidad
                        }]
                    };

                    carrito.push(objProduct);
                } else {
                    const productoModificar = carrito[indiceExiste];
                    productoModificar.precio_venta = producto.precio_venta;
                    productoModificar.precio_venta_nuevo = producto.precio_venta_nuevo;
                    productoModificar.porcentaje_descuento = producto.porcentaje_descuento;

                    const indexTalla = productoModificar.tallas.findIndex(t => t.talla_id == producto.talla_id);

                    if (indexTalla !== -1) {
                        const cantidadAnterior = productoModificar.tallas[indexTalla].cantidad;
                        productoModificar.tallas[indexTalla].cantidad = producto.cantidad;
                        carrito[indiceExiste] = productoModificar;
                    } else {
                        const objTallaProduct = {
                            talla_id: producto.talla_id,
                            talla_nombre: producto.talla_nombre,
                            cantidad: producto.cantidad
                        };
                        carrito[indiceExiste].tallas.push(objTallaProduct);
                    }
                }
            } else {
                const producto = formarProducto(ic);
                const indiceProductoColor = carrito.findIndex(p => p.producto_id == producto.producto_id && p
                    .color_id == producto.color_id);

                if (indiceProductoColor !== -1) {
                    const indiceTalla = carrito[indiceProductoColor].tallas.findIndex(t => t.talla_id == producto
                        .talla_id);

                    if (indiceTalla !== -1) {
                        const cantidadAnterior = carrito[indiceProductoColor].tallas[indiceTalla].cantidad;
                        carrito[indiceProductoColor].tallas.splice(indiceTalla, 1);

                        const cantidadTallas = carrito[indiceProductoColor].tallas.length;

                        if (cantidadTallas == 0) {
                            carrito.splice(indiceProductoColor, 1);
                        }
                    }
                }
            }
        }
    }

    //======== ELIMINAR ITEM ========
    const eliminarProducto = (lstProductosValidados) => {
        let productosEliminados = 0;

        //========= RECORRER LOS PRODUCTOS VALIDADOS ===========
        lstProductosValidados.forEach((producto_validado) => {

            //======= SI EL PRODUCTO ES VÁLIDO =======
            //if(producto_validado.validacion){

            //========== ELIMINAR =========
            const indiceProducto = carrito.findIndex((producto) => {
                return producto.producto_id == producto_validado.producto_id && producto.color_id ==
                    producto_validado.color_id;
            })

            if (indiceProducto !== -1) {
                //========= OBTENIENDO TALLA ======
                const indiceTalla = carrito[indiceProducto].tallas.findIndex((talla) => {
                    return talla.talla_id == producto_validado.talla_id;
                })

                if (indiceTalla !== -1) {
                    //======= ELIMINAR TALLA ========
                    carrito[indiceProducto].tallas.splice(indiceTalla, 1);

                    productosEliminados++;

                    //======= EN CASO EL PRODUCTO SE QUEDE SIN TALLAS, ELIMINAR PRODUCTO =======
                    if (carrito[indiceProducto].tallas.length === 0) {
                        carrito.splice(indiceProducto, 1);
                    }

                }
            }
            //}

        })

        return productosEliminados;
    }

    //========= VALIDAR FECHAS =========
    function validarDatosPedido() {
        let enviar = true;

        if (carrito.length === 0) {
            toastr.error('El detalle del pedido está vacío.', 'Error');
            enviar = false;
        }

        return enviar
    }

    //======= CARGAR PRODUCTOS ========
    function setProductosForm() {
        document.querySelector('#productos_tabla').value = JSON.stringify(carrito);
    }

    //=========== CALCULAR MONTOS =======
    const calcularMontos = () => {
        let subtotal = 0;
        let embalaje = tfootEmbalaje.value ? parseFloat(tfootEmbalaje.value) : 0;
        let envio = tfootEnvio.value ? parseFloat(tfootEnvio.value) : 0;
        let total = 0;
        let igv = 0;
        let total_pagar = 0;
        let descuento = 0;

        //====== subtotal es la suma de todos los productos ======
        carrito.forEach((c) => {
            subtotal += parseFloat(c.subtotal_nuevo);
            descuento += parseFloat(c.monto_descuento);
        })

        subtotal += noEditTotal;
        total_pagar = subtotal + embalaje + envio;
        total = total_pagar / 1.18;
        igv = total_pagar - total;

        tfootTotalPagar.textContent = 'S/. ' + total_pagar.toFixed(2);
        tfootIgv.textContent = 'S/. ' + igv.toFixed(2);
        tfootTotal.textContent = 'S/. ' + total.toFixed(2);
        tfootSubtotal.textContent = 'S/. ' + subtotal.toFixed(2);
        tfootDescuento.textContent = 'S/. ' + descuento.toFixed(2);

        amountsPedido.totalPagar = total_pagar.toFixed(2);
        amountsPedido.igv = igv.toFixed(2);
        amountsPedido.total = total.toFixed(2);
        amountsPedido.embalaje = embalaje.toFixed(2);
        amountsPedido.envio = envio.toFixed(2);
        amountsPedido.subtotal = subtotal.toFixed(2);
        amountsPedido.monto_descuento = descuento.toFixed(2);
    }

    //======== CALCULAR DESCUENTO ========
    const calcularDescuento = (producto_id, color_id, porcentaje_descuento) => {
        const indiceExiste = carrito.findIndex((c) => {
            return c.producto_id == producto_id && c.color_id == color_id;
        })

        if (indiceExiste !== -1) {
            const producto_color_editar = carrito[indiceExiste];

            //===== APLICANDO DESCUENTO ======
            producto_color_editar.porcentaje_descuento = porcentaje_descuento;
            producto_color_editar.monto_descuento = parseFloat(producto_color_editar.subtotal) - parseFloat(
                producto_color_editar.subtotal_nuevo);
            producto_color_editar.precio_venta_nuevo = producto_color_editar.precio_venta_nuevo;
            producto_color_editar.subtotal_nuevo = producto_color_editar.subtotal_nuevo;

            carrito[indiceExiste] = producto_color_editar;

            //==== RECALCULANDO MONTOS ====
            calcularMontos();

        }
    }

    //========= REORDENAR CARRITO =========
    const reordenarCarrito = () => {
        carrito.sort(function(a, b) {
            if (a.producto_id === b.producto_id) {
                return a.color_id - b.color_id;
            } else {
                return a.producto_id - b.producto_id;
            }
        });
    }

    function destruirDataTableStocks() {
        if (dataTableStocksPedido) {
            dataTableStocksPedido.destroy();
            dataTableStocksPedido = null;
        }
    }

    function limpiarTableStocks() {
        const tableStocksBody = document.querySelector('#table-stocks-pedidos tbody');

        while (tableStocksBody.firstChild) {
            tableStocksBody.removeChild(tableStocksBody.firstChild);
        }
    }

    //====== CARGAR EL PRECIO DE VENTA ELEGIDO PARA EL PRODUCTO EN EL CARRITO ======
    function loadPrecioVentaProductoCarrito(producto_id) {
        const producto_elegido_id = producto_id;
        //===== LO BUSCAMOS EN EL CARRITO ======
        const indiceProducto = carrito.findIndex((p) => {
            return p.producto_id == producto_elegido_id;
        })

        if (indiceProducto !== -1) {
            const itemProducto = carrito[indiceProducto];
            let targetValue;
            //==== UBICANDO PRECIO VENTA SELECCIONADO ======
            $('#precio_venta option').each(function() {
                if ($(this).text() == itemProducto.precio_venta) {
                    targetValue = $(this).val();
                    return false;
                }
            });

            if (targetValue) {
                $('#precio_venta').val(targetValue).trigger('change');
            }
        } else {
            toastr.info('NO SE PUEDO FIJAR EL PRECIO DE VENTA PREVIO PARA EL PRODUCTO');
        }

    }

    //=========== FORMAR PRODUCTO =========
    const formarProducto = (ic) => {
        const producto_id = ic.getAttribute('data-producto-id');
        const modelo_nombre = $('#modelo').find('option:selected').text().trim();
        const producto_nombre = ic.getAttribute('data-producto-nombre');
        const producto_codigo = ic.getAttribute('data-producto-codigo');
        const color_id = ic.getAttribute('data-color-id');
        const color_nombre = ic.getAttribute('data-color-nombre');
        const talla_id = ic.getAttribute('data-talla-id');
        const talla_nombre = ic.getAttribute('data-talla-nombre');
        const precio_venta = parseFloat(precioVentaOriginal);
        const cantidad = ic.value ? ic.value : 0;
        const subtotal = 0;
        const subtotal_nuevo = 0;

        const monto_descuento = 0;
        const porcentaje_descuento = ((precioVentaOriginal - precioVentaFinal) / precioVentaOriginal) * 100;
        const precio_venta_nuevo = parseFloat(precioVentaFinal);

        const producto = {
            producto_id,
            producto_nombre,
            producto_codigo,
            modelo_nombre,
            color_id,
            color_nombre,
            talla_id,
            talla_nombre,
            cantidad,
            precio_venta,
            subtotal,
            subtotal_nuevo,
            porcentaje_descuento,
            monto_descuento,
            precio_venta_nuevo
        };
        return producto;
    }

    //======== CALCULAR SUBTOTAL POR PRODUCTO COLOR EN EL DETALLE ======
    const calcularSubTotal = (productos) => {
        let subtotal = 0;
        let subtotal_nuevo = 0;

        productos.forEach((p) => {
            p.tallas.forEach((t) => {
                subtotal += parseFloat(p.precio_venta) * parseFloat(t.cantidad);
                subtotal_nuevo += parseFloat(p.precio_venta_nuevo) * parseFloat(t.cantidad);
            })

            p.subtotal = subtotal;
            p.subtotal_nuevo = subtotal_nuevo;
            subtotal = 0;
            subtotal_nuevo = 0;
        })
    }

    //========= PINTAR DETALLE PEDIDO =======
    //========= PINTAR DETALLE PEDIDO =======
    function pintarDetallePedido(carrito) {
        let fila = ``;
        let htmlTallas = ``;
        const bodyDetalleTable = document.querySelector('#table-detalle-pedido tbody');
        const tallas = @json($tallas);
        clearTabla(bodyDetalleTable);

        carrito.forEach((c) => {
            htmlTallas = ``;
            fila += `<tr>
                        <td>
                            <i class="fas fa-trash-alt btn btn-danger delete-product"
                            data-producto="${c.producto_id}" data-color="${c.color_id}">
                            </i>
                        </td>
                        <th>
                            <div style="width:120px;">${c.producto_nombre}</div>
                        </th>
                        <th>${c.color_nombre}</th>`;

            // tallas
            tallas.forEach((t) => {
                let cantidad = c.tallas.filter((ct) => t.id == ct.talla_id);
                cantidad = cantidad.length ? cantidad[0].cantidad : '';
                htmlTallas += `<td>${cantidad}</td>`;
            });

            // precio venta con comparación
            htmlTallas += `<td style="text-align: right;">
            <div style="width:100px;">`;
            if (parseFloat(c.precio_venta) !== parseFloat(c.precio_venta_nuevo)) {
                htmlTallas += `
                <del style="color: gray;">${parseFloat(c.precio_venta).toFixed(2)}</del><br>
                <strong>${parseFloat(c.precio_venta_nuevo).toFixed(2)}</strong>
            `;
            } else {
                htmlTallas += `
                ${parseFloat(c.precio_venta_nuevo).toFixed(2)}
            `;
            }
            htmlTallas += `</div></td>`;

            // subtotal con comparación
            htmlTallas += `<td class="td-subtotal" style="text-align: right;">`;
            if (parseFloat(c.subtotal) !== parseFloat(c.subtotal_nuevo)) {
                htmlTallas += `
                <del style="color: gray;">${parseFloat(c.subtotal).toFixed(2)}</del><br>
                <strong>${parseFloat(c.subtotal_nuevo).toFixed(2)}</strong>
            `;
            } else {
                htmlTallas += `
                ${parseFloat(c.subtotal_nuevo).toFixed(2)}
            `;
            }
            htmlTallas += `</td>`;

            // porcentaje descuento
            htmlTallas += `
            <td style="text-align: center;">
                <span data-producto-id="${c.producto_id}" data-color-id="${c.color_id}"
                style="width:130px; margin: 0 auto;"
                class="detailDescuento">${parseFloat(c.porcentaje_descuento).toFixed(2)}</span>
            </td>
        `;

            fila += htmlTallas + `</tr>`;
            bodyDetalleTable.innerHTML = fila;
        });
    }

    function formatearPrecioVentaFinal(event) {
        let valorInput = event.target.value;

        valorInput = valorInput.replace(/[^\d.]/g, '');

        if (valorInput.startsWith('.')) {
            valorInput = '';
        }

        const partes = valorInput.split('.');
        if (partes.length > 2) {
            valorInput = partes[0] + '.' + partes[1];
        }

        if (partes[1]?.length > 2) {
            valorInput = partes[0] + '.' + partes[1].slice(0, 2);
        }

        const final = parseFloat(valorInput);
        const original = parseFloat(precioVentaOriginal);

        precioVentaFinal = parseFloat(valorInput);

        if (!isNaN(final) && final >= 0 && original > 0 && final < original) {
            const descuento = ((original - final) / original) * 100;
            porcentajeDescuentoPrecioVenta = Math.round(descuento * 100) / 100;
        }
        if (final === original) {
            porcentajeDescuentoPrecioVenta = 0;
        }

        pintarPrecioYDescuento(original, final, porcentajeDescuentoPrecioVenta);

        event.target.value = valorInput;
    }

    function pintarPrecioYDescuento(original, final, porcentajeDescuento) {
        const precioOriginalSpan = document.getElementById('precio_original_span');
        const descuentoSpan = document.getElementById('descuento_span');
        const precioOriginalText = document.getElementById('precio_original_text');

        precioOriginalText.textContent = original.toFixed(2);

        if (!isNaN(final) && final !== original) {
            precioOriginalSpan.style.textDecoration = 'line-through';
            if (final < original) {
                descuentoSpan.textContent = `-${porcentajeDescuento}%`;
                descuentoSpan.style.display = 'inline';
            } else {
                descuentoSpan.style.display = 'none';
            }
        } else {
            precioOriginalSpan.style.textDecoration = 'none';
            descuentoSpan.style.display = 'none';
        }
    }

    //======= PINTAR PRECIOS VENTA =======
    function pintarPreciosVenta(producto_color_tallas) {

        //====== LLENAR =======

        if (producto_color_tallas) {
            if (producto_color_tallas.precio_venta_1 != null) {
                document.querySelector('#precio_venta').value = producto_color_tallas.precio_venta_1;
                precioVentaOriginal = producto_color_tallas.precio_venta_1;
                precioVentaFinal = producto_color_tallas.precio_venta_1;
            }

        }

    }

    //===== LIMPIAR INPUTS DEL TABLERO PRODUCTOS ======
    function clearInputsCantidad() {
        const inputsCantidad = document.querySelectorAll('.inputCantidad');
        inputsCantidad.forEach((inputCantidad) => {
            inputCantidad.value = '';
        })
    }

    //======= LLENAR INPUTS CON CANTIDADES EXISTENTES EN EL CARRITO =========
    function loadCarrito() {

        carrito.forEach((c) => {
            c.tallas.forEach((talla) => {
                let llave = `#inputCantidad_${c.producto_id}_${c.color_id}_${talla.talla_id}`;
                const inputLoad = document.querySelector(llave);
                if (inputLoad) {
                    inputLoad.value = talla.cantidad;
                }
            })
        })

    }

    //======= OBTENER COLORES Y TALLAS POR PRODUCTO =======
    async function getColoresTallas() {
        mostrarAnimacion();
        const producto_id = $('#producto').val();
        const almacen_id = $('#almacen').val();
        if (producto_id && almacen_id) {
            try {
                const res = await axios.get(route('pedidos.pedido.getColoresTallas', {
                    almacen_id,
                    producto_id
                }));
                if (res.data.success) {
                    destruirDataTableStocks();
                    pintarTableStocks(res.data.producto_color_tallas);
                    loadDataTableStocksPedido();
                    pintarPreciosVenta(res.data.producto_color_tallas);
                    loadCarrito();
                    loadPrecioVentaProductoCarrito(producto_id);
                } else {
                    toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER COLORES Y TALLAS');
            } finally {
                ocultarAnimacion();
            }
        } else {
            destruirDataTableStocks();
            limpiarTableStocks();
            loadDataTableStocksPedido();
            limpiarSelectPreciosVenta();
            ocultarAnimacion();
        }
    }

    //======== PINTAR SELECT PRODUCTOS =======
    function pintarSelectProductos(productos) {
        //======= LIMPIAR SELECT2 DE PRODUCTOS ======
        $('#producto').empty();

        if (productos.length === 0) {
            ocultarAnimacion();
        }

        //====== LLENAR =======
        productos.forEach((producto) => {
            const option = new Option(producto.nombre, producto.id, false, false);
            $('#producto').append(option);
        });

        // Refrescar Select2
        //$('#producto').val(null);
        $('#producto').trigger('change');
    }

    async function getProductosByModelo(e) {
        toastr.clear();
        mostrarAnimacion();
        const bodyTableStocks = document.querySelector('#table-stocks-pedidos tbody');
        const btnAgregarDetalle = document.querySelector('#btn_agregar_detalle');
        clearTabla(bodyTableStocks);

        const modelo_id = e.value;
        btnAgregarDetalle.disabled = true;

        if (modelo_id) {
            try {
                const res = await axios.get(route('pedidos.pedido.getProductosByModelo', modelo_id));
                if (res.data.success) {
                    pintarSelectProductos(res.data.productos);
                    toastr.info('PRODUCTOS CARGADOS', 'OPERACIÓN COMPLETADA');
                } else {
                    ocultarAnimacion();
                    toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                ocultarAnimacion();
                toastr.error(error, 'ERROR EN LA PETICIÓN DE OBTENER PRODUCTOS');
            }

        } else {
            ocultarAnimacion();
        }
    }


    //========== OBTENER PRODUCTOS POR MODELO =========
    /*async function getProductosByModelo(target){
        const   btnAgregarDetalle   =   document.querySelector('#btn_agregar_detalle')
        const modelo_id =   target.value;
        btnAgregarDetalle.disabled = true;

        if(modelo_id){
            try {
                const res       =   await axios.get(route('pedidos.pedido.getProductosByModelo', modelo_id));
                const productos =   res.data.message;
                console.log(productos);
                pintarTableProductos(productos);
                setCantidadesTablero();
            } catch (error) {

            }
        }else{
            const bodyTablaProductos    =   document.querySelector('#table-stocks-pedidos tbody');
            clearTabla(bodyTablaProductos);
        }
    }*/

    const pintarTableStocks = (producto) => {
        let filas = ``;
        const tableStocksBody = document.querySelector('#table-stocks-pedidos tbody');
        const btnAgregarDetalle = document.querySelector('#btn_agregar_detalle')


        producto.colores.forEach((color) => {
            filas += `  <tr>
                            <th scope="row" data-producto=${producto.id} data-color=${color.id} >
                                <div style="width:200px;">${producto.nombre}</div>
                            </th>
                            <th scope="row">${color.nombre}</th>
                        `;

            color.tallas.forEach((talla) => {
                filas += `<td style="background-color: rgb(210, 242, 242);">
                                        <p style="margin:0;width:20px;text-align:center;${talla.stock_logico != 0?'font-weight:bold':''};">${talla.stock_logico}</p>
                            </td>
                            <td width="8%">
                                <input style="width:50px;text-align:center;" type="text" class="form-control inputCantidad"
                                id="inputCantidad_${producto.id}_${color.id}_${talla.id}"
                                data-producto-id="${producto.id}"
                                data-producto-nombre="${producto.nombre}"
                                data-color-nombre="${color.nombre}"
                                data-talla-nombre="${talla.nombre}"
                                data-color-id="${color.id}" data-talla-id="${talla.id}"></input>
                            </td>`;
            })

            filas += `</tr>`;

        })

        tableStocksBody.innerHTML = filas;
        btnAgregarDetalle.disabled = false;

    }

    function loadDataTableStocksPedido() {
        dataTableStocksPedido = new DataTable('#table-stocks-pedidos', {
            language: {
                "sEmptyTable": "No hay datos disponibles en la tabla",
                "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "sInfoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "sInfoFiltered": "(filtrado de _MAX_ entradas totales)",
                "sInfoPostFix": "",
                "sInfoThousands": ",",
                "sLengthMenu": "Mostrar _MENU_ entradas",
                "sLoadingRecords": "Cargando...",
                "sProcessing": "Procesando...",
                "sSearch": "Buscar:",
                "sZeroRecords": "No se encontraron resultados",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": activar para ordenar la columna de manera descendente"
                }
            }
        });
    }



    //======== PINTAR TABLA PRODUCTOS =========
    /*function pintarTableStocks(productos){
        let     options             =   ``;
        const   tallas              =   @json($tallas);
        const   tableStocksBody     =   document.querySelector('#table-stocks-pedidos tbody');
        const   btnAgregarDetalle   =   document.querySelector('#btn_agregar_detalle')

        productos.forEach((p)=>{
            options+=`  <tr>
                            <th scope="row" data-producto=${p.producto_id} data-color=${p.color_id} >
                                <div style="width:120px;">${p.producto_nombre}</div>
                            </th>
                            <th>${p.color_nombre}</th>
                        `;

            let htmlTallas = ``;

            tallas.forEach((t)=>{
                const stock = p.tallas.filter(pt => pt.talla_id == t.id)[0]?.stock_logico || 0;

                htmlTallas +=   `
                                    <td style="background-color: rgb(210, 242, 242);">
                                        <p style="margin:0;width:20px;text-align:center;">${stock}</p>
                                    </td>
                                    <td width="8%">
                                        <input style="width:50px;text-align:center;" type="text" class="form-control inputCantidad"
                                        id="inputCantidad_${p.producto_id}_${p.color_id}_${t.id}"
                                        data-modelo-nombre="${p.modelo_nombre}"
                                        data-producto-id="${p.producto_id}"
                                        data-producto-nombre="${p.producto_nombre}"
                                        data-producto-codigo="${p.producto_codigo}"
                                        data-color-id="${p.color_id}"
                                        data-color-nombre="${p.color_nombre}"
                                        data-talla-id="${t.id}"
                                        data-talla-nombre="${t.descripcion}"></input>
                                    </td>
                                `;
            })

            if(p.print_precios){
                htmlTallas+=`
                    <td>
                        <select style="width:100px;" class="select2_form form-control" id="precio-venta-${p.producto_id}">
                            <option>${p.precio_venta_1}</option>
                            <option>${p.precio_venta_2}</option>
                            <option>${p.precio_venta_3}</option>
                        </select>
                    </td>`;
            }else{
                htmlTallas+=`<td></td>`;
            }

            htmlTallas += `</tr>`;
            options += htmlTallas;
        })

        tableStocksBody.innerHTML = options;
        btnAgregarDetalle.disabled = false;
    }*/

    //======== LIMPIAR TABLA PRODUCTOS ========
    function clearTabla(bodyTable) {
        while (bodyTable.firstChild) {
            bodyTable.removeChild(bodyTable.firstChild);
        }
    }


    //=========== CARGAR PRODUCTOS PREVIOS =======
    const cargarProductosPrevios = () => {

        //====== CARGANDO EMBALAJE Y ENVÍO PREVIO =======
        tfootEmbalaje.value = @json($pedido->monto_embalaje);
        tfootEnvio.value = @json($pedido->monto_envio);

        const productosPrevios = @json($pedido_detalles);

        //====== CARGANDO CARRITO ======
        const producto_color_procesados = [];

        productosPrevios.forEach((productoPrevio) => {
            const id = `${productoPrevio.producto_id}-${productoPrevio.color_id}`;

            if (!producto_color_procesados.includes(id)) {
                const producto = {
                    producto_id: productoPrevio.producto_id,
                    producto_nombre: productoPrevio.producto_nombre,
                    producto_codigo: productoPrevio.producto_codigo,
                    modelo_nombre: productoPrevio.modelo_nombre,
                    color_id: productoPrevio.color_id,
                    color_nombre: productoPrevio.color_nombre,
                    precio_venta: productoPrevio.precio_unitario,
                    precio_venta_nuevo: productoPrevio.precio_unitario_nuevo,
                    subtotal: 0,
                    subtotal_nuevo: 0,
                    porcentaje_descuento: parseFloat(productoPrevio.porcentaje_descuento),
                    monto_descuento: 0,
                    tallas: []
                }

                //==== BUSCANDO SUS TALLAS ====
                const tallas = productosPrevios.filter((t) => {
                    return t.producto_id == productoPrevio.producto_id && t.color_id ==
                        productoPrevio.color_id;
                })

                if (tallas.length > 0) {
                    const producto_color_tallas = [];
                    tallas.forEach((t) => {
                        const talla = {
                            talla_id: t.talla_id,
                            talla_nombre: t.talla_nombre,
                            cantidad: parseInt(t.cantidad),
                        }
                        producto_color_tallas.push(talla);
                    })
                    producto.tallas = producto_color_tallas;
                }
                producto_color_procesados.push(id);
                carrito.push(producto);
            }
        })

        //===== CALCULAR SUBTOTAL POR FILA DEL DETALLE ======
        calcularSubTotal(carrito);

        //===== PINTANDO DETALLE ======
        pintarDetallePedido(carrito);

        carrito.forEach((c) => {
            calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
        })

        //===== CALCULAR MONTOS Y PINTARLOS ======
        calcularMontos();

    }

    function pintarDetalleNoEditable(productosNoEditables) {
        const tbodyNoEditable = document.querySelector('#table-detalle-noeditable tbody');
        let fila = ``;
        let total = 0;
        productosNoEditables.forEach((p) => {
            fila += `<tr>
                                <th scope="row">${p.producto_nombre}</th>
                                <td>${p.color_nombre}</td>
                                <td>${p.talla_nombre}</td>
                                <td>${p.cantidad}</td>
                                <td>${p.cantidad_atendida}</td>
                                <td>${p.cantidad_pendiente}</td>
                                <td>${p.precio_unitario_nuevo}</td>
                                <td>${p.importe_nuevo}</td>
                            </tr>`;
            total += parseFloat(p.importe_nuevo);
        })
        tbodyNoEditable.innerHTML = fila;

        noEditTotal = parseFloat(total);
        const formattedTotal = 'S/ ' + total.toLocaleString('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.querySelector('.total-pagar-noedit').textContent = formattedTotal;
    }

    //======= LLENAR INPUTS CON CANTIDADES EXISTENTES EN EL CARRITO =========
    function setCantidadesTablero() {
        carrito.forEach((c) => {
            c.tallas.forEach((t) => {
                const inputLoad = document.querySelector(
                    `#inputCantidad_${c.producto_id}_${c.color_id}_${t.talla_id}`);
                if (inputLoad) {
                    inputLoad.value = t.cantidad;
                }
            })
            //==== UBICANDO PRECIOS DE VENTA SELECCIONADOS ======
            const selectPrecioVenta = document.querySelector(`#precio-venta-${c.producto_id}`);
            if (selectPrecioVenta) {
                selectPrecioVenta.value = c.precio_venta;
            }
        })
    }

    function limpiarSelectPreciosVenta() {
        $('#precio_venta').empty();
        $('#precio_venta').trigger('change');
    }

    //============= ABRIR MODAL CLIENTE =============
    function openModalCliente() {
        $("#modal_cliente").modal("show");
    }

    //======= PINTAR TABLE PEDIDO ATENCIÓN HISTORIAL =======
    function pintarTablePedidoAtencionHistorial() {
        let filas = ``;
        const tbody = document.querySelector('#table_pedido_atencion_historial tbody');
        const pedido_detalles = @json($pedido_detalles);

        pedido_detalles.forEach((producto) => {
            filas += `<tr>
                            <th>${producto.producto_nombre}</th>
                            <th>${producto.color_nombre}</th>
                            <th>${producto.talla_nombre}</th>
                            <th>${producto.cantidad}</th>
                            <th>${producto.cantidad_atendida}</th>
                            <th>${producto.cantidad_pendiente}</th>
                            <th>${producto.cantidad_enviada}</th>
                            <th>${producto.cantidad_devuelta}</th>
                        </tr>`;
        })

        tbody.innerHTML = filas;
    }

    function actualizarPedido(formPedido) {

        Swal.fire({
            title: 'Desea actualizar el pedido?',
            text: "Se realizarán cambios",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then(async (result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Actualizando Pedido...',
                    text: 'Por favor, espere.',
                    icon: 'info',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const formData = new FormData(formPedido);
                    formData.append('lstPedido', JSON.stringify(carrito));
                    formData.append('sede_id', @json($sede_id));
                    formData.append('registrador_id', @json($registrador->id));
                    formData.append('amountsPedido', JSON.stringify(amountsPedido));

                    /*
                    const delay = new Promise(resolve => setTimeout(resolve, 10000));
                    const request = axios.post(
                        route('pedidos.pedido.update', { id: @json($pedido->id) }),
                        formData,
                        {
                            headers: {
                                "X-HTTP-Method-Override": "PUT"
                            }
                        }
                    );
                    const [res] = await Promise.all([request, delay]);
                    */



                    const res = await axios.post(route('pedidos.pedido.update', {
                            id: @json($pedido->id)
                        }),
                        formData, {
                            headers: {
                                "X-HTTP-Method-Override": "PUT"
                            }
                        });


                    if (res.data.success) {
                        const routeIndex = "{{ route('pedidos.pedido.index') }}";
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = routeIndex;
                    } else {
                        if ('lstErroresValidacion' in res.data) {
                            pintarErroresValidacion(res.data.lstErroresValidacion);
                            return;
                        }
                        Swal.close();
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    if (error.response) {
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            pintarErroresValidacion(errors, 'error');
                            Swal.close();
                            toastr.error("ERRORES DE VALIDACIÓN!!!");
                        } else {
                            Swal.close();
                            toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } else if (error.request) {
                        Swal.close();
                        toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                            'ERROR DE CONEXIÓN');
                    } else {
                        Swal.close();
                        toastr.error(error.message, 'ERROR DESCONOCIDO');
                    }
                }


            } else if (result.dismiss === Swal.DismissReason.cancel) {

                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'La Solicitud se ha cancelado.',
                    'error'
                )

            }
        })
    }

    function validacionAgregarProducto() {
        let validacion = true;
        //====== VALIDACIONES =======
        if (!precioVentaFinal) {
            toastr.error('DEBES INGRESAR UN PRECIO DE VENTA!!!');
            validacion = false;
            return;
        }

        //======= DEBE SELECCIONARSE UN ALMACÉN =======
        const almacenSeleccionado = document.querySelector('#almacen');
        if (!almacenSeleccionado) {
            validacion = false;
            toastr.error('DEBES SELECCIONAR UN ALMACÉN!!!');
            return;
        }

        const inputPrecioVenta = document.querySelector('#precio_venta');
        if (precioVentaFinal > precioVentaOriginal) {
            toastr.error('EL PRECIO DE VENTA DEBE SER MENOR O IGUAL AL ORIGINAL');
            validacion = false;
            inputPrecioVenta.focus();
        }

        if (precioVentaFinal <= 0) {
            toastr.error('EL PRECIO DE VENTA DEBE SER MAYOR A 0');
            validacion = false;
            inputPrecioVenta.focus();
        }

        return validacion;
    }

    function setDatosDefault() {
        $('#metodo_pago_1').val(3).trigger('change');
        window.departamentoSelect.setValue(15);
        window.tipoEnvioSelect.setValue(188);
        window.tipoPagoEnvioSelect.setValue(197);
    }

    function cambiarMetodoPago1(metodoPagoId) {
        const cuentasBD = @json($cuentas);
        let cuentasFiltradas = cuentasBD.filter(c => c.tipo_pago_id == metodoPagoId);
        pintarCuentas(cuentasFiltradas);
    }

    function pintarCuentas(lstCuentas) {
        let selectCuentas = $('#cuenta_1');
        selectCuentas.empty();
        lstCuentas.forEach(cuenta => {
            selectCuentas.append(
                $('<option>', {
                    value: cuenta.cuenta_id,
                    text: cuenta.cuentaLabel
                })
            );
        });
        selectCuentas.trigger('change.select2');
    }

    function accionImgPago1(e) {
        const file = e.target.files[0];
        const label = e.target.nextElementSibling;
        const preview = document.getElementById('previewImage1');
        const defaultImage = "{{ asset('img/default.png') }}";

        if (file) {
            const validTypes = ['image/jpeg', 'image/png'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!validTypes.includes(file.type)) {
                toastr.error('Solo se permiten imágenes JPG, JPEG o PNG.');
                input.value = '';
                label.textContent = 'Imagen';
                preview.src = defaultImage;
                return;
            }

            if (file.size > maxSize) {
                toastr.error('El tamaño máximo permitido es de 2 MB.');
                input.value = '';
                label.textContent = 'Imagen';
                preview.src = defaultImage;
                return;
            }

            label.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            label.textContent = 'Imagen';
            preview.src = defaultImage;
        }
    }

    async function setDatosEnvioPrevio() {

        const despacho = @json($envio_venta);
        if (despacho) {

            document.querySelector('#monto-envio').classList.remove('btn-light');
            document.querySelector('#monto-envio').classList.add('btn-success');

            desactivarEventosSelectsMdlEnvio();
            window.departamentoSelect.setValue(parseInt(despacho.departamento_id));
            const provincias = await getProvincias(despacho.departamento_id);
            pintarProvincias(provincias, parseInt(despacho.provincia_id));
            const distritos = await getDistritos(despacho.provincia_id);
            pintarDistritos(distritos, parseInt(despacho.distrito_id));
            setZona(getZona(parseInt(despacho.departamento_id)));
            window.tipoEnvioSelect.setValue(despacho.tipo_envio_id, false);
            await getEmpresasEnvio();
            window.empresaEnvioSelect.setValue(despacho.empresa_envio_id, false);
            await getSedesEnvio();
            window.sedeEnvioSelect.setValue(despacho.sede_envio_id, false);

            setEntregaDomicilio(despacho.entrega_domicilio);
            setDireccionEntrega(despacho.direccion_entrega);
            // setOrigenVenta(despacho.origen_venta_id);
            setFechaEnvio(despacho.fecha_envio_propuesta);
            setObservaciones(despacho.obs_rotulo, despacho.obs_despacho);
            setDestinatario(despacho.destinatario_tipo_doc, despacho.destinatario_nro_doc, despacho
                .destinatario_nombre);

            activarEventosSelectsMdlEnvio();
        }
    }

    function setEntregaDomicilio(entrega_domicilio) {
        const check = document.querySelector('#check_entrega_domicilio');

        if (entrega_domicilio === 'SI') {
            check.checked = true;
            document.querySelector('#direccion_entrega').readOnly = false;
        } else {
            check.checked = false;
            document.querySelector('#direccion_entrega').readOnly = true;
        }

        check.dispatchEvent(new Event('change'));

    }

    function setDireccionEntrega(direccion_entrega) {
        document.querySelector('#direccion_entrega').value = direccion_entrega;
    }

    // function setOrigenVenta(origen_venta_id) {
    //     if (window.origenVentaSelect) {
    //         window.origenVentaSelect.setValue(origen_venta_id, true);
    //         document.getElementById('origen_venta').dispatchEvent(new Event('change'));
    //     }
    // }

    function setObservaciones(obs_rotulo, obs_despacho) {
        document.querySelector('#obs-rotulo').value = obs_rotulo;
        document.querySelector('#obs-despacho').value = obs_despacho;
    }

    function setFechaEnvio(fecha_envio_propuesta) {
        document.querySelector('#fecha_envio').value = fecha_envio_propuesta;
    }

    function setDestinatario(destTipoDoc, destNroDoc, destNombre) {
        if (window.tipoDocDestinatarioSelect) {
            const items = window.tipoDocDestinatarioSelect.options;
            const match = Object.values(items).find(i => i.text.trim() === destTipoDoc.trim());
            if (match) {
                window.tipoDocDestinatarioSelect.setValue(match.value, true);
                document.getElementById('tipo_doc_destinatario').dispatchEvent(new Event('change'));
            }
        }
        document.querySelector('#nro_doc_destinatario').value = destNroDoc;
        document.querySelector('#nombres_destinatario').value = destNombre;
    }

    function elegirCliente() {
        const cliente_elegido = $('#cliente').select2('data')[0];
        document.querySelector('#telefono').value = '';
        if (cliente_elegido) {
            if (cliente_elegido.id == 1) {
                return;
            }
            document.querySelector('#telefono').value = cliente_elegido.telefono;
        }
    }

    async function accionOpenMdlEnvio() {

        //======= COLCANDO EN MODAL ENVIO EL NOMBRE DEL CLIENTE =======
        let clienteSeleccionado =   $('#cliente').select2('data')[0];
        const hayEnvio          =   document.querySelector('#data_envio').value;
        const despacho          =   @json($envio_venta);
        const estadoDespacho    =   @json($documento->estado_despacho);

        if (!clienteSeleccionado.departamento_id) {
            const option = $('#cliente').find(':selected');
            clienteSeleccionado = {
                ...clienteSeleccionado,
                telefono: option.data('telefono'),
                departamento_id: option.data('departamento-id'),
                provincia_id: option.data('provincia-id'),
                distrito_id: option.data('distrito-id'),
            };
        }

        if (clienteSeleccionado && !hayEnvio && !despacho) {
            desactivarEventosSelectsMdlEnvio();
            window.departamentoSelect.setValue(parseInt(clienteSeleccionado.departamento_id));
            const provincias = await getProvincias(clienteSeleccionado.departamento_id);
            pintarProvincias(provincias, parseInt(clienteSeleccionado.provincia_id));
            const distritos = await getDistritos(clienteSeleccionado.provincia_id);
            pintarDistritos(distritos, parseInt(clienteSeleccionado.distrito_id));
            setZona(getZona(parseInt(clienteSeleccionado.departamento_id)));

            //====== COLOCAR TEXTO DEL SPAN =====
            const cliente_nombre = $("#cliente").find('option:selected').text();
            const nroDocumento = cliente_nombre.split(':')[1].split('-')[0].trim();
            const cliente_nombre_recortado = cliente_nombre.split('-')[1].trim()
            const tipo_documento = cliente_nombre.split(':')[0];

            if (tipo_documento === "DNI" || tipo_documento === "CARNET EXT.") {
                //====== COLOCAR TEXTO DEL SPAN =====
                document.querySelector('.span-tipo-doc-dest').textContent = tipo_documento;
                //====== SELECCIONAR LA OPCIÓN RESPECTIVA EN SELECT TIPO DOC DEST ======
                if (tipo_documento === "DNI") {
                    window.tipoDocDestinatarioSelect.setValue(6);
                    if (nroDocumento.trim() != "99999999") {
                        document.querySelector('#nro_doc_destinatario').value = nroDocumento;
                        document.querySelector('#nro_doc_destinatario').value = nroDocumento;
                        document.querySelector('#nombres_destinatario').value = cliente_nombre_recortado;
                    }
                }
                if (tipo_documento === "CARNET EXT.") {
                    window.tipoDocDestinatarioSelect.setValue(7);
                    document.querySelector('#nro_doc_destinatario').value = nroDocumento;
                    document.querySelector('#nombres_destinatario').value = cliente_nombre_recortado;
                }
            }
            activarEventosSelectsMdlEnvio();
        }

        //========= ABRIR MODAL ENVÍO =======
        $("#modal_envio").modal("show");
    }
</script>
@endpush
