@extends('layout')
@section('content')

@section('ventas-active', 'active')
@section('documento-active', 'active')
@include('ventas.documentos.editar.modals.modal_cliente')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2 style="text-transform:uppercase"><b>CONVERTIR DOCUMENTO DE VENTA</b></h2>
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


<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <input type="hidden" id='asegurarCierre'>

                    @include('ventas.documentos.convertir.forms.form_convertir')

                    <hr>
                    <div class="row">

                        <div class="col-lg-12">
                            <div class="panel panel-success" id="panel_detalle">
                                <div class="panel-heading">
                                    <h4 class=""><b>Detalle del Documento de Venta</b></h4>
                                </div>
                                <div class="panel-body ibox-content">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                @include('ventas.documentos.convertir.tables.tbl_detalle')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                @include('ventas.documentos.convertir.tables.tbl_montos')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group row">

                        <div class="col-md-6 text-left" style="color:#fcbc6c">
                            <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                                (<label class="required"></label>) son obligatorios.</small>
                        </div>

                        <div class="col-md-6 text-right">

                            <a href="{{ route('ventas.documento.index') }}" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
                            <button type="submit" id="btn_grabar" class="btn btn-w-m btn-success"
                                form="form-convertir-doc">
                                <i class="fa fa-save"></i> Convertir
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
@endpush

