@extends('layout')
@section('content')
    @include('despachos.reparto_detalle.modals.mdl_bultos')

    {{-- @include('ventas.despachos.modal-detalles-doc')
    @include('ventas.documentos.mdl_envio_edit') --}}

@section('despachos-active', 'active')
@section('reparto_detalle-active', 'active')

<style>
    .fila-pendiente {
        background-color: #fff0f0 !important;
        /* rojito leve */
    }

    .fila-reservado {
        background-color: #f7f0ff !important;
        /* moradito leve */
    }

    .fila-despachado {
        background-color: #f0faff !important;
        /* celestito leve */
    }

    .icono-pendiente {
        color: #f8b3b3;
    }

    .icono-reservado {
        color: #d8b3ff;
    }

    .icono-despachado {
        color: #b3e5ff;
    }
</style>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>REPARTO DETALLE</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Despachos</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2 col-md-2 text-right d-flex align-items-center justify-content-end">
        <a href="{{ route('despachos.reparto.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo
        </a>
    </div>
</div>


<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row mb-5">

                        {{-- <div class="col-3">
                            <label for="filtroEstado" style="font-weight: bold;">ESTADO:</label>
                            <select id="filtroEstado" class="form-control select2_form"
                                onchange="dtRepartos.ajax.reload();">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="DESPACHADO">DESPACHADO</option>
                            </select>
                        </div> --}}

                        <div class="col-3">
                            <label for="filtroCliente" style="font-weight: bold;">CLIENTE:</label>
                            <select class="select2_form" style="text-transform: uppercase; width:100%"
                                name="filtroCliente" id="filtroCliente" required onchange="dtRepartos.ajax.reload();">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-3">
                            <label for="filtroFechaInicio" style="font-weight: bold;">FEC INICIO:</label>
                            <input value="<?php echo date('Y-m-d'); ?>" type="date" class="form form-control"
                                id="filtroFechaInicio" onchange="filtrarDespachoFechaInic()">
                        </div>
                        <div class="col-3">
                            <label for="filtroFechaFin" style="font-weight: bold;">FEC FIN:</label>
                            <input value="<?php echo date('Y-m-d'); ?>" type="date" class="form form-control"
                                id="filtroFechaFin" onchange="filtrarDespachoFechaFin()">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto d-flex align-items-center mr-3">
                            <i class="fa fa-square icono-pendiente"></i>
                            <span class="ml-2">PENDIENTE</span>
                        </div>

                        <div class="col-auto d-flex align-items-center">
                            <i class="fa fa-square icono-despachado"></i>
                            <span class="ml-2">DESPACHADO</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">

                                @include('despachos.reparto_detalle.tables.tbl_list_rd')

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
    let dtRepartos = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDtRepartos();
        events();
        iniciarSelect2();
        //iniciarSelectsMdlEnvio();
        //detallesDataTable = dataTableDetalles();
    })

    function events() {
        eventsModalBultos();
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

    function iniciarDtRepartos() {

        dtRepartos = new DataTable('#tbl_list_rd', {
            responsive: true,
            "serverSide": true,
            initComplete: function() {
                $('.dt-search').append(`
                <div class="text-muted small mt-1">
                    <strong>Buscar por: Paquete,Reparto</strong>
                </div>
                `);
            },
            rowCallback: function(row, data, index) {
                let estado = data.estado;

                if (estado === 'PENDIENTE') {
                    $(row).addClass('fila-pendiente');
                } else if (estado === 'RESERVADO') {
                    $(row).addClass('fila-reservado');
                } else if (estado === 'DESPACHADO') {
                    $(row).addClass('fila-despachado');
                }
            },
            "ajax": {
                "url": "{{ route('despachos.reparto_detalle.getRepartoDetalle') }}",
                "type": "GET",
                "beforeSend": function() {
                    mostrarAnimacion();
                },
                "data": function(d) {
                    d.fecha_inicio = $('#filtroFechaInicio').val();
                    d.fecha_fin = $('#filtroFechaFin').val();
                    d.estado = $('#filtroEstado').val();
                    d.cliente_id = $('#filtroCliente').val();
                },
                "complete": function() {
                    ocultarAnimacion();
                }
            },
            order: [
                [0, 'desc']
            ],
            "columns": [{
                    data: 'id',
                    name: 'p.id',
                    searchable: false,
                    className: "text-center letrapequeña"
                },
                {
                    data: 'qr_codigo',
                    name: 'p.qr_codigo',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'reparto_codigo',
                    name: 'r.codigo',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'departamento',
                    name: 'p.departamento',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'provincia',
                    name: 'p.provincia',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'distrito',
                    name: 'p.distrito',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'cliente_nombre',
                    name: 'p.cliente_nombre',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'cliente_celular',
                    name: 'p.cliente_celular',
                    className: "text-center letrapequeña"
                },
                {
                    data: 'tipo_pago_envio',
                    name: 'p.tipo_pago_envio',
                    className: "text-center letrapequeña"
                },
                {
                    data: 'empresa_envio_nombre',
                    name: 'p.empresa_envio_nombre',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'sede_envio_nombre',
                    name: 'p.sede_envio_nombre',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'destinatario_tipo_doc',
                    name: 'p.destinatario_tipo_doc',
                    className: "text-center letrapequeña"
                },
                {
                    data: 'destinatario_nro_doc',
                    name: 'p.destinatario_nro_doc',
                    className: "text-center letrapequeña"
                },
                {
                    data: 'destinatario_nombre',
                    name: 'p.destinatario_nombre',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'entrega_domicilio',
                    name: 'p.entrega_domicilio',
                    className: "text-center letrapequeña"
                },
                {
                    data: 'direccion_entrega',
                    name: 'p.direccion_entrega',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'usuario_embalaje',
                    name: 'p.registrador_nombre',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'usuario_reparto',
                    name: 'r.registrador_nombre',
                    className: "text-left letrapequeña"
                },
                {
                    searchable: false,
                    data: 'ventas',
                    className: "text-left letrapequeña"
                },
                {
                    searchable: false,
                    data: 'fecha_embalaje',
                    className: "text-left letrapequeña"
                },
                {
                    searchable: false,
                    data: 'fecha_reparto',
                    className: "text-left letrapequeña"
                },
                 {
                    searchable: false,
                    data: 'guia_serie',
                    className: "text-left letrapequeña"
                },
                {
                    data: 'estado',
                    name: 'p.estado',
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
                    className: "text-center",
                    render: function(data) {

                        let acciones = `
                            <div class='btn-group' style='text-transform:capitalize;'>
                                <button data-toggle='dropdown' class='btn btn-success btn-sm dropdown-toggle'>
                                    <i class='fa fa-bars'></i>
                                </button>
                                <ul class='dropdown-menu'>
                                    <li>
                                        <a class='dropdown-item' href='javascript:void(0);' onclick="openMdlBultos(${data.id})" >
                                            <b><i class='fa fa-file-pdf'></i> ADHESIVO</b>
                                        </a>
                                    </li>
                        `;

                        if (data.guia_id) {
                            acciones += `
                                    <li>
                                        <a class='dropdown-item' onclick='pdfGuia(${data.guia_id})' title='PDF Guía'>
                                            <b><i class='fa fa-truck'></i> PDF GUÍA</b>
                                        </a>
                                    </li>
                            `;
                        }

                        acciones += `
                                </ul>
                            </div>
                        `;

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
            const res = await axios.get(route('despachos.reparto.show', documento_id));

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
            detallesDataTable.row.add([
                ddc.nombre_modelo,
                ddc.nombre_producto,
                ddc.nombre_color,
                ddc.nombre_talla,
                parseInt(ddc.cantidad),
                parseInt(ddc.cantidad_cambiada),
                parseInt(ddc.cantidad_sin_cambio)
            ]);
        });

        detallesDataTable.draw();

    }

    //========= RESERVAR =========
    function reservar(documento_id, despacho_id) {
        //======= OBTENER LOS DATOS DEL DESPACHO ======
        var miTabla = dtRepartos;

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
            title: "Desea reservar el envío?",
            text: descripcion,
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


    async function setEmbalaje(despacho_id, documento_id) {
        try {

            const res = await axios.post(route('ventas.despachos.setEmbalaje'), {
                despacho_id,
                documento_id
            })

            if (res.data.success) {
                //======= PINTANDO ESTADO EN DATATABLE ======
                dtRepartos.ajax.reload();
            }

            return res.data;

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN EMBALAR');
        }
    }

    async function setReserva(despacho_id, documento_id) {
        try {

            const res = await axios.post(route('ventas.despachos.setReserva'), {
                despacho_id,
                documento_id
            })

            if (res.data.success) {
                //======= PINTANDO ESTADO EN DATATABLE ======
                dtRepartos.ajax.reload();
            }

            return res.data;

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN EMBALAR');
        }
    }


    function despachar(documento_id, despacho_id) {
        //======= OBTENER LOS DATOS DEL DESPACHO ======
        var miTabla = dtRepartos;

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

    function embalar(documento_id, despacho_id) {
        //======= OBTENER LOS DATOS DEL DESPACHO ======
        var miTabla = dtRepartos;

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
            title: "Desea embalar el envío?",
            text: descripcion,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, embalar!",
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            preConfirm: async () => {
                const res = await setEmbalaje(despacho_id, documento_id);
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

            const res = await axios.post(route('ventas.despachos.setDespacho'), {
                despacho_id,
                documento_id
            })

            if (res.data.success) {
                dtRepartos.ajax.reload(null, false);
                //======= PINTANDO ESTADO EN DATATABLE ======

                // const fila          =   dtRepartos.row((idx,data) => data['id'] == despacho_id);
                // const indiceFila    =   dtRepartos.row((idx,data) => data['id'] == despacho_id).index();
                // await fila.cell(indiceFila,0).data('DESPACHADO').draw();
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
            dtRepartos.ajax.reload();

            return;
        }

        dtRepartos.ajax.reload();
    }

    function filtrarDespachoFechaFin(fecha_fin) {
        const fi = document.querySelector('#filtroFechaInicio').value;
        const ff = document.querySelector('#filtroFechaFin').value;

        if ((fi.toString().trim().length > 0 && ff.toString().trim().length > 0) & (ff < fi)) {
            document.querySelector('#filtroFechaFin').value = '';
            toastr.error('FECHA FIN DEBE SER MAYOR O IGUAL A FECHA INICIO', 'ERROR FECHAS');
            dtRepartos.ajax.reload();
            return;
        }

        dtRepartos.ajax.reload();
    }

    async function openMdlEditarEnvio(ventaId, despachoId) {

        mostrarAnimacion();
        const despacho = await getDespachoById(despachoId);
        await setDespachoEdit(despacho);
        ocultarAnimacion();

        parametrosMdlEnvioEdit.id = despachoId;
        $("#modal_envio").modal("show");
    }

    function setDespachoEdit(despacho) {
        console.log('SET DESPACHO EDIT');
        return setDepartamento(despacho.departamento_id)
            .then(() => setProvincia(despacho.provincia_id))
            .then(() => setDistrito(despacho.distrito_id))
            .then(() => setTipoEnvio(despacho.tipo_envio_id))
            .then(() => setEmpresaEnvio(despacho.empresa_envio_id))
            .then(() => setSedeEnvio(despacho.sede_envio_id))
            .then(() => setTipoPagoEnvio(despacho.tipo_pago_envio_id))
            .then(() => {
                setEntregaDomicilio(despacho.entrega_domicilio);
                setDireccionEntrega(despacho.direccion_entrega);
                setOrigenVenta(despacho.origen_venta_id);
                setFechaEnvio(despacho.fecha_envio_propuesta);
                setObservaciones(despacho.obs_rotulo, despacho.obs_despacho);
                setDestinatario(despacho.destinatario_tipo_doc, despacho.destinatario_nro_doc, despacho
                    .destinatario_nombre);
            });
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

    function setDepartamento(id) {
        console.log('SET DEPARTAMENTO');
        const _id = parseInt(id).toString();
        return new Promise(resolve => {
            window.departamentoSelect.setValue(_id, true);
            document.getElementById('departamento').dispatchEvent(new Event('change'));
            $(document).one("provinciasCargadas", resolve);
        });
    }

    function setProvincia(id) {
        console.log('SET PROVINCIA');
        const _id = parseInt(id).toString();
        return new Promise(resolve => {
            window.provinciaSelect.setValue(_id, true);
            document.getElementById('provincia').dispatchEvent(new Event('change'));
            $(document).one("distritosCargados", resolve);
        });
    }

    function setDistrito(id) {
        console.log('SET DISTRITO');
        console.log('DISTRITO ID:', id);
        const _id = parseInt(id);
        console.log('DISTRITO ID PARSED:', _id);
        return new Promise(resolve => {
            //window.distritoSelect.setValue('10403')
            //window.distritoSelect.setValue(_id, false);
            resolve();
        });
    }

    function setTipoEnvio(id) {
        return new Promise(resolve => {
            window.tipoEnvioSelect.setValue(id, true);
            document.getElementById('tipo_envio').dispatchEvent(new Event('change'));
            $(document).one("empresasEnvioCargadas", resolve);
        });
    }

    function setEmpresaEnvio(id) {
        return new Promise(resolve => {
            window.empresaEnvioSelect.setValue(id, true);
            document.getElementById('empresa_envio').dispatchEvent(new Event('change'));
            resolve();
        });
    }

    function setSedeEnvio(sedeId) {
        return new Promise(resolve => {
            document.addEventListener("sedeEnvioCargada", () => {
                window.sedeEnvioSelect.setValue(sedeId, true);
                document.getElementById('sede_envio').dispatchEvent(new Event('change'));
                resolve();
            }, {
                once: true
            });
        });
    }

    function setTipoPagoEnvio(id) {
        return new Promise(resolve => {
            window.tipoPagoEnvioSelect.setValue(id, true);
            document.getElementById('tipo_pago_envio').dispatchEvent(new Event('change'));
            resolve();
        });
    }

    async function getDespachoById(despachoId) {
        try {

            const res = await axios.get(route('ventas.despachos.getDespachoById', {
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

    function pdfGuia(id) {
        let url = '{{ route('ventas.guiasremision.show', ':id') }}';
        url = url.replace(':id', id);

        window.open(url, '_blank');
    }
</script>
@endpush
