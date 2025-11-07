@extends('layout')
@section('content')

    @include('ventas.documentos.editar.modals.modal_cliente')
    @include('ventas.documentos.editar.modals.mdl_envio')

@section('ventas-active', 'active')
@section('documento-active', 'active')

<style>
    .inputCantidadValido {
        border-color: rgb(59, 63, 255) !important;
    }

    .inputCantidadIncorrecto {
        border-color: red !important;
    }

    .inputCantidadColor {
        border-color: rgb(48, 48, 88);
    }

    .colorStockLogico {
        background-color: rgb(243, 248, 255);
    }
</style>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2 style="text-transform:uppercase"><b>EDITAR DOCUMENTO DE VENTA</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('consultas.ventas.documento.no.index') }}">Documentos de venta no enviados</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Editar</strong>
            </li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-12">
        <div class="alert alert-info alert-dismissible fade show shadow-sm rounded mb-0" role="alert">
            <h5 class="mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>IMPORTANTE</strong>
            </h5>

            <div class="row">
                {{-- =====================
                     COLUMNA 1: ESTADO DE PAGO
                ====================== --}}
                <div class="col-md-4 mb-3">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-money-bill-wave mr-2 text-success"></i> Estado de Pago
                    </h6>
                    <ul class="pl-3 mb-0">
                        @if ($documento->estado_pago === 'PENDIENTE')
                            <li>
                                <i class="fas fa-box-open text-warning mr-1"></i>
                                Aún puedes <strong>modificar el detalle de la venta</strong>.
                            </li>
                            <li>
                                <i class="fas fa-money-bill-wave text-success mr-1"></i>
                                Aún puedes <strong>registrar pagos</strong>.
                            </li>
                            <li>
                                <span class="badge badge-warning">{{ $documento->estado_pago }}</span>
                            </li>
                        @elseif ($documento->estado_pago === 'PAGADA')
                            <li>
                                <i class="fas fa-user text-primary mr-1"></i>
                                Solo puedes modificar los datos de <strong>cliente</strong>, <strong>teléfono</strong>,
                                <strong>observación</strong> y <strong>origen</strong>.
                            </li>
                            <li>
                                <span class="badge badge-success">{{ $documento->estado_pago }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- =====================
                     COLUMNA 2: ESTADO DE DESPACHO
                ====================== --}}
                <div class="col-md-4 mb-3">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-shipping-fast mr-2 text-info"></i> Estado de Despacho
                    </h6>
                    <ul class="pl-3 mb-0">
                        @if ($documento->estado_despacho === 'PENDIENTE')
                            <li>
                                <i class="fas fa-edit text-success mr-1"></i>
                                Puedes <strong>modificar los datos de envío</strong>.
                            </li>
                            <li>
                                <span class="badge badge-info">{{ $documento->estado_despacho }}</span>
                            </li>
                        @elseif ($documento->estado_despacho === 'S/D')
                            <li>
                                <i class="fas fa-plus-circle text-primary mr-1"></i>
                                Puedes <strong>registrar datos de envío</strong>.
                            </li>
                            <li>
                                <span class="badge badge-secondary">{{ $documento->estado_despacho }}</span>
                            </li>
                        @else
                            <li>
                                <i class="fas fa-truck text-danger mr-1"></i>
                                Los datos de envío <strong>ya no pueden modificarse</strong>.
                            </li>
                            <li>
                                <span class="badge badge-danger">{{ $documento->estado_despacho }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- =====================
                     COLUMNA 3: ESTADO DE TRASLADO
                ====================== --}}
                @if ($traslado)
                    <div class="col-md-4 mb-3">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fas fa-truck mr-2 text-danger"></i> Estado de Traslado
                        </h6>
                        <ul class="pl-3 mb-0">
                            <li>
                                <i class="fas fa-barcode text-dark mr-1"></i>
                                Traslado asociado <strong>TR-{{ $traslado->id }}</strong>
                            </li>
                            <li>
                                <span class="badge
                                    @if($traslado->estado == 'PENDIENTE') badge-warning
                                    @elseif($traslado->estado == 'ENVIADO') badge-info
                                    @elseif($traslado->estado == 'RECIBIDO') badge-primary
                                    @elseif($traslado->estado == 'ENTREGADO AL CLIENTE') badge-success
                                    @else badge-danger @endif">
                                    {{ $traslado->estado }}
                                </span>
                            </li>

                            {{-- Reglas combinadas de edición --}}
                            @if ($traslado->estado === 'PENDIENTE' && $documento->estado_pago === 'PENDIENTE')
                                <li class="mt-2">
                                    <i class="fas fa-edit text-success mr-1"></i>
                                    Puedes <strong>editar el detalle de la venta</strong>.
                                </li>
                            @else
                                <li class="mt-2">
                                    <i class="fas fa-lock text-muted mr-1"></i>
                                    No puedes editar el detalle. Solo <strong>cliente, teléfono, observación y origen</strong>.
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

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
                    <input type="hidden" id='asegurarCierre'>

                    @include('ventas.documentos.editar.forms.form_edit')

                    <hr>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">

                        <div class="col-md-6 text-left" style="color:#fcbc6c">
                            <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                                (<label class="required"></label>) son obligatorios.</small>
                        </div>

                        <div class="col-md-6 text-right">
                            <a onclick="regresarClick(event)" href="javascript:void(0)" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
                            <button type="submit" form="formActualizarVenta" class="btn btn-w-m btn-success">
                                <i class="fa fa-save"></i> Grabar
                            </button>
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
    .my-swal {
        z-index: 3000 !important;
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

    const tableDetalleBody = document.querySelector('#table-detalle tbody');
    const tableStocksBody = document.querySelector('#table-stocks tbody');
    const detalles = @json($detalles);
    const tallasBD = @json($tallas);
    const documento = @json($documento);

    const tfootSubtotal = document.querySelector('.subtotal');
    const tfootEmbalaje = document.querySelector('.embalaje');
    const tfootEnvio = document.querySelector('.envio');
    const tfootTotal = document.querySelector('.total');
    const tfootIgv = document.querySelector('.igv');
    const tfootTotalPagar = document.querySelector('.total-pagar');
    const tfootDescuento = document.querySelector('.descuento');


    const amounts = {
        subtotal: 0,
        embalaje: 0,
        envio: 0,
        total: 0,
        igv: 0,
        totalPagar: 0,
        monto_descuento: 0
    }

    let dtDetalleVenta = null;
    let dtStocksVenta = null;

    let carrito = [];
    let modelo_id;
    let asegurarCierre = 5;

    document.addEventListener('DOMContentLoaded', async () => {

        dtStocksVenta = iniciarDataTable('table-stocks');
        dtDetalleVenta = iniciarDataTable('table-detalle');

        events();
        iniciarSelectsMdlEnvio();
        loadSelect2();
        cargarProductosPrevios(); //======== FORMATEAR DETALLE ==============
        setDatosEnvioPrevio();
        setConfiguracionDefault();

        asegurarCierre = 1;

    })

    function events() {

        eventsCliente();
        eventsModalEnvio();

        if (documento.estado_pago === 'PENDIENTE') {
            document.getElementById('img_pago_1').addEventListener('change', (e) => {
                accionImgPago1(e);
            });
        }

        if (documento.estado_pago === 'PENDIENTE') {
            document.querySelector('#btn_agregar_detalle').addEventListener('click', async () => {

                //======== ANIMACIÓN ======
                mostrarAnimacion();
                //======= LIMPIAR ALERTAS PREVIAS ======
                toastr.clear();

                const validacion = validacionAgregarProducto();
                if (!validacion) {
                    ocultarAnimacion();
                    return;
                };

                //========= AGREGAR PRODUCTO ========
                agregarProducto();

                //======== REORDENAR CARRITO =======
                reordenarCarrito();

                //========= CALCULAR SUBTOTAL =====
                calcularSubTotal();

                //========= DESTRUIR DATATABLE DETALLE VENTA ======
                destruirDataTable(dtDetalleVenta);

                //======== LIMPIAR TABLA DETALLE VENTA ======
                limpiarTabla('table-detalle');

                //========= PINTAR TABLA DETALLE VENTA =======
                pintarDetalle();

                //===== RECALCULANDO DESCUENTOS, ESTO EDITA LA TABLA DETALLE VENTA TMB =====
                carrito.forEach((c) => {
                    calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
                })

                //======= INICIAR DATATABLE DETALLE VENTA =======
                dtDetalleVenta = iniciarDataTable('table-detalle');

                //======= CALCULAR MONTOS ======
                calcularMontos();

                toastr.info('PRODUCTO AGREGADO');
                ocultarAnimacion();

            })
        }


        //===== VALIDAR CONTENIDO DE INPUTS CANTIDAD ========
        //===== VALIDAR TFOOTS EMBALAJE Y ENVIO ======
        document.addEventListener('input', (e) => {

            if (e.target.classList.contains('inputCantidad')) {
                e.target.value = e.target.value.replace(/^0+|[^0-9]/g, '');
            }

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

            if (e.target.classList.contains('detailDescuento')) {
                //==== CONTROLANDO DE QUE EL VALOR SEA UN NÚMERO ====
                const valor = event.target.value;
                const producto_id = e.target.getAttribute('data-producto-id');
                const color_id = e.target.getAttribute('data-color-id');

                //==== SI EL INPUT ESTA VACÍO ====
                if (valor.trim().length === 0) {
                    //===== CALCULAR DESCUENTO Y PINTARLO ======
                    calcularDescuento(producto_id, color_id, 0);
                    //===== CALCULAR Y PINTAR MONTOS =======
                    calcularMontos();
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

                //==== CALCULAR DESCUENTO Y PINTARLO ====
                calcularDescuento(producto_id, color_id, porcentaje_desc)
                //===== CALCULAR Y PINTAR MONTOS =======
                calcularMontos();
            }

        })

        //===== ELIMINAR PRODUCTO-COLOR DEL CARRITO =========
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-product')) {
                console.log(e.target);
                eliminarProductoColor(e.target);
            }
        })

        //======= GRABAR =======
        document.querySelector('#formActualizarVenta').addEventListener('submit', (e) => {
            e.preventDefault();

            toastr.clear();
            if (carrito.length === 0) {
                toastr.error('EL DETALLE DE VENTA ESTÁ VACÍO!!!');
                return;
            }

            actualizarVenta(e.target);

        })

        //=========== MODAL DESPACHO =========
        document.querySelector('.btn-envio').addEventListener('click', () => {
            accionOpenMdlEnvio();
        })

    }

    //====== CARGAR SELECT2 =======
    function loadSelect2() {

        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            width: '100%',
        });

        $(".select2_modal_cliente").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            width: '100%'
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
                url: '{{ route('utilidades.getProductosTodos') }}',
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

    }

    function cambiarCondicion(condicion_id) {

        if (condicion_id) {
            const condiciones = @json($condiciones);
            const condicion_filtrada = condiciones.find((c) => c.id == condicion_id);
            const dias = condicion_filtrada.dias;

            const fecha_registro = @json($documento->fecha_documento);

            if (fecha_registro) {

                const fecha = new Date(fecha_registro);

                if (isNaN(fecha.getTime())) {
                    console.error("Fecha de vencimiento inválida");
                    return;
                }

                fecha.setDate(fecha.getDate() + dias);

                const nueva_fecha_vencimiento = fecha.toISOString().split('T')[0];

                document.querySelector('#fecha_vencimiento').value = nueva_fecha_vencimiento;
            }
        }
    }






    function regresarClick(event) {
        event.preventDefault();
        if (!event.target.classList.contains("disabled")) {
            event.target.classList.add("disabled");
            window.location.href = '{{ route('ventas.documento.index') }}';
        }
    }

    //===== ELIMINAR PRODUCTO COLOR ====
    function eliminarProductoColor(pc) {

        //========== obteniendo producto_id color_id ======
        const producto_id = pc.getAttribute('data-producto');
        const color_id = pc.getAttribute('data-color');


        //===== OBTENIENDO ITEM DEL CARRITO ========
        const item = carrito.filter((c) => {
            return c.producto_id == producto_id && c.color_id == color_id;
        })


        //=== FORMANDO OBJETO ====
        const producto = {
            producto_id: producto_id,
            color_id: color_id,
            tallas: item[0].tallas
        }

        //===== ELIMINANDO DEL CARRITO ===
        carrito = carrito.filter((c) => {
            return !(c.producto_id == producto_id && c.color_id == color_id);
        })

        //this.actualizarStockLogico(producto,'eliminar')

        destruirDataTable(dtDetalleVenta);
        limpiarTabla('table-detalle')
        pintarDetalle();
        dtDetalleVenta = iniciarDataTable('table-detalle');
        calcularMontos();

        toastr.success(`${item[0].producto_nombre} - ${item[0].color_nombre}`, 'ELIMINADO DEL DETALLE');

    }

    //========= VALIDAR TIPO DOC =======
    function validarTipo() {
        var enviar = false

        if ($('#tipo_cliente_documento').val() == '0' && $('#tipo_venta').val() == 'FACTURA') {
            toastr.error('El tipo de documento del cliente es diferente a RUC.', 'Error');
            enviar = true;
        }
        return enviar
    }

    //=================== PINTAR MONTOS ==============
    const pintarMontos = () => {
        tfootSubtotal.textContent = cotizacion.sub_total;
        tfootEmbalaje.value = cotizacion.monto_embalaje;
        tfootEnvio.value = cotizacion.monto_envio;
        tfootTotal.textContent = cotizacion.total;
        tfootIgv.textContent = cotizacion.total_igv;
        tfootTotalPagar.textContent = cotizacion.total_pagar;
        document.querySelector('#monto_1').value = cotizacion.total_pagar;
    }

    function actualizarVenta(formActualizarVenta) {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea actualizar el documento de venta?",
            text: "Se realizarán cambios",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, actualizar!",
            cancelButtonText: "No!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                try {

                    limpiarErroresValidacion('msgError');

                    Swal.fire({
                        title: 'Actualizando documento de venta...',
                        text: 'Por favor, espera',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const documento_id = @json($documento->id);
                    const formData = new FormData(formActualizarVenta);
                    formData.append('lstVenta', JSON.stringify(carrito));
                    formData.append('amounts', JSON.stringify(amounts));
                    formData.append('tipo_venta', @json($documento->tipo_venta_id));
                    formData.append('condicion_id', @json($documento->condicion_id));

                    const res = await axios.post(route('ventas.documento.update', {
                        id: documento_id
                    }), formData, {
                        headers: {
                            "X-HTTP-Method-Override": "PUT"
                        }
                    });

                    if (res.data.success) {

                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');

                        let url_open_pdf = '{{ route('ventas.documento.comprobante', [':id', ':size']) }}'
                            .replace(':id', res.data.documento_id)
                            .replace(':size', 80);

                        window.open(url_open_pdf, 'Comprobante SISCOM',
                            'location=1, status=1, scrollbars=1,width=900, height=600');
                        location = "{{ route('ventas.documento.index') }}";

                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    if (error.response) {
                        if (error.response.status === 422) {
                            Swal.close();
                            const errors = error.response.data.errors;
                            pintarErroresValidacion(errors, 'error');
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
                        toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR VENTA');
                        Swal.close();
                    }
                }

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }


    //=========== ENVIAR VENTA ===========
    function enviarVenta() {
        axios.get("{{ route('Caja.movimiento.verificarestado') }}").then((value) => {
            let data = value.data;
            if (!data.success) {
                toastr.error(data.mensaje);
            } else {
                let envio_ok = true;

                var tipo = validarTipo();

                if (tipo == false) {
                    cargarProductos();
                    //CARGAR DATOS TOTAL
                    // $('#monto_sub_total').val($('.subtotal').text())
                    // $('#monto_total_igv').val($('.igv').text())
                    // $('#monto_total').val($('.total').text())

                    document.getElementById("moneda").disabled = false;
                    document.getElementById("observacion").disabled = false;
                    document.getElementById("fecha_documento_campo").disabled = false;
                    document.getElementById("fecha_atencion_campo").disabled = false;
                    document.getElementById("empresa_id").disabled = false;
                    document.getElementById("cliente_id").disabled = false;
                    document.getElementById("condicion_id").disabled = false;
                    //HABILITAR EL CARGAR PAGINA
                } else {
                    envio_ok = false;
                }

                if (envio_ok) {
                    let formDocumento = document.getElementById('enviar_documento');
                    let formData = new FormData(formDocumento);

                    var object = {};
                    formData.forEach(function(value, key) {
                        object[key] = value;
                    });

                    //var json = JSON.stringify(object);

                    var datos = object;
                    var init = {
                        // el método de envío de la información será POST
                        method: "POST",
                        headers: { // cabeceras HTTP
                            // vamos a enviar los datos en formato JSON
                            'Content-Type': 'application/json'
                        },
                        // el cuerpo de la petición es una cadena de texto
                        // con los datos en formato JSON
                        body: JSON.stringify(datos) // convertimos el objeto a texto
                    };

                    var url = '{{ route('consultas.ventas.documento.no.update', ':id') }}';
                    url = url.replace(":id", "{{ $documento->id }}")
                    var textAlert = "¿Seguro que desea guardar cambios?";
                    Swal.fire({
                        title: 'Opción Guardar',
                        text: textAlert,
                        icon: 'question',
                        customClass: {
                            container: 'my-swal'
                        },
                        showCancelButton: true,
                        confirmButtonColor: "#1ab394",
                        confirmButtonText: 'Si, Confirmar',
                        cancelButtonText: "No, Cancelar",
                        showLoaderOnConfirm: true,
                        allowOutsideClick: false,
                        preConfirm: (login) => {
                            return fetch(url, init)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(response.statusText)
                                    }
                                    return response.json()
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(
                                        `Ocurrió un error`
                                    );
                                })
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.value !== undefined && result.isConfirmed) {
                            if (result.value.errors) {
                                let mensaje = sHtmlErrores(result.value.data.mensajes);
                                toastr.error(mensaje);

                                asegurarCierre = 1;
                                document.getElementById("moneda").disabled = true;
                                document.getElementById("observacion").disabled = true;
                                document.getElementById("fecha_documento_campo").disabled = true;
                                document.getElementById("fecha_atencion_campo").disabled = true;
                                document.getElementById("empresa_id").disabled = true;
                                document.getElementById("cliente_id").disabled = true;
                                document.getElementById("condicion_id").disabled = true;
                            } else if (result.value.success) {
                                toastr.success('¡Documento de venta modificado!', 'Exito')
                                console.log(result);
                                asegurarCierre = 5;

                                location = "{{ route('ventas.documento.index') }}";
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: '¡' + result.value.mensaje + '!',
                                    customClass: {
                                        container: 'my-swal'
                                    },
                                    showConfirmButton: false,
                                    timer: 2500
                                });
                                asegurarCierre = 1;
                                $('#asegurarCierre').val(1);
                                document.getElementById("moneda").disabled = true;
                                document.getElementById("observacion").disabled = true;
                                document.getElementById("fecha_documento_campo").disabled = true;
                                document.getElementById("fecha_atencion_campo").disabled = true;
                                document.getElementById("empresa_id").disabled = true;
                                document.getElementById("cliente_id").disabled = true;
                                document.getElementById("condicion_id").disabled = true;
                            }
                        }
                    });

                }
            }
        })
    }

    //======== CARGAR PRODUCTOS ======
    function cargarProductos() {
        $('#productos_tabla').val(JSON.stringify(carrito));
    }


    //=========== AGREGAR PRODUCTOS AL CARRITO =============
    async function agregarProducto() {

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
                        subtotal_nuevo: producto.subtotal_nuevo,
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
                    productoModificar.monto_descuento = producto.monto_descuento;
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

    //====== REORDENAR CARRITO =======
    const reordenarCarrito = () => {
        carrito.sort(function(a, b) {
            if (a.producto_id === b.producto_id) {
                return a.color_id - b.color_id;
            } else {
                return a.producto_id - b.producto_id;
            }
        });
    }

    //============ VALIDAR CANTIDAD CON STOCK LOGICO =======
    async function validarCantidadCarrito(inputCantidad) {
        const stockLogico = await this.getStockLogico(inputCantidad);
        const cantidadSolicitada = inputCantidad.value;
        return stockLogico >= cantidadSolicitada;
    }

    //====== OBTENER STOCK LOGICO ACTUALIZADO DEL PRODUCTO COLOR TALLA =====
    async function getStockLogico(inputCantidad) {
        const producto_id = inputCantidad.getAttribute('data-producto-id');
        const color_id = inputCantidad.getAttribute('data-color-id');
        const talla_id = inputCantidad.getAttribute('data-talla-id');

        try {
            const url = `/get-stocklogico/${producto_id}/${color_id}/${talla_id}`;
            const response = await axios.get(url);
            if (response.data.message == 'success') {
                const stock_logico = response.data.data[0].stock_logico;
                return stock_logico;
            }

        } catch (error) {
            toastr.error(`El producto no cuenta con registros en esa talla`, "Error");
            event.target.value = '';
            console.error('Error al obtener stock logico:', error);
            return null;
        }
    }

    //============== formar objeto producto ================
    function formarProducto(ic) {

        const producto_id = ic.getAttribute('data-producto-id');
        const producto_nombre = ic.getAttribute('data-producto-nombre');
        const color_id = ic.getAttribute('data-color-id');
        const color_nombre = ic.getAttribute('data-color-nombre');
        const talla_id = ic.getAttribute('data-talla-id');
        const talla_nombre = ic.getAttribute('data-talla-nombre');
        const precio_venta = precioVentaOriginal;
        const cantidad = parseFloat(ic.value ? ic.value : 0);

        const monto_descuento = parseFloat(precioVentaOriginal - precioVentaFinal);
        const porcentaje_descuento = (monto_descuento / precioVentaOriginal) * 100;
        const precio_venta_nuevo = parseFloat(precioVentaFinal);
        const subtotal_nuevo = 0.0;

        const producto = {
            producto_id,
            producto_nombre,
            color_id,
            color_nombre,
            talla_id,
            talla_nombre,
            cantidad,
            precio_venta,
            monto_descuento,
            porcentaje_descuento,
            precio_venta_nuevo,
            subtotal_nuevo
        };
        return producto;
    }

    //============= ACTUALIZAR STOCK LOGICO ==============
    async function actualizarStockLogico(producto, modo, cantidadAnterior) {
        //modo=="eliminar"?asegurarCierre=0:asegurarCierre=1;
        //carrito.length>0?asegurarCierre=1:0;
        try {
            const res = await this.axios.post(route('consultas.ventas.documento.no.cantidad'), {
                'producto_id': producto.producto_id,
                'color_id': producto.color_id,
                'talla_id': producto.talla_id,
                'cantidad': producto.cantidad,
                'condicion': asegurarCierre,
                'modo': modo,
                'cantidadAnterior': cantidadAnterior,
                'tallas': producto.tallas,
            });

            console.log(res)

        } catch (ex) {

        }
    }

    //======= OBTENER COLORES Y TALLAS POR PRODUCTO =======
    async function getColoresTallas() {
        mostrarAnimacion();
        const producto_id = $('#producto').val();
        const almacen_id = $('#almacen').val();

        if (producto_id && almacen_id) {
            try {
                const res = await axios.get(route('utilidades.getColoresTalla', {
                    almacen_id,
                    producto_id
                }));
                if (res.data.success) {
                    destruirDataTable(dtStocksVenta);
                    pintarTableStocks(res.data.producto_color_tallas);
                    dtStocksVenta = iniciarDataTable('table-stocks');
                    pintarPreciosVenta(res.data.producto_color_tallas);
                } else {
                    toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER COLORES Y TALLAS');
            } finally {
                ocultarAnimacion();
            }
        } else {
            destruirDataTable(dtStocksVenta);
            limpiarTabla('table-stocks');
            dtStocksVenta = iniciarDataTable('table-stocks');
            ocultarAnimacion();
        }
    }


    //======= CARGAR STOCKS LOGICOS DE PRODUCTOS POR MODELO =======
    async function getProductosByModelo(idModelo) {
        mostrarAnimacion();
        modelo_id = idModelo;
        document.querySelector('#btn_agregar_detalle').disabled = true;

        if (modelo_id) {
            try {
                const url = `/get-producto-by-modelo/${modelo_id}`;
                const response = await axios.get(url);
                console.log(response.data);
                pintarTableStocks(response.data.stocks, tallasBD, response.data.producto_colores);
                loadCantPrevias();
            } catch (error) {
                console.error('Error al obtener productos por modelo:', error);
            } finally {
                ocultarAnimacion();
            }
        } else {
            tableStocksBody.innerHTML = ``;
            ocultarAnimacion();
        }
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

        if (documento.estado_pago === 'PENDIENTE') {
            document.querySelector('#monto_1').value = total_pagar;
        }

        amounts.totalPagar = total_pagar.toFixed(2);
        amounts.igv = igv.toFixed(2);
        amounts.total = total.toFixed(2);
        amounts.embalaje = embalaje.toFixed(2);
        amounts.envio = envio.toFixed(2);
        amounts.subtotal = subtotal.toFixed(2);
        amounts.monto_descuento = descuento.toFixed(2);
    }

    const cargarProductosPrevios = () => {
        //====== CARGANDO CARRITO ======
        const producto_color_procesados = [];

        detalles.forEach((productoPrevio) => {
            const id = `${productoPrevio.producto_id}-${productoPrevio.color_id}`;

            if (!producto_color_procesados.includes(id)) {
                const producto = {
                    producto_id: productoPrevio.producto_id,
                    producto_nombre: productoPrevio.nombre_producto,
                    color_id: productoPrevio.color_id,
                    color_nombre: productoPrevio.nombre_color,
                    precio_venta: parseFloat(productoPrevio.precio_unitario),
                    subtotal: 0,
                    subtotal_nuevo: 0,
                    porcentaje_descuento: parseFloat(productoPrevio.porcentaje_descuento),
                    monto_descuento: parseFloat(productoPrevio.monto_descuento),
                    precio_venta_nuevo: parseFloat(productoPrevio.precio_unitario_nuevo),
                    tallas: []
                }

                //==== BUSCANDO SUS TALLAS ====
                const tallas = detalles.filter((t) => {
                    return t.producto_id == productoPrevio.producto_id && t.color_id ==
                        productoPrevio.color_id;
                })

                if (tallas.length > 0) {
                    const producto_color_tallas = [];
                    tallas.forEach((t) => {
                        const talla = {
                            talla_id: t.talla_id,
                            talla_nombre: t.nombre_talla,
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
        calcularSubTotal();

        //===== PINTANDO DETALLE ======
        pintarDetalle();

        //========= PINTAR DESCUENTOS Y CALCULARLOS ============
        carrito.forEach((c) => {
            calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
        })

        //===== CALCULAR MONTOS Y PINTARLOS ======
        calcularMontos();

        const embalajeInput = document.querySelector('.embalaje');
        if (embalajeInput) {
            embalajeInput.value = parseFloat(documento.monto_embalaje) ?? 0;
            embalajeInput.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }

        const envioInput = document.querySelector('.envio');
        if (envioInput) {
            envioInput.value = parseFloat(documento.monto_envio) ?? 0;
            envioInput.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }

    }

    //======= CALCULAR DESCUENTO ========
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

        }
    }

    //======== CARGAR SUBTOTAL =======
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

    //========= PINTAR TABLA STOCKS ==========
    const pintarTableStocks = (producto) => {
        let filas = ``;
        const tableStocksBody = document.querySelector('#table-stocks tbody');

        producto.colores.forEach((color) => {
            filas += `
                <tr>
                    <th scope="row" data-producto=${producto.id} data-color=${color.id}>
                        <div style="width:200px;">${producto.nombre}</div>
                    </th>
                    <th scope="row">${color.nombre}</th>
            `;

            color.tallas.forEach((talla) => {
                if (talla.stock_logico == 0) {
                    filas += `
                        <td style="background-color: rgb(210, 242, 242);">
                            <p style="margin:0;width:20px;text-align:center;">${talla.stock_logico}</p>
                        </td>
                          <td width="8%">
                            <input style="width:50px;text-align:center;border: 2px solid #207ebc;" type="text" class="form-control inputCantidad"
                                id="inputCantidad_${producto.id}_${color.id}_${talla.id}"
                                data-producto-id="${producto.id}"
                                data-producto-nombre="${producto.nombre}"
                                data-color-nombre="${color.nombre}"
                                data-talla-nombre="${talla.nombre}"
                                data-color-id="${color.id}" data-talla-id="${talla.id}"
                                data-producto-codigo="${producto.codigo}">
                        </td>
                    `;
                } else {
                    filas += `
                        <td style="background-color: rgb(210, 242, 242);">
                            <p style="margin:0;width:20px;text-align:center;font-weight:bold;">${talla.stock_logico}</p>
                        </td>
                        <td width="8%">
                            <input style="width:50px;text-align:center;border: 2px solid #207ebc;" type="text" class="form-control inputCantidad"
                                id="inputCantidad_${producto.id}_${color.id}_${talla.id}"
                                data-producto-id="${producto.id}"
                                data-producto-nombre="${producto.nombre}"
                                data-color-nombre="${color.nombre}"
                                data-talla-nombre="${talla.nombre}"
                                data-color-id="${color.id}" data-talla-id="${talla.id}"
                                data-producto-codigo="${producto.codigo}">
                        </td>
                    `;
                }
            });

            filas += `</tr>`;
        });

        tableStocksBody.innerHTML = filas;
    }

    //============== PINTAR DETALLE ===========
    function pintarDetalle() {
        let fila = ``;

        carrito.forEach((c) => {
            let htmlTallas = ``;

            fila = `<tr>`;

            if (documento.estado_pago === 'PENDIENTE' && documento.estado_traslado === 'PENDIENTE') {
                fila += `<td>
                            <i class="fas fa-trash-alt btn btn-danger delete-product"
                            data-producto="${c.producto_id}" data-color="${c.color_id}"></i>
                        </td>`;
            }

            if (documento.estado_pago === 'PAGADA' || documento.estado_traslado !== 'PENDIENTE') {
                fila += `<td></td>`;
            }

            fila += `
                        <td>${c.producto_nombre}</td>
                        <td>${c.color_nombre}</td>
                    `;

            // tallas
            tallasBD.forEach((t) => {
                let cantidad = c.tallas.filter((ct) => t.id == ct.talla_id);
                cantidad = cantidad.length ? cantidad[0].cantidad : 0;

                if (cantidad == 0) {
                    htmlTallas += `<td></td>`;
                } else {
                    htmlTallas += `<td style="font-weight:bold;">${cantidad}</td>`;
                }
            });

            // precio venta con comparación
            htmlTallas += `<td style="text-align: right;">`;
            if (parseFloat(c.precio_venta) !== parseFloat(c.precio_venta_nuevo)) {
                htmlTallas += `
                <div>
                    <del style="color: gray;">${parseFloat(c.precio_venta).toFixed(2)}</del><br>
                    <strong>${parseFloat(c.precio_venta_nuevo).toFixed(2)}</strong>
                </div>
            `;
            } else {
                htmlTallas += `
                <div>${parseFloat(c.precio_venta_nuevo).toFixed(2)}</div>
            `;
            }
            htmlTallas += `</td>`;

            // subtotal con comparación
            htmlTallas += `<td class="td-subtotal" style="text-align: right;">`;
            if (parseFloat(c.subtotal) !== parseFloat(c.subtotal_nuevo)) {
                htmlTallas += `
                <div>
                    <del style="color: gray;">${parseFloat(c.subtotal).toFixed(2)}</del><br>
                    <strong>${parseFloat(c.subtotal_nuevo).toFixed(2)}</strong>
                </div>
            `;
            } else {
                htmlTallas += `
                <div>${parseFloat(c.subtotal_nuevo).toFixed(2)}</div>
            `;
            }
            htmlTallas += `</td>`;

            // porcentaje descuento
            htmlTallas += `
            <td style="text-align: center;">
                <span data-producto-id="${c.producto_id}" data-color-id="${c.color_id}"
                style="width:130px; margin: 0 auto;"
                class="detailDescuento">${parseFloat(c.porcentaje_descuento).toFixed(2)}%</span>
            </td>
        `;

            fila += htmlTallas + `</tr>`;
        });

        tableDetalleBody.innerHTML = fila;
    }

    function validacionAgregarProducto() {
        let validacion = true;
        //====== VALIDACIONES =======
        if (!precioVentaFinal) {
            toastr.error('DEBE SELECCIONAR UN PRECIO DE VENTA!!!');
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

    //======== LLENAR CANTIDADES PREVIAS AL TABLERO DE STOCKS =====
    function loadCantPrevias() {

        carrito.forEach((p) => {
            const select_precio_venta = document.querySelector(`#precio-venta-${p.producto_id}`);
            console.log('lad cant prev')
            console.log(p);
            console.log(select_precio_venta)
            if (select_precio_venta) {
                select_precio_venta.value = p.precio_venta;
            }
            p.tallas.forEach((t) => {
                const inputLoad = document.querySelector(
                    `#inputCantidad_${p.producto_id}_${p.color_id}_${t.talla_id}`);
                if (inputLoad) {
                    inputLoad.value = t.cantidad;
                }
            })
        })
    }

    function cambiarAlmacen(almacen_id) {

        toastr.clear();

        mostrarAnimacion();

        //======== LIMPIAR SELECTS ======
        $('#producto').val(null).trigger('change');
        $('#precio_venta').val(null).trigger('change');

        //======= LIMPIAR TABLERO STOCKS ======
        destruirDataTable(dtStocksVenta);
        limpiarTabla('table-stocks');
        dtStocksVenta = iniciarDataTable('table-stocks');

        //========== LIMPIAR DETALLE DE LA VENTA ========
        carrito.length = 0;
        destruirDataTable(dtDetalleVenta);
        limpiarTabla('table-detalle');
        pintarDetalle(carrito);
        dtDetalleVenta = iniciarDataTable('table-detalle');

        carrito.forEach((c) => {
            calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
        })
        calcularMontos();

        ocultarAnimacion();
        toastr.info('SE HA LIMPIADO EL FORMULARIO');

    }

    function desactivarEventosSelectsMdlEnvio() {
        document.querySelector('#departamento').onchange = null;
        document.querySelector('#provincia').onchange = null;
        document.querySelector('#distrito').onchange = null;
        document.querySelector('#tipo_envio').onchange = null;
        document.querySelector('#empresa_envio').onchange = null;
    }

    async function setDatosEnvioPrevio() {

        const despacho = @json($envio_venta);
        if (despacho) {
            document.querySelector('#monto-envio').classList.remove('btn-light');
            document.querySelector('#monto-envio').classList.add('btn-success');

            desactivarEventosSelectsMdlEnvio();
            window.departamentoSelect.setValue(parseInt(despacho.departamento_id));
            const provincias = await getProvincias(parseInt(despacho.departamento_id));
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

    function activarEventosSelectsMdlEnvio() {
        document.querySelector('#departamento').onchange = function(e) {
            setUbicacionDepartamento(e.target.value, 'first');
        };

        document.querySelector('#provincia').onchange = function(e) {
            setUbicacionProvincia(e.target.value, 'first');
        };

        document.querySelector('#distrito').onchange = function(e) {
            setMdlDistrito();
        };

        document.querySelector('#tipo_envio').onchange = function(e) {
            getEmpresasEnvio();
        };

        document.querySelector('#empresa_envio').onchange = function(e) {
            getSedesEnvio();
        };
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

    function setConfiguracionDefault() {
        $('#metodo_pago_1').val(3).change();

        if (documento.estado_pago === 'PAGADA') {
            $('#condicion_id').select2({
                disabled: true
            });
            document.querySelector('.envio').disabled = true;
            document.querySelector('.embalaje').disabled = true;
            $('#almacen').select2({
                disabled: true
            });
        }

        if (documento.estado_despacho !== 'PENDIENTE' && documento.estado_despacho !== 'S/D') {
            document.querySelector('.btn-guardar-envio').style.display = 'none';
            document.querySelector('.btn-borrar-envio').style.display = 'none';
            document.querySelector('#btn-consultar-dni').style.display = 'none';
            window.departamentoSelect.disable();
            window.provinciaSelect.disable();
            window.distritoSelect.disable();
            window.tipoEnvioSelect.disable();
            window.tipoPagoEnvioSelect.disable();
            window.empresaEnvioSelect.disable();
            window.sedeEnvioSelect.disable();
            // window.origenVentaSelect.disable();
            window.tipoDocDestinatarioSelect.disable();
            document.querySelector('#check_entrega_domicilio').disabled = true;
            document.querySelector('#direccion_entrega').disabled = true;
            document.querySelector('#fecha_envio').disabled = true;
            document.querySelector('#obs-rotulo').disabled = true;
            document.querySelector('#obs-despacho').disabled = true;
            document.querySelector('#nro_doc_destinatario').disabled = true;
            document.querySelector('#nombres_destinatario').disabled = true;
        }

        const envio_venta = @json($envio_venta);

        if (!envio_venta) {
            window.departamentoSelect.setValue(15);
            window.tipoEnvioSelect.setValue(188);
        }
    }

    async function accionOpenMdlEnvio() {

        //======= COLCANDO EN MODAL ENVIO EL NOMBRE DEL CLIENTE =======
        let clienteSeleccionado = $('#cliente').select2('data')[0];
        const hayEnvio = document.querySelector('#data_envio').value;
        const estado_despacho = @json($documento->estado_despacho);

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

        if (clienteSeleccionado && !hayEnvio && estado_despacho === 'S/D') {
            desactivarEventosSelectsMdlEnvio();
            window.departamentoSelect.setValue(parseInt(clienteSeleccionado.departamento_id));
            const provincias = await getProvincias(parseInt(clienteSeleccionado.departamento_id));
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