@push('scripts')
<script>
    const tallasBD = @json($tallas);
    const detalles = @json($detalles);
    const tableDetalleBody = document.querySelector('#table-detalle-conv tbody');
    const tfootSubtotal = document.querySelector('.subtotal');
    const tfootIgv = document.querySelector('.igv');
    const tfootTotal = document.querySelector('.total');
    const btnGrabar = document.querySelector('#btn_grabar');

    let carrito = [];

    document.addEventListener('DOMContentLoaded', () => {
        formatearDetalle();
        pintarDetalle();
        loadSelect2();
        events();

    })

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
                                text: item.descripcion
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

    function events() {
        eventsCliente();

        document.querySelector('#form-convertir-doc').addEventListener('submit', (e) => {
            e.preventDefault();
            cargarProductos();
            convertirDocumento(e.target);
        })
    }

    //========= ENVIAR VENTA ===========
    function convertirDocumento(formConvertirDoc) {
        const select = document.querySelector('#tipo_comprobante');
        const tipo_comprobante_text = select.options[select.selectedIndex].text;
        const doc = document.querySelector('#documento').value;

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: `CONVERTIR ${doc} A ${tipo_comprobante_text}?`,
            text: "Se generará un nuevo doc venta en la caja actual del usuario,pero no será contabilizado",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                try {

                    Swal.fire({
                        title: `Convirtiendo`,
                        text: "Por favor, espere...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });


                    const formData = new FormData(formConvertirDoc);
                    const documento = @json($documento);
                    formData.append('documento_id', documento.id);
                    formData.append('lstVenta', JSON.stringify(carrito));


                    const res = await axios.post(route('ventas.documento.convertirStore'), formData);

                    if (res.data.success) {

                        let url_open_pdf = '{{ route('ventas.documento.comprobante', [':id', ':size']) }}'
                            .replace(':id', res.data.documento_id)
                            .replace(':size', 80);

                        window.open(url_open_pdf, 'Comprobante SISCOM',
                            'location=1, status=1, scrollbars=1,width=900, height=600');
                        location = "{{ route('ventas.documento.index') }}";
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');

                    } else {
                        Swal.close();
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    Swal.close();
                    toastr.error(error, 'ERROR EN LA PETICIÓN CONVERTIR DOCUMENTO');
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

    //======== CARGAR PRODUCTOS ======
    function cargarProductos() {
        $('#productos_tabla').val(JSON.stringify(detalles));
    }


    //========= FORMATEAR CARRITO COMO JSON ==============
    //====== PRODUCTO - COLOR [TALLAS []] =======
    const formatearDetalle = () => {
        console.log(detalles);
        const producto_color_procesados = [];
        detalles.forEach((p) => {
            const llave = `${p.producto_id}-${p.color_id}`;
            if (!producto_color_procesados.includes(llave)) {
                const producto = {
                    producto_id: p.producto_id,
                    color_id: p.color_id,
                    producto_nombre: p.nombre_producto,
                    color_nombre: p.nombre_color,
                    modelo_nombre: p.nombre_modelo,
                    precio_venta: parseFloat(p.precio_unitario).toFixed(2),
                    porcentaje_descuento: p.porcentaje_descuento,
                    precio_venta_nuevo: parseFloat(p.precio_unitario_nuevo).toFixed(2)
                }

                const tallasProducto = detalles.filter((c) => {
                    return c.producto_id == p.producto_id && c.color_id == p.color_id;
                })
                const tallas = [];
                tallasProducto.forEach((t) => {
                    const talla = {
                        talla_id: t.talla_id,
                        cantidad: parseInt(t.cantidad),
                        talla_nombre: t.nombre_talla
                    }
                    tallas.push(talla);
                })
                producto.tallas = tallas;
                carrito.push(producto);
                producto_color_procesados.push(llave);
            }
        })

        console.log('CARRITO', carrito);
        cargarSubTotal();

        carrito.forEach((c) => {
            calcularDescuento(c.producto_id, c.color_id, c.porcentaje_descuento);
        })
    }

    //======== CARGAR SUBTOTAL =======
    function cargarSubTotal() {
        carrito.forEach((p) => {
            let cantidadTallas = 0;
            p.tallas.forEach((t) => {
                cantidadTallas += t.cantidad;
            })
            p.subtotal = cantidadTallas * parseFloat(p.precio_venta);
            p.subtotal_nuevo = cantidadTallas * parseFloat(p.precio_venta_nuevo);
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
            producto_color_editar.precio_venta_nuevo = parseFloat(producto_color_editar.precio_venta_nuevo).toFixed(2);

            carrito[indiceExiste] = producto_color_editar;
        }
    }

    //============== PINTAR DETALLE ===========
    function pintarDetalle() {
        let fila = ``;

        carrito.forEach((p) => {
            const carritoFiltrado = carrito.filter((c) => {
                return c.producto_id == p.producto_id && c.color_id == p.color_id;
            });

            fila += `
                        <tr>

                            <td>${p.producto_nombre} - ${p.color_nombre}</td>
                    `;
            let descripcion = ``;

            tallasBD.forEach((tb) => {
                const indexTalla = p.tallas.findIndex((pt) => {
                    return pt.talla_id == tb.id;
                });

                if (indexTalla !== -1) {
                    fila += `<td>${p.tallas[indexTalla].cantidad}</td> `;
                } else {
                    fila += `<td></td>`;
                }
            })


            fila += `
                            <td>
                                <span class="precio_venta_${p.producto_id}_${p.color_id}">
                                        ${p.porcentaje_descuento === 0? p.precio_venta:p.precio_venta_nuevo}
                                </span>
                            </td>
                            <td>
                                <span class="subtotal_${p.producto_id}_${p.color_id}">
                                    ${p.porcentaje_descuento === 0? p.subtotal:p.subtotal_nuevo}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <input readonly data-producto-id="${p.producto_id}" data-color-id="${p.color_id}"
                                style="width:130px; margin: 0 auto;" value="${p.porcentaje_descuento}"
                                class="form-control detailDescuento"></input>
                            </td>
                        </tr>
                    `;
        })
        tableDetalleBody.innerHTML = fila;
    }

    function cambiarCliente() {
        const cliente = $('#cliente').select2('data')[0].text.trim();

        if (!cliente) return;

        const tipoComprobanteId = $('#tipo_comprobante').val();
        const tipoDoc = cliente.split(':', 1)[0];
        console.log(tipoDoc)

        if (tipoDoc == 'RUC') {
            $('#tipo_comprobante').val(127).trigger('change');
            return;
        }
        if (tipoDoc == 'DNI') {
            $('#tipo_comprobante').val(128).trigger('change');
            return;
        }
    }

    function cambiarTipoComprobante(tipoComprobanteId) {
        toastr.clear();
        if (!tipoComprobanteId) return;

        const cliente = $('#cliente').select2('data')[0].text.trim();
        const tipoDoc = cliente.split(':', 1)[0];

        console.log(tipoDoc)

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
</script>
@endpush
