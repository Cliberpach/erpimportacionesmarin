@extends('layout')
@section('content')

    @include('ventas.documentos.editar.modals.mdl_envio')
    @include('ventas.cotizaciones.modal-cliente')
    @include('reutilizables.lightbox.lightbox')

@section('pedidos-active', 'active')
@section('pedido-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>Registrar Nueva Reserva</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Reservas</strong>
            </li>
        </ol>
    </div>
</div>

{{-- 🚨 ALERTA DE INFORMACIÓN --}}
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="alert alert-info alert-dismissible fade show shadow-sm rounded" role="alert">
            <h5 class="mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>IMPORTANTE - RESERVAS</strong>
            </h5>
            <ul class="mb-0 pl-3">
                <li>
                    <i class="fas fa-ticket-alt text-primary mr-1"></i>
                    Generá siempre un <strong>Ticket a condición crédito</strong> con el total.
                </li>
                <li>
                    <i class="fas fa-shipping-fast text-success mr-1"></i>
                    Puedes <strong>agregar datos de envío</strong>.
                </li>
                <li>
                    <i class="fas fa-money-bill-wave text-success mr-1"></i>
                    Se pueden <strong>registrar pagos</strong> asociados a la reserva.
                </li>
                <li>
                    <i class="fas fa-box-open text-warning mr-1"></i>
                    Los <strong>productos sin stock</strong> serán guardados con estado
                    <span class="badge badge-warning">EN ESPERA</span>
                    y podrán agregarse luego a una <strong>orden de producción manual</strong>.
                </li>
                <li>
                    <i class="fas fa-warehouse text-info mr-1"></i>
                    Los <strong>productos con stock suficiente</strong> tendrán su stock
                    <span class="badge badge-info">SEPARADO</span> automáticamente.
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

                            @include('pedidos.pedido.forms.form_pedido_create')

                        </div>
                    </div>
                    <hr>

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

                                    <button type="submit" id="btn_grabar" form="form-pedido"
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

    let pedidos_data_table = null;
    let carrito = [];
    let dataTableStocksPedido = null;

    document.addEventListener('DOMContentLoaded', async () => {
        mostrarAnimacion();
        loadSelect2();
        events();
        iniciarSelectsMdlEnvio();
        setDatosDefault();
        setDataCotizacion();
        ocultarAnimacion();
    })

    function events() {

        eventsModalEnvio();
        eventsCliente();

        document.getElementById('img_pago_1').addEventListener('change', (e) => {
            accionImgPago1(e);
        });

        //====== ELIMINAR ITEM =========
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-product')) {
                mostrarAnimacion();
                const productoId = e.target.getAttribute('data-producto');
                const colorId = e.target.getAttribute('data-color');
                eliminarProducto(productoId, colorId);
                pintarDetallePedido(carrito);
                clearInputsCantidad();
                loadCarrito();
                calcularMontos();
                ocultarAnimacion();
            }
        })

        //====== SUBMIT FORM PEDIDOS =======
        document.querySelector('#form-pedido').addEventListener('submit', (e) => {

            e.preventDefault();
            registrarPedido(e.target);

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
                // Reemplazar todo excepto los dígitos y el punto decimal
                e.target.value = e.target.value.replace(/[^\d.]/g, '');

                // Reemplazar múltiples puntos decimales con uno solo
                e.target.value = e.target.value.replace(/(\..*)\./g, '$1');

                // Si el valor empieza con ".", agregar un "0" delante
                e.target.value = e.target.value.replace(/^(\.)/, '0$1');

                // Eliminar ceros a la izquierda (excepto si hay un punto después, ej: 0.50)
                e.target.value = e.target.value.replace(/^0+(?=\d)/, '');

                // Limitar a 2 decimales como máximo
                e.target.value = e.target.value.replace(/^(\d+)(\.\d{0,2})?.*$/, (_, intPart, decPart) => {
                    return intPart + (decPart || '');
                });

                calcularMontos();
            }
        })

        //======== AGREGAR PRODUCTO AL DETALLE =====
        document.querySelector('#btn_agregar_detalle').addEventListener('click', () => {

            mostrarAnimacion();
            toastr.clear();

            const validacion = validacionAgregarProducto();
            if (!validacion) {
                ocultarAnimacion();
                return;
            };

            agregarProducto();
            reordenarCarrito();
            calcularSubTotal();
            pintarDetallePedido(carrito);
            //===== RECALCULANDO DESCUENTOS Y MONTOS =====
            carrito.forEach((c) => {
                calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
            })
            //===== RECALCULANDO MONTOS =====
            calcularMontos();

            toastr.info('PRODUCTO AGREGADO');
            ocultarAnimacion();

        })

        //=========== MODAL DESPACHO =========
        document.querySelector('.btn-envio').addEventListener('click', () => {
            accionOpenMdlEnvio();
        })

    }


    //====== SELECT2 =======
    function loadSelect2() {

        $(".select2_modal_cliente").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            width: '100%'
        });


        $(".select2_form").select2({
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
                                telefono: item.telefono_movil,
                                departamento_id: item.departamento_id,
                                provincia_id: item.provincia_id,
                                distrito_id: item.distrito_id
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
                    }).attr('data-telefono', data.telefono || '').attr('data-departamento-id', data
                        .departamento_id || '')
                    .attr('data-provincia-id', data.provincia_id || '').attr('data-distrito-id', data
                        .distrito_id || '');


                return $option;
            },
        });
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

    function setDatosDefault() {
        $('#metodo_pago_1').val(3).trigger('change');
        window.departamentoSelect.setValue(15);
        window.tipoEnvioSelect.setValue(188);
        window.tipoPagoEnvioSelect.setValue(197);
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

    //========= SWAL ======
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger',
        },
        buttonsStyling: false
    })

    //======== ELIMINAR ITEM ========
    const eliminarProducto = (productoId, colorId) => {
        carrito = carrito.filter((p) => {
            return !(p.producto_id == productoId && p.color_id == colorId);
        })
    }


    //===== LIMPIAR INPUTS DEL TABLERO PRODUCTOS ======
    function clearInputsCantidad() {
        const inputsCantidad = document.querySelectorAll('.inputCantidad');
        inputsCantidad.forEach((inputCantidad) => {
            inputCantidad.value = '';
        })
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

    //======= CALCULAR MONTOS =======
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
            if (c.porcentaje_descuento === 0) {
                subtotal += parseFloat(c.subtotal);
            } else {
                subtotal += parseFloat(c.subtotal_nuevo);
            }
            descuento += parseFloat(c.monto_descuento);
        })

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
        document.querySelector('#monto_1').value = amountsPedido.totalPagar;
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

    //=========== FORMAR PRODUCTO =========
    const formarProducto = (ic) => {
        const producto_id = ic.getAttribute('data-producto-id');
        const modelo_nombre = $('#modelo').find('option:selected').text();
        const producto_nombre = ic.getAttribute('data-producto-nombre');
        const producto_codigo = ic.getAttribute('data-producto-codigo');
        const color_id = ic.getAttribute('data-color-id');
        const color_nombre = ic.getAttribute('data-color-nombre');
        const talla_id = ic.getAttribute('data-talla-id');
        const talla_nombre = ic.getAttribute('data-talla-nombre');
        const precio_venta = parseFloat(precioVentaOriginal);
        const cantidad = ic.value ? ic.value : 0;

        const monto_descuento = 0;
        const porcentaje_descuento = ((precioVentaOriginal - precioVentaFinal) / precioVentaOriginal) * 100;
        const precio_venta_nuevo = parseFloat(precioVentaFinal);
        const subtotal_nuevo = 0.0;
        const subtotal = 0;

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
    function calcularSubTotal() {
        carrito.forEach((p) => {
            let cantidadTallas = 0;
            p.tallas.forEach((t) => {
                cantidadTallas += parseFloat(t.cantidad);
            })
            p.subtotal = cantidadTallas * parseFloat(p.precio_venta);
            p.subtotal_nuevo = cantidadTallas * parseFloat(p.precio_venta_nuevo);
        })
    }

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


    //========== OBTENER PRODUCTOS POR MODELO =========
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

    function destruirDataTableStocks() {
        if (dataTableStocksPedido) {
            dataTableStocksPedido.destroy();
            dataTableStocksPedido = null;
        }
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
                                data-color-id="${color.id}" data-talla-id="${talla.id}"
                                data-producto-codigo="${producto.codigo}"></input>
                            </td>`;
            })

            filas += `</tr>`;

        })

        tableStocksBody.innerHTML = filas;
        btnAgregarDetalle.disabled = false;

    }


    function limpiarTableStocks() {
        const tableStocksBody = document.querySelector('#table-stocks-pedidos tbody');

        while (tableStocksBody.firstChild) {
            tableStocksBody.removeChild(tableStocksBody.firstChild);
        }
    }

    //======= PINTAR PRECIOS VENTA =======
    function pintarPreciosVenta(producto_color_tallas) {

        if (producto_color_tallas) {
            if (producto_color_tallas.precio_venta_1 != null) {
                document.querySelector('#precio_venta').value = producto_color_tallas.precio_venta_1;
                precioVentaOriginal = producto_color_tallas.precio_venta_1;
                precioVentaFinal = producto_color_tallas.precio_venta_1;
            }

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

    function mostrarAnimacion() {
        document.querySelector('.overlay_pedido_create').style.visibility = 'visible';
    }

    function ocultarAnimacion() {
        document.querySelector('.overlay_pedido_create').style.visibility = 'hidden';
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
            toastr.info('NO SE ENCONTRÓ PRECIO DE VENTA PREVIO PARA EL PRODUCTO');
        }

    }

    //======== LIMPIAR TABLA PRODUCTOS ========
    function clearTabla(bodyTable) {
        while (bodyTable.firstChild) {
            bodyTable.removeChild(bodyTable.firstChild);
        }
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

    //============= ABRIR MODAL CLIENTE =============
    function openModalCliente() {
        $("#modal_cliente").modal("show");
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

    function registrarPedido(formPedido) {

        toastr.clear();
        if (carrito.length === 0) {
            toastr.error('EL DETALLE DEL PEDIDO ESTÁ VACÍO', 'ERROR');
            return;
        }

        Swal.fire({
            title: 'Opción Guardar',
            text: "¿Seguro que desea guardar cambios?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then(async (result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: "Registrando pedido...",
                    text: "Por favor, espere",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
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

                    const cotizacion    =   @json($cotizacion??null);
                    if(cotizacion){
                        formData.append('cotizacion_id',cotizacion.id);
                    }

                    const res = await axios.post(route('pedidos.pedido.store'), formData);
                    /*
                    const delay     =   new Promise(resolve => setTimeout(resolve, 10000));
                    const request   =   axios.post(route('pedidos.pedido.store'), formData);
                    const [res]     =   await Promise.all([request, delay]);
                    */

                    if (res.data.success) {
                        const documento_id = res.data.documento_id;

                        let url_open_pdf = '{{ route('ventas.documento.comprobante', [':id1', ':size']) }}'
                            .replace(':id1', documento_id)
                            .replace(':size', 80);

                        window.open(url_open_pdf, 'Comprobante SISCOM',
                            'location=1, status=1, scrollbars=1,width=900, height=600');
                        window.location.href = '{{ route('ventas.documento.index') }}';

                        window.location = route('pedidos.pedido.index');
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                    } else {
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
                            toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } else if (error.request) {
                        toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                            'ERROR DE CONEXIÓN');
                    } else {
                        toastr.error(error.message, 'ERROR DESCONOCIDO');
                    }
                } finally {
                    Swal.close();
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

    function cambiarAlmacen(almacen_id) {

        toastr.clear();

        mostrarAnimacion();

        //======== LIMPIAR SELECTS ======
        $('#producto').val(null).trigger('change');

        //======= LIMPIAR TABLERO STOCKS ======
        destruirDataTable(dataTableStocksPedido);
        limpiarTabla('table-stocks-pedidos');
        loadDataTableStocksPedido();


        //========== LIMPIAR DETALLE DEL PEDIDO ========
        carrito.length = 0;
        destruirDataTableStocks();
        limpiarTabla('table-detalle-pedido');
        pintarDetallePedido(carrito);

        carrito.forEach((c) => {
            calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
        })
        calcularMontos();

        ocultarAnimacion();
        toastr.info('SE HA LIMPIADO EL FORMULARIO');

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

    async function accionOpenMdlEnvio() {

        //======= COLCANDO EN MODAL ENVIO EL NOMBRE DEL CLIENTE =======
        let clienteSeleccionado = $('#cliente').select2('data')[0];
        const hayEnvio = document.querySelector('#data_envio').value;

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

        if (clienteSeleccionado && !hayEnvio) {
            desactivarEventosSelectsMdlEnvio();
            window.departamentoSelect.setValue(parseInt(clienteSeleccionado.departamento_id));
            const provincias = await getProvincias(clienteSeleccionado.departamento_id);
            pintarProvincias(provincias, clienteSeleccionado.provincia_id);
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

    function setDataCotizacion() {
        const cotizacion = @json($cotizacion ?? null);

        if (cotizacion) {
            carrito = @json($detalle??[]);
            reordenarCarrito();
            calcularSubTotal();
            pintarDetallePedido(carrito);

            //===== RECALCULANDO MONTOS =====
            calcularMontos();
            toastr.info('PRODUCTOS AGREGADOS');

            const envioInput = document.querySelector('.envio');
            const embalajeInput = document.querySelector('.embalaje');

            if (envioInput) {
                envioInput.value = cotizacion.monto_envio ?? '';
                envioInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }

            if (embalajeInput) {
                embalajeInput.value = cotizacion.monto_embalaje ?? '';
                embalajeInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
            ocultarAnimacion();
        }
    }
</script>
@endpush
