@extends('layout')
@section('content')

    @include('ventas.documentos.editar.modals.mdl_envio')
    @include('ventas.documentos.editar.modals.modal_cliente')

@section('ventas-active', 'active')
@section('documento-active', 'active')
<style>
    .toastr-morado {
        background-color: #652e9b !important;
        /* Color morado */
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2 style="text-transform:uppercase"><b>CONVERTIR COTIZACIÓN A DOC VENTA</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('ventas.documento.index') }}">Documentos de Venta</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Registrar</strong>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    @include('ventas.documentos.cotizacion_a_docventa.forms.form_create')

                    <hr>

                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">

                        <div class="col-md-6 text-left" style="color:#fcbc6c">
                            <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                                (<label class="required"></label>) son obligatorios.</small>
                        </div>

                        <div class="col-md-6 text-right">

                            <a href="javascript:void(0)" onclick="regresarClick(event)" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
                            @if ($cantidadErrores == 0)
                                <button form="enviar_documento" type="submit" id="btn_grabar"
                                    class="btn btn-w-m btn-success">
                                    <i class="fa fa-save"></i> Grabar
                                </button>
                            @endif

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
    const productosPrevios = @json($detalle);
    const tallas = @json($tallas);
    const cotizacion = @json($cotizacion);
    const cantidadErrores = @json($cantidadErrores);

    const tableDetalleBody = document.querySelector('#table-detalle tbody');
    const tableDetalleFoot = document.querySelector('#table-detalle tfoot');

    const tableSubtotal = document.querySelector('.subtotal');
    const tableTotal = document.querySelector('.total');
    const tableIgv = document.querySelector('.igv');

    const inputSubTotal = document.querySelector('#monto_sub_total');
    const inputEmbalaje = document.querySelector('#monto_embalaje');
    const inputEnvio = document.querySelector('#monto_envio');
    const inputTotal = document.querySelector('#monto_total');
    const inputIgv = document.querySelector('#monto_total_igv');
    const inputTotalPagar = document.querySelector('#monto_total_pagar');

    const tfootSubtotal = document.querySelector('.subtotal');
    const tfootEmbalaje = document.querySelector('.embalaje');
    const tfootEnvio = document.querySelector('.envio');
    const tfootTotal = document.querySelector('.total');
    const tfootIgv = document.querySelector('.igv');
    const tfootTotalPagar = document.querySelector('.total-pagar');

    const formDocumento = document.querySelector('#enviar_documento');
    const btnRegresar = document.querySelector('#btn_cancelar');
    const btnGrabar = document.querySelector('#btn_grabar');

    let carrito = [];
    let carritoFormateado = [];
    let asegurarCierre = 2;

    document.addEventListener('DOMContentLoaded', async () => {

        cargarProductosPrevios();
        setAsegurarCierre();
        cargarSelect2();
        iniciarSelectsMdlEnvio();

        showAlertas();
        formatearDetalle();

        events();
    })


    function events() {

        eventsCliente();
        eventsModalEnvio();

        //======== evitando doble click en regresar =========
        btnRegresar.addEventListener('click', () => {
            btnRegresar.disabled = true;
        })

        formDocumento.addEventListener('submit', (e) => {

            e.preventDefault();
            btnGrabar.disabled = true;
            let correcto = validarCampos();

            if (correcto) {
                let total = $('#monto_total_pagar').val();
                $('#monto_venta').val(total);
                $('#importe_venta').val(total);
                let condicion_id = $('#condicion_id').val();
                let cadena = condicion_id.split('-');
                enviarVenta(e.target);

            } else {
                btnGrabar.disabled = false;
            }

        })

        //=========== MODAL DESPACHO =========
        document.querySelector('.btn-envio').addEventListener('click', () => {
            accionOpenMdlEnvio();
        })
    }

    function setAsegurarCierre() {
        //======= SI NO HAY ERRORES DEVUELVE  STOCKS LOGICOS =======
        if (cantidadErrores == 0) {
            asegurarCierre = 1;
        } else {
            asegurarCierre = 2;
        }
    }

    //===== SHOW ALERTAS ============
    function showAlertas() {
        productosPrevios.forEach((c) => {
            if (c.tipo == "NO EXISTE EL PRODUCTO COLOR TALLA") {
                toastr.error(`${c.producto_nombre} - ${c.color_nombre} - ${c.talla_nombre}`,
                    'No existe el producto', {
                        timeOut: 0,
                        extendedTimeOut: 0,
                        toastClass: 'toastr-morado'
                    });
            }
            if (c.tipo == "STOCK LOGICO INSUFICIENTE") {
                toastr.error(`${c.producto_nombre} - ${c.color_nombre} - ${c.talla_nombre}`,
                    'Stock lógico insuficiente', {
                        timeOut: 0,
                        extendedTimeOut: 0,
                    });
            }
        })
    }

    //======= EVITAR DOBLE CLICK EN REGRESAR =========
    function regresarClick(event) {
        event.preventDefault();
        var btnCancelar = document.getElementById("btn_cancelar");
        if (!btnCancelar.classList.contains("disabled")) {
            btnCancelar.classList.add("disabled");
            window.location.href = '{{ route('ventas.cotizacion.index') }}';
        }
    }

    //========== VALIDAR TIPO ===============
    function validarTipoComprobante() {
        var enviar = true

        if ($('#tipo_cliente_documento').val() == '0' && $('#tipo_comprobante').val() == 'FACTURA') {
            toastr.error('El tipo de documento del cliente es diferente a RUC.', 'Error');
            enviar = false;
        }
        return enviar
    }

    //============ ENVIAR VENTA ===================
    async function enviarVenta(formCotizacionADocVenta) {


        let validacion_comprobante = validarTipoComprobante();

        if (!validacion_comprobante) return;


        const formData = new FormData(formCotizacionADocVenta);

        const textAlert = "¿Desea convertir la cotización a documento de venta?";

        let result = await Swal.fire({
            title: textAlert,
            text: "Se generará un nuevo documento de venta!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, genéralo!"
        });

        if (!result.isConfirmed) {
            asegurarCierre = 1;
            btnGrabar.disabled = false;
            return;
        }

        Swal.fire({
            title: "Conviertiendo cotización a documento de venta...",
            text: "Por favor, espere...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        formData.append('cotizacion_id', @json($cotizacion->id))
        const res = await axios.post(route('ventas.cotizacion.convertirADocVenta'), formData);

        if (res.data.success) {

            asegurarCierre = 2;

            let url_open_pdf = '{{ route('ventas.documento.comprobante', [':id1', ':size']) }}'
                .replace(':id1', res.data.documento_id)
                .replace(':size', 80);

            window.open(url_open_pdf, 'Comprobante SISCOM',
                'location=1, status=1, scrollbars=1,width=900, height=600');
            location = "{{ route('ventas.documento.index') }}";
            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');

        } else {
            asegurarCierre = 1;
            Swal.close();
            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
        }

    }

    //========== LIBRERIA SELECT 2 =============
    const cargarSelect2 = () => {
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
    }

    //====== FORMATEAR EL CARRITO A FORMATO DE BD ======
    function formatearDetalle() {
        carrito.forEach((d) => {
            console.log('producto_color')
            d.tallas.forEach((t) => {
                console.log('talla')
                const producto = {};
                producto.producto_id = d.producto_id;
                producto.color_id = d.color_id;
                producto.talla_id = t.talla_id;
                producto.cantidad = t.cantidad;
                producto.precio_unitario = d.precio_venta;
                producto.porcentaje_descuento = d.porcentaje_descuento;
                producto.precio_unitario_nuevo = d.precio_venta_nuevo;
                carritoFormateado.push(producto);
            })
        })
    }

    //==========    CARGAR PRODUCTOS AL FORM   =================
    function cargarProductos() {
        carrito.forEach((c) => {
            c.cantidad = c.cantidad_solicitada;
        })
    }

    //========== VALIDAR CAMPOS ======================
    function validarCampos() {

        let correcto = true;
        const moneda = document.querySelector('#moneda').value;
        const observacion = document.querySelector('#observacion').value;
        const condicion_id = document.querySelector('#condicion_id').value;
        const fecha_documento_campo = document.querySelector('#fecha_documento_campo').value;
        const fecha_atencion_campo = document.querySelector('#fecha_atencion_campo').value;
        const fecha_vencimiento_campo = document.querySelector('#fecha_vencimiento_campo').value;

        const cliente_id = document.querySelector('#cliente').value;
        const tipo_comprobante = document.querySelector('#tipo_comprobante').value;


        if (moneda == null || moneda == '') {
            correcto = false;
            toastr.error('El campo moneda es requerido.');
        }
        if (condicion_id == null || condicion_id == '') {
            correcto = false;
            toastr.error('El campo condicion de pago es requerido.');
        }
        if (fecha_documento_campo == null || fecha_documento_campo == '') {
            correcto = false;
            toastr.error('El campo fecha de documento es requerido.');
        }
        if (fecha_atencion_campo == null || fecha_atencion_campo == '') {
            correcto = false;
            toastr.error('El campo fecha de atención es requerido.');
        }
        if (fecha_vencimiento_campo == null || fecha_vencimiento_campo == '') {
            correcto = false;
            toastr.error('El campo fecha de vencimiento es requerido.');
        }

        const campos = {
            moneda,
            observacion,
            condicion_id,
            fecha_documento_campo,
            fecha_atencion_campo,
            fecha_vencimiento_campo,
            cliente_id,
            tipo_comprobante
        };

        //validación de fechas...
        const fechaDocumento = new Date(fecha_documento_campo);
        const fechaVencimiento = new Date(fecha_vencimiento_campo);


        if (fecha_documento_campo > fecha_vencimiento_campo) {
            correcto = false;
            toastr.error('El campo fecha de vencimiento debe ser mayor a la fecha de atención.');
        }

        if (cliente_id == null || cliente_id == '') {
            correcto = false;
            toastr.error('El campo cliente es requerido.');
        }
        if (tipo_comprobante == null || tipo_comprobante == '') {
            correcto = false;
            toastr.error('El campo tipo de venta es requerido.');
        }

        return correcto;
    }


    //=================== PINTAR MONTOS ==============
    const pintarMontos = () => {
        tfootSubtotal.textContent = cotizacion.sub_total;
        tfootEmbalaje.value = cotizacion.monto_embalaje;
        tfootEnvio.value = cotizacion.monto_envio;
        tfootTotal.textContent = cotizacion.total;
        tfootIgv.textContent = cotizacion.total_igv;
        tfootTotalPagar.textContent = cotizacion.total_pagar;
    }

    //================== REORDENAR CARRITO ==================
    const reordenarCarrito = () => {
        carrito.sort(function(a, b) {
            if (a.producto_id === b.producto_id) {
                return a.color_id - b.color_id;
            } else {
                return a.producto_id - b.producto_id;
            }
        });
    }

    //=============== CALCULAR SUBTOTAL POR PRODUCTO-COLOR ======================
    const calcularSubTotal = () => {
        let subtotal = 0;

        carrito.forEach((p) => {
            p.tallas.forEach((t) => {
                subtotal += parseFloat(p.precio_venta) * parseFloat(t.cantidad);
            })

            p.subtotal = subtotal;
            subtotal = 0;
        })
    }


    function clearDetalleTable() {
        while (tableDetalleBody.firstChild) {
            tableDetalleBody.removeChild(tableDetalleBody.firstChild);
        }
    }

    //======  CARGAR PRODUCTOS AL CARRITO EN FORMATO ANIDADO =======
    const cargarProductosPrevios = () => {
        //====== CARGANDO CARRITO ======
        const producto_color_procesados = [];

        productosPrevios.forEach((productoPrevio) => {
            const id = `${productoPrevio.producto_id}-${productoPrevio.color_id}`;

            if (!producto_color_procesados.includes(id)) {
                const producto = {
                    producto_id: productoPrevio.producto_id,
                    producto_nombre: productoPrevio.producto_nombre,
                    color_id: productoPrevio.color_id,
                    color_nombre: productoPrevio.color_nombre,
                    precio_venta: productoPrevio.precio_unitario,
                    subtotal: 0,
                    subtotal_nuevo: 0,
                    porcentaje_descuento: parseFloat(productoPrevio.porcentaje_descuento),
                    monto_descuento: 0,
                    precio_venta_nuevo: 0,
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
                            cantidad: parseInt(t.cantidad_solicitada),
                            tipo: t.tipo,
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
        reordenarCarrito();
        //===== PINTANDO DETALLE ======
        pintarDetalleCotizacion(carrito);
        //========= PINTAR DESCUENTOS Y CALCULARLOS ============
        carrito.forEach((c) => {
            calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
        })


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
            producto_color_editar.monto_descuento = porcentaje_descuento === 0 ? 0 : producto_color_editar
                .subtotal * (porcentaje_descuento / 100);
            producto_color_editar.precio_venta_nuevo = porcentaje_descuento === 0 ? 0 : (producto_color_editar
                .precio_venta * (1 - porcentaje_descuento / 100)).toFixed(2);
            producto_color_editar.subtotal_nuevo = porcentaje_descuento === 0 ? 0 : (producto_color_editar
                .subtotal * (1 - porcentaje_descuento / 100)).toFixed(2);

            carrito[indiceExiste] = producto_color_editar;


            //==== ACTUALIZANDO PRECIO VENTA Y SUBTOTAL EN EL HTML ====
            const detailPrecioVenta = document.querySelector(
                `.precio_venta_${producto_color_editar.producto_id}_${producto_color_editar.color_id}`);
            const detailSubtotal = document.querySelector(
                `.subtotal_${producto_color_editar.producto_id}_${producto_color_editar.color_id}`);

            if (porcentaje_descuento !== 0) {
                detailPrecioVenta.textContent = producto_color_editar.precio_venta_nuevo;
                detailSubtotal.textContent = producto_color_editar.subtotal_nuevo;
            } else {
                detailPrecioVenta.textContent = producto_color_editar.precio_venta;
                detailSubtotal.textContent = producto_color_editar.subtotal;
            }

        }
    }

    //====== PINTAR DETALLE COTIZACIÓN ======
    function pintarDetalleCotizacion(carrito) {
        let fila = ``;
        let htmlTallas = ``;

        carrito.forEach((c) => {
            htmlTallas = ``;
            fila += `<tr>

                            <th>${c.producto_nombre} - ${c.color_nombre}</th>`;

            //tallas
            tallas.forEach((t) => {
                let talla_item = c.tallas.filter((ct) => {
                    return t.id == ct.talla_id;
                });

                const cantidad = talla_item.length > 0 ? talla_item[0].cantidad : '';
                const validacion = talla_item.length > 0 ? talla_item[0].tipo : '';

                htmlTallas += `<td>
                                        <span style="${validacion === 'STOCK LOGICO INSUFICIENTE' ? 'color: #ff6666; font-weight: bold;' : (validacion === 'NO EXISTE EL PRODUCTO COLOR TALLA' ? 'color: #9966cc; font-weight: bold;' : (validacion === 'STOCK LOGICO VÁLIDO' ? 'color: black; font-weight: bold;' : ''))}">
                                            ${cantidad}
                                        </span>
                                    </td>
                                    `;
            })

            htmlTallas += `   <td style="text-align: right;">
                                    <span class="precio_venta_${c.producto_id}_${c.color_id}">
                                        ${c.porcentaje_descuento === 0? c.precio_venta:c.precio_venta_nuevo}
                                    </span>
                                </td>
                                <td class="td-subtotal" style="text-align: right;">
                                    <span class="subtotal_${c.producto_id}_${c.color_id}">
                                        ${c.porcentaje_descuento === 0? c.subtotal:c.subtotal_nuevo}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <input readonly data-producto-id="${c.producto_id}" data-color-id="${c.color_id}"
                                    style="width:130px; margin: 0 auto;" value="${c.porcentaje_descuento}"
                                    class="form-control detailDescuento"></input>
                                </td>
                            </tr>`;

            fila += htmlTallas;
            tableDetalleBody.innerHTML = fila;
        })
    }

    //============= DEVOLVER STOCK LÓGICO ===================
    window.addEventListener('beforeunload', async () => {
        if (asegurarCierre == 1) {
            await this.devolverCantidades();
            asegurarCierre = 2;
        } else {
            console.log("beforeunload", asegurarCierre);
        }
    });

    //================ DEVOLVER STOCK LÓGICO ===============
    async function devolverCantidades() {
        await this.axios.post(route('ventas.cotizacion.devolverCantidades'), {
            cotizacion_id: @json($cotizacion->id)
        });
    }

    function cambiarTipoComprobante(tipoComprobanteId) {
        toastr.clear();

        if (!tipoComprobanteId) return;
        const clienteSelecionado = $('#cliente').val();
        if (!clienteSelecionado) return;

        const cliente = $('#cliente').select2('data')[0].text.trim();
        const tipoDoc = cliente.split(':', 1)[0];

        if (tipoComprobanteId == 127 && tipoDoc != 'RUC') {
            $('#tipo_comprobante').val(null).trigger('change');
            toastr.error('EL CLIENTE DEBE TENER RUC!!')
            return;
        }
        if (tipoComprobanteId == 128 && tipoDoc != 'DNI') {
            $('#tipo_comprobante').val(null).trigger('change');
            toastr.error('EL CLIENTE DEBE TENER DNI!!')
            return;
        }
    }

    function cambiarCliente() {

        const clienteSelecionado = $('#cliente').val();
        if (!clienteSelecionado) return;

        const cliente = $('#cliente').select2('data')[0].text.trim();

        const tipoComprobanteId = $('#tipo_comprobante').val();
        const tipoDoc = cliente.split(':', 1)[0];

        if (tipoDoc == 'RUC') {
            $('#tipo_comprobante').val(127).trigger('change');
            return;
        }
        if (tipoDoc == 'DNI') {
            $('#tipo_comprobante').val(128).trigger('change');
            return;
        }
    }

    async function accionOpenMdlEnvio() {

        //======= COLCANDO EN MODAL ENVIO EL NOMBRE DEL CLIENTE =======
        const clienteId         = $('#cliente').val();
        if(!clienteId){
            toastr.clear();
            toastr.error('DEBE SELECCIONAR UN CLIENTE');
            return;
        }

        let clienteSeleccionado = $('#cliente').select2('data')[0];
        const hayEnvio          = document.querySelector('#data_envio').value;

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
            const provincias = await getProvincias(parseInt(clienteSeleccionado.departamento_id));
            pintarProvincias(provincias, parseInt(clienteSeleccionado.provincia_id));
            const distritos = await getDistritos(parseInt(clienteSeleccionado.provincia_id));
            pintarDistritos(distritos, parseInt(clienteSeleccionado.distrito_id));
            setZona(getZona(parseInt(clienteSeleccionado.departamento_id)));

            //====== COLOCAR TEXTO DEL SPAN =====
            const cliente_nombre = $("#cliente").find('option:selected').text();
            const nroDocumento = cliente_nombre.split(':')[1].split('-')[0].trim();
            const cliente_nombre_recortado = cliente_nombre.split('-')[1].trim()
            const tipo_documento = cliente_nombre.split(':')[0].trim();

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
            window.tipoEnvioSelect.setValue(188);
        }

        //========= ABRIR MODAL ENVÍO =======
        $("#modal_envio").modal("show");
    }

    @if (!empty($errores))
        asegurarCierre = 2;
        @foreach ($errores as $error)
            @if ($error->tipo == 'stocklogico')
                toastr.error(
                    'La cantidad solicitada {{ $error->cantidad }} excede al stock del producto {{ $error->producto }}',
                    'Error', {
                        timeOut: 0,
                        extendedTimeOut: 0
                    });
            @elseif ($error->tipo == 'producto_no_existe')
                toastr.error('No existe stock para el producto: {{ $error->producto }}', 'Error', {
                    timeOut: 0,
                    extendedTimeOut: 0
                });
            @endif
        @endforeach
    @endif
</script>
@endpush
