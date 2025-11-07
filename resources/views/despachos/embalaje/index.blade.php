@extends('layout')
@section('content')

    @include('despachos.embalaje.modals.modal-detalles-doc')
    {{-- @include('despachos.embalaje.modal-bultos') --}}
    @include('despachos.embalaje.modals.mdl_envio_edit')
    @include('despachos.embalaje.modals.mdl_embalar')

@section('despachos-active', 'active')
@section('embalaje-active', 'active')

<style>
    .fila-pendiente {
        background-color: #f2f96a !important;
        /* rojito leve */
    }

    .fila-fallas {
        background-color: rgb(255, 163, 173) !important;
        /* moradito leve */
    }

    .fila-revision {
        background-color: #fed295 !important;
        /* celestito leve */
    }

    .icono-pendiente {
        color: #f2f96a;
        /* Amarillo leve */
    }

    .icono-fallas {
        color: #fbaba2;
        /* Rojo suave */
    }

    .icono-revision {
        color: #fed295;
        /* Ámbar */
    }
</style>

</style>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>LISTA DE EMBALAJE</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Despachos</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row mb-5">

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                            <label for="filtroModo" style="font-weight: bold;">MODO:</label>
                            <select id="filtroModo" class="form-control select2_form"
                                onchange="dtDespachos.ajax.reload();">
                                <option value="VENTA">VENTA</option>
                                <option value="RESERVA">RESERVA</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                            <label for="filtroCliente" style="font-weight: bold;">CLIENTE:</label>
                            <select class="select2_form" style="text-transform: uppercase; width:100%"
                                name="filtroCliente" id="filtroCliente" required onchange="dtDespachos.ajax.reload();">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                            <label for="filtroFechaInicio" style="font-weight: bold;">FEC INICIO:</label>
                            <input value="<?php echo date('Y-m-d'); ?>" type="date" class="form form-control"
                                id="filtroFechaInicio" onchange="filtrarDespachoFechaInic()">
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                            <label for="filtroFechaFin" style="font-weight: bold;">FEC FIN:</label>
                            <input value="<?php echo date('Y-m-d'); ?>" type="date" class="form form-control"
                                id="filtroFechaFin" onchange="filtrarDespachoFechaFin()">
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-3">
                            <label for="filtroEstadoItems" style="font-weight: bold;">ESTADO ITEMS:</label>
                            <select id="filtroEstadoItems" class="form-control select2_form"
                                onchange="dtDespachos.ajax.reload();">
                                <option value="TODO">TODO</option>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="CON FALLAS">CON FALLAS</option>
                                <option value="EN REVISION">EN REVISION</option>
                            </select>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-12" style="display:flex;justify-content:end;">
                            <!-- Botón Excel -->
                            <button class="btn btn-success" style="margin-right: 10px;" onclick="downloadExcel();">
                                <i class="fas fa-file-excel"></i> EXCEL
                            </button>

                            <!-- Botón PDF -->
                            <button class="btn btn-danger" onclick="downloadPdf()">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Pendiente -->
                        <div class="col-auto d-flex align-items-center mr-3">
                            <i class="fa fa-square" style="color: #f2f96a;"></i>
                            <span class="ml-2">PENDIENTE</span>
                        </div>

                        <!-- Con Fallas -->
                        <div class="col-auto d-flex align-items-center mr-3">
                            <i class="fa fa-square" style="color: #f51c04;"></i>
                            <span class="ml-2">CON FALLAS</span>
                        </div>

                        <!-- En Revisión -->
                        <div class="col-auto d-flex align-items-center mr-3">
                            <i class="fa fa-square" style="color: #ffb347;"></i>
                            <span class="ml-2">EN REVISIÓN</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">

                                @include('despachos.embalaje.tables.tbl_list_despachos')

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
    .letrapequeña {
        font-size: 11px;
    }

    .envio-despachado {
        background-color: rgb(220, 255, 255) !important;
    }

    .envio-embalado {
        background-color: rgb(239, 244, 213) !important;
    }

    .col-estado-pendiente {
        border: 2px solid #FF5A5F;
        background-color: #FF5A5F;
        color: #FFFFFF;
        font-weight: bold;
        border-radius: 7px;
        padding: 0 5px;
        text-align: center;
        display: inline-block;
    }

    .col-estado-despachado {
        border: 2px solid #014c5b;
        background-color: #014c5b;
        color: #FFFFFF;
        font-weight: bold;
        border-radius: 7px;
        padding: 0 5px;
        text-align: center;
        display: inline-block;
    }

    .col-estado-reservado {
        border: 2px solid #566003;
        background-color: #566003;
        color: #FFFFFF;
        font-weight: bold;
        border-radius: 7px;
        padding: 0 5px;
        text-align: center;
        display: inline-block;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    let detallesDataTable;
    let dtDespachos = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDtEmbalaje();
        events();
        iniciarSelect2();
        iniciarSelectsMdlEnvio();
        detallesDataTable = dataTableDetalles();
    })

    function events() {
        //eventsModalBultos();
        eventsModalEnvio();
        eventsMdlEmbalar();
    }

    function iniciarSelect2() {

        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });

        $('#filtroCliente').select2({
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

    function iniciarDtEmbalaje() {

        dtDespachos = new DataTable('#dt-embalaje', {
            "serverSide": true,
            initComplete: function() {
                $('.dt-search').append(`
                <div class="text-muted small mt-1">
                    <strong>Buscar por: Modo, Doc, Cliente, Vendedor, Tipo envío, Empresa envío</strong>
                </div>
                `);
            },
            rowCallback: function(row, data, index) {
                let estadoItems = data.estado_items;

                if (estadoItems === 'PENDIENTE') {
                    $(row).addClass('fila-pendiente');
                } else if (estadoItems === 'EN REVISION') {
                    $(row).addClass('fila-revision');
                } else if (estadoItems === 'CON FALLAS') {
                    $(row).addClass('fila-fallas');
                }
            },
            "ajax": {
                "url": "{{ route('despachos.embalaje.getEmbalaje') }}",
                "type": "GET",
                "beforeSend": function() {
                    mostrarAnimacion();
                },
                "data": function(d) {
                    d.fecha_inicio = $('#filtroFechaInicio').val();
                    d.fecha_fin = $('#filtroFechaFin').val();
                    d.estado = $('#filtroEstado').val();
                    d.cliente_id = $('#filtroCliente').val();
                    d.modo = $('#filtroModo').val();
                    d.estado_items = $('#filtroEstadoItems').val();
                },
                "complete": function() {
                    ocultarAnimacion();
                }
            },
            order: [
                [2, 'asc'],
                [11, 'asc']
            ],
            "columns": [{
                    data: 'modo',
                    name: 'ev.modo',
                    'orderable': false,
                    className: "text-center letrapequeña"
                },
                {
                    data: 'documento_nro',
                    name: 'ev.documento_nro',
                    orderable: true,
                    className: "text-center letrapequeña"
                },
                {
                    data: 'cliente_nombre',
                    name: 'ev.cliente_nombre',
                    orderable: true,
                    className: "text-left letrapequeña",
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).css({
                            "font-weight": "bold",
                            "color": "#1a237e"
                        });
                    }
                },
                {
                    data: 'cliente_celular',
                    className: "text-left letrapequeña",
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'user_vendedor_nombre',
                    name: 'ev.user_vendedor_nombre',
                    className: "text-left letrapequeña",
                    orderable: false
                },
                {
                    data: 'almacen_nombre',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: false
                },
                {
                    data: 'sede_origen_nombre',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: false
                },
                {
                    data: 'sede_despachadora_nombre',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: false
                },
                {
                    data: 'user_despachador_nombre',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: false
                },
                {
                    data: 'fecha_envio_propuesta',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: true
                },
                {
                    data: 'fecha_envio',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: true
                },
                {
                    data: 'fecha_registro',
                    searchable: false,
                    className: "text-left letrapequeña",
                    orderable: true
                },
                {
                    data: 'tipo_envio',
                    name: 'ev.tipo_envio',
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'empresa_envio_nombre',
                    name: 'ev.empresa_envio_nombre',
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'sede_envio_nombre',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'ubigeo',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'entrega_domicilio',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'direccion_entrega',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'destinatario_nombre',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'destinatario_nro_doc',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'tipo_pago_envio',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'monto_envio',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false

                },
                {
                    data: 'obs_despacho',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'obs_rotulo',
                    searchable: false,
                    className: "text-center letrapequeña",
                    orderable: false
                },
                {
                    data: 'estado',
                    searchable: false,
                    orderable: false,
                    className: "text-center letrapequeña",
                    render: function(data) {
                        let estado = '';
                        if (data == "PENDIENTE") {
                            estado = `<div class="col-estado-pendiente">${data}</div>`;
                        }
                        if (data == "RESERVADO") {
                            estado = `<div class="col-estado-reservado">${data}</div>`;
                        }
                        if (data == "DESPACHADO") {
                            estado = `<div class="col-estado-despachado">${data}</div>`;
                        }
                        return estado;
                    }
                },
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    className: "text-center",
                    render: function(data) {

                        //Ruta Detalle
                        var url_detalle = '{{ route('despachos.embalaje.showDetalles', ':id') }}';
                        url_detalle = url_detalle.replace(':id', data.id);

                        //======== ACCIONES ========
                        let acciones = `<div class='btn-group' style='text-transform:capitalize;'><button data-toggle='dropdown' class='btn btn-success btn-sm  dropdown-toggle'><i class='fa fa-bars'></i></button>
                                                        <ul class='dropdown-menu'>
                                                            <li><a class='dropdown-item' href='javascript:void(0);' onclick="verDetalles(${data.documento_id})" title='Modificar' ><b><i class='fa fa-eye'></i> Detalle</a></b></li>

                                                       `;

                        if (data.estado == "PENDIENTE" && data.estado != 'EMBALADO' && (data.modo !=
                                'RESERVA')) {
                            acciones += `<li class='dropdown-divider'></li>
                                             <li>
                                                <a class='dropdown-item' href='javascript:void(0);' onclick="openMdlEditarEnvio(${data.id})" title='Editar'>
                                                    <b><i class="fas fa-truck"></i> Editar</b>
                                                </a>
                                            </li>
                                        `;
                        }

                        if (data.cliente_id != 1) {
                            acciones += `<li>
                                                <a class='dropdown-item' href='javascript:void(0);' onclick="openMdlEmbalar(${data.id})" title='Embalar' >
                                                    <b><i class="fas fa-cubes"></i> Embalar
                                                </a>
                                            </li>`;
                        }

                        if (data.estado_items != 'CON FALLAS') {
                            acciones += `<li>
                                                <a class='dropdown-item' href='javascript:void(0);' onclick="setFallado(${data.id})" title='Fallado' >
                                                    <b><i class="fas fa-times-circle"></i> Fallado
                                                </a>
                                            </li>`;
                        }

                        if (data.estado_items != 'EN REVISION') {
                            acciones += `<li>
                                                <a class='dropdown-item' href='javascript:void(0);' onclick="setRevision(${data.id})" title='Fallado' >
                                                    <b><i class="fas fa-clipboard-list"></i> Revision
                                                </a>
                                            </li>`;
                        }

                        /*if (data.estado != 'RESERVADO' && data.modo != 'VENTA') {
                            acciones += `
                             <li class='dropdown-divider'></li>
                            <li><a class='dropdown-item' href='javascript:void(0);' onclick="reservar(${data.documento_id},${data.id})" title='Reservar' ><b><i class="fa fa-tape"></i> Reservar</a></b></li>
                             `;
                        }*/

                        // if (data.estado === 'PENDIENTE' && data.modo === 'RESERVA') {
                        //     acciones += `
                        //     <li class='dropdown-divider'></li>
                        //     <li><a class='dropdown-item' href='javascript:void(0);' onclick="reservar(${data.documento_id},${data.id})" title='Reservar' ><b><i class="fa fa-tape"></i> Reservar</a></b></li>
                        //     </ul></div>`;
                        // }

                        acciones += `</ul></div>`;

                        return acciones;
                    }
                }

            ],
            "createdRow": function(row, data, dataIndex) {
                if (data.estado === 'EMBALADO') {
                    $(row).addClass('envio-embalado');
                }
                if (data.estado === 'DESPACHADO') {
                    $(row).addClass('envio-despachado');
                }
                $('td', row).css('vertical-align', 'middle');
            },
            "language": {
                "url": "{{ asset('Spanish.json') }}"
            },
        });
    }


    //Modal Eliminar
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger',
        },
        buttonsStyling: false
    })


    async function verDetalles(documento_id) {
        try {
            mostrarAnimacion();
            const res = await axios.get(route('despachos.embalaje.showDetalles', documento_id));

            if (res.data.success) {
                const detalles_doc_venta = res.data.detalles_doc_venta;

                $("#modal_detalles_doc").modal("show");
                pintarDetallesDoc(detalles_doc_venta);
                pintarMaestroDoc(res.data.documento)

            } else {
                toastr.error(`${res.data.message} - ${res.data.exception}`, "ERROR");
            }
        } catch (error) {
            toasr.error(error, 'ERROR EN LA PETICIÓN MOSTRAR DETALLE')
        } finally {
            ocultarAnimacion();
        }
    }

    function pintarMaestroDoc(documento) {
        document.querySelector('#info_documento').textContent = `${documento.serie}-${documento.correlativo}`;
        document.querySelector('#info_almacen_despacho').textContent = `${documento.almacen_despacho}`;
        document.querySelector('#info_sede_despacho').textContent = `${documento.sede_despacho}`;
    }

    function pintarDetallesDoc(detalles_doc_venta) {

        detallesDataTable.clear();

        detalles_doc_venta.forEach((ddc) => {
            let estadoBadge = '';

            if (ddc.estado === 'SEPARADO') {
                estadoBadge = `<span class="badge badge-warning">SEPARADO</span>`;
            } else if (ddc.estado === 'EN ESPERA') {
                estadoBadge = `<span class="badge badge-danger">EN ESPERA</span>`;
            } else {
                estadoBadge = `<span class="badge badge-success">${ddc.estado}</span>`;
            }

            detallesDataTable.row.add([
                ddc.nombre_modelo,
                ddc.nombre_producto,
                ddc.nombre_color,
                ddc.nombre_talla,
                estadoBadge,
                parseInt(ddc.cantidad),
                // parseInt(ddc.cantidad_cambiada),
                // parseInt(ddc.cantidad_sin_cambio)
            ]);
        });

        detallesDataTable.draw();
    }


    function imprimirEnvio(documento_id, despacho_id) {
        document.querySelector('#documento_id').value = documento_id;
        document.querySelector('#despacho_id').value = despacho_id;

        $('#modal-bultos').modal('show');

    }

    //========= RESERVAR =========
    function reservar(documento_id, despacho_id) {
        //======= OBTENER LOS DATOS DEL DESPACHO ======
        var miTabla = dtDespachos;

        const fila = miTabla.rows().data().filter(function(value, index) {
            return value['id'] == despacho_id;
        });

        let descripcion = ``;

        if (fila.length > 0) {
            const f = fila[0];
            descripcionHtml = `
                <div style="text-align:center; line-height:1.6">
                <div><i class="fas fa-map-marker-alt"></i> <strong>Destino:</strong> ${f.ubigeo}</div>
                <div><i class="fas fa-user"></i> <strong>Destinatario:</strong> ${f.destinatario_nombre}</div>
                <div><i class="fas fa-id-card"></i> <strong>Doc.:</strong> ${f.destinatario_nro_doc}</div>
                <div><i class="fas fa-file-invoice"></i> <strong>Venta:</strong> ${f.documento_nro}</div>
                </div>
            `;
        }

        //======== ALERTA =========
        Swal.fire({
            title: "Desea reservar el envío?",
            html: descripcionHtml,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, reservar!",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            preConfirm: async () => {
                const res = await setReserva(despacho_id, documento_id);
                return res;
            }
        }).then((result) => {
            if (result.value.success) {

                Swal.fire(result.value.message, descripcion, "success");
            } else {
                Swal.fire(`${result.value.message} - ${result.value.exception}`, descripcion, "error");
            }
        });
    }

    async function establecerFallado(despachoId) {
        try {

            const res = await axios.post(route('despachos.embalaje.setFallado'), {
                despachoId
            })

            if (res.data.success) {
                toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                dtDespachos.ajax.reload();
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN ESTABLECER ESTADO FALLADO');
        } finally {
            Swal.close();
        }
    }

    async function setReserva(despacho_id, documento_id) {
        try {

            const res = await axios.post(route('despachos.embalaje.setReserva'), {
                despacho_id,
                documento_id
            })

            if (res.data.success) {
                //======= PINTANDO ESTADO EN DATATABLE ======
                dtDespachos.ajax.reload();
            }

            return res.data;

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN EMBALAR');
        }
    }


    function despachar(documento_id, despacho_id) {
        //======= OBTENER LOS DATOS DEL DESPACHO ======
        var miTabla = dtDespachos;

        const fila = miTabla.rows().data().filter(function(value, index) {
            return value['id'] == despacho_id;
        });

        let descripcion = ``;

        if (fila.length > 0) {
            descripcion += `DESTINO: ${fila[0].ubigeo}
                                DESTINATARIO: ${fila[0].destinatario_nombre} - ${fila[0].destinatario_nro_doc}`;
        }

        //======== ALERTA =========
        Swal.fire({
            title: "Desea despachar el envío?",
            text: descripcion,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, despachar!",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            preConfirm: async () => {
                const res = await setDespacho(despacho_id, documento_id);
                return res;
            }
        }).then((result) => {
            if (result.value.success) {

                Swal.fire(result.value.message, descripcion, "success");
            } else {
                Swal.fire(`${result.value.message} - ${result.value.exception}`, descripcion, "error");
            }
        });
    }

    async function setDespacho(despacho_id, documento_id) {
        try {

            const res = await axios.post(route('despachos.embalaje.setDespacho'), {
                despacho_id,
                documento_id
            })

            if (res.data.success) {
                dtDespachos.ajax.reload(null, false);
            }

            return res.data;

        } catch (error) {

        }
    }

    function filtrarDespachoFechaInic(fecha_inicio) {

        const fi = document.querySelector('#filtroFechaInicio').value;
        const ff = document.querySelector('#filtroFechaFin').value;

        if ((fi.toString().trim().length > 0 && ff.toString().trim().length > 0) & (fi > ff)) {
            document.querySelector('#filtroFechaInicio').value = '';
            toastr.error('FECHA INICIO DEBE SER MENOR O IGUAL A FECHA FIN', 'ERROR FECHAS');
            dtDespachos.ajax.reload();

            return;
        }

        dtDespachos.ajax.reload();
    }

    function filtrarDespachoFechaFin(fecha_fin) {
        const fi = document.querySelector('#filtroFechaInicio').value;
        const ff = document.querySelector('#filtroFechaFin').value;

        if ((fi.toString().trim().length > 0 && ff.toString().trim().length > 0) & (ff < fi)) {
            document.querySelector('#filtroFechaFin').value = '';
            toastr.error('FECHA FIN DEBE SER MAYOR O IGUAL A FECHA INICIO', 'ERROR FECHAS');
            dtDespachos.ajax.reload();
            return;
        }

        dtDespachos.ajax.reload();
    }

    async function openMdlEditarEnvio(despachoId) {

        mostrarAnimacion();
        const despacho = await getDespachoById(despachoId);
        console.log('DESPACHO:', despacho);
        desactivarEventosSelectsMdlEnvio();
        await setDespachoEdit(despacho);
        ocultarAnimacion();

        parametrosMdlEnvioEdit.id = despachoId;
        $("#modal_envio").modal("show");
    }

    function desactivarEventosSelectsMdlEnvio() {
        document.querySelector('#departamento').onchange = null;
        document.querySelector('#provincia').onchange = null;
        document.querySelector('#distrito').onchange = null;
        document.querySelector('#tipo_envio').onchange = null;
        document.querySelector('#empresa_envio').onchange = null;
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

    async function setDespachoEdit(despacho) {

        window.departamentoSelect.setValue(parseInt(despacho.departamento_id), false);
        const provincias = await getProvincias(despacho.departamento_id);
        pintarProvincias(provincias, despacho.provincia_id);
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
        setOrigenVenta(despacho.origen_venta_id);
        setFechaEnvio(despacho.fecha_envio_propuesta);
        setObservaciones(despacho.obs_rotulo, despacho.obs_despacho);
        setDestinatario(despacho.destinatario_tipo_doc, despacho.destinatario_nro_doc, despacho
            .destinatario_nombre);

        activarEventosSelectsMdlEnvio();

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

    function setObservaciones(obs_rotulo, obs_despacho) {
        document.querySelector('#obs-rotulo').value = obs_rotulo;
        document.querySelector('#obs-despacho').value = obs_despacho;
    }

    function setFechaEnvio(fecha_envio_propuesta) {
        document.querySelector('#fecha_envio').value = fecha_envio_propuesta;
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

    function setOrigenVenta(origen_venta_id) {
        if (window.origenVentaSelect) {
            window.origenVentaSelect.setValue(origen_venta_id, true);
            document.getElementById('origen_venta').dispatchEvent(new Event('change'));
        }
    }

    async function getDespachoById(despachoId) {
        try {

            const res = await axios.get(route('despachos.embalaje.getDespachoById', {
                id: despachoId
            }));
            if (!res.data.success) {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                return null;
            }

            toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            return res.data.data;

        } catch (error) {
            toastr.error('ERROR EN LA PETICIÓN OBTENER DESPACHO');
            return null;
        }
    }

    function setFallado(despachoId) {

        const fila = getRowById(dtDespachos, despachoId);
        let descripcionHtml = ``;

        descripcionHtml = `
                <div style="text-align:center; line-height:1.6">
                <div><i class="fas fa-map-marker-alt"></i> <strong>Destino:</strong> ${fila.ubigeo}</div>
                <div><i class="fas fa-user"></i> <strong>Destinatario:</strong> ${fila.destinatario_nombre}</div>
                <div><i class="fas fa-id-card"></i> <strong>Doc.:</strong> ${fila.destinatario_nro_doc}</div>
                <div><i class="fas fa-file-invoice"></i> <strong>Venta:</strong> ${fila.documento_nro}</div>
                </div>
            `;


        //======== ALERTA =========
        Swal.fire({
            title: '¿Desea establecer el envío con fallas?',
            html: descripcionHtml,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'No, cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Estableciendo estado Fallado...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const res = establecerFallado(despachoId);

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: 'OPERACIÓN CANCELADA',
                    icon: 'info',
                    confirmButtonColor: '#007bff'
                });
            }
        });
    }

    function setRevision(despachoId) {

        const fila = getRowById(dtDespachos, despachoId);
        let descripcionHtml = ``;

        descripcionHtml = `
                <div style="text-align:center; line-height:1.6">
                <div><i class="fas fa-map-marker-alt"></i> <strong>Destino:</strong> ${fila.ubigeo}</div>
                <div><i class="fas fa-user"></i> <strong>Destinatario:</strong> ${fila.destinatario_nombre}</div>
                <div><i class="fas fa-id-card"></i> <strong>Doc.:</strong> ${fila.destinatario_nro_doc}</div>
                <div><i class="fas fa-file-invoice"></i> <strong>Venta:</strong> ${fila.documento_nro}</div>
                </div>
            `;

        //======== ALERTA =========
        Swal.fire({
            title: '¿Desea establecer el envío en revision?',
            html: descripcionHtml,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'No, cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Estableciendo estado en revisión...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const res = establecerEstadoRevision(despachoId);

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: 'OPERACIÓN CANCELADA',
                    icon: 'info',
                    confirmButtonColor: '#007bff'
                });
            }
        });
    }

    async function establecerEstadoRevision(despachoId) {
        try {

            const res = await axios.post(route('despachos.embalaje.setRevision'), {
                despachoId
            })

            if (res.data.success) {
                toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                dtDespachos.ajax.reload();
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN ESTABLECER ESTADO REVISIÓN');
        } finally {
            Swal.close();
        }
    }

    function downloadExcel() {

        toastr.clear();

        const url = @json(route('despachos.embalaje.getExcel'));

        const params = {
                filtroModo: document.querySelector('#filtroModo').value,
                filtroCliente: document.querySelector('#filtroCliente').value,
                filtroFechaInicio: document.querySelector('#filtroFechaInicio').value,
                filtroFechaFin: document.querySelector('#filtroFechaFin').value,
            };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.location.href = finalUrl;

    }

    function downloadPdf() {

        toastr.clear();

        const url = @json(route('despachos.embalaje.getPdf'));

        const params = {
            filtroModo: document.querySelector('#filtroModo').value,
            filtroCliente: document.querySelector('#filtroCliente').value,
            filtroFechaInicio: document.querySelector('#filtroFechaInicio').value,
            filtroFechaFin: document.querySelector('#filtroFechaFin').value,
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.open(finalUrl, '_blank');

    }
</script>
@endpush
