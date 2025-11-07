@extends('layout')
@section('content')

@section('almacenes-active', 'active')
@section('traslados-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>Lista de Traslados</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Traslados</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2 col-md-2">
        <a class="btn btn-block btn-w-m btn-success m-t-md" href="{{ route('almacenes.traslados.create') }}">
            <i class="fa fa-plus-square"></i> NUEVO
        </a>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">

                    <div class="row mb-3">
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <label for="estado" style="font-weight:bold;">ESTADO</label>
                            <select data-placeholder="Seleccionar" id="estado" class="form-control select2_form"
                                aria-label="Default select example">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="ENVIADO">ENVIADO</option>
                                <option value="RECIBIDO">RECIBIDO</option>
                                <option value="ENTREGADO">ENTREGADO</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-2">
                            <label for="filtroProducto" style="font-weight: bold;">PRODUCTO:</label>
                            <select class="select2_form" style="text-transform: uppercase; width:100%"
                                name="filtroProducto" id="filtroProducto" required
                                onchange="dtTraslados.ajax.reload();">
                            </select>
                        </div>

                        <div class="col-lg-6 col-md-3 col-sm-12 col-xs-12" style="text-align: end;margin-top:auto;">
                            <button class="btn btn-success btnFiltrar" onclick="filterDataTable()"><i
                                    class="fas fa-search"></i>
                                FILTRAR</button>
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
                        <div class="col-12">
                            <div class="table-responsive">
                                @include('almacenes.traslados.tables.tbl_traslados_index')
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
@endpush

@push('scripts')
<script>
    let dtTraslados = null;
    document.addEventListener('DOMContentLoaded', () => {
        iniciarDtTraslados();
        iniciarSelect2();
        events();
    })

    function events() {

    }

    function iniciarSelect2() {
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            width: '100%',
        });

        $('#filtroProducto').select2({
            width: '100%',
            placeholder: "Buscar Producto...",
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
                url: '{{ route('utilidades.getProductos') }}',
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
                        const productos = data.productos;
                        return {
                            results: productos.map(item => ({
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

    function iniciarDtTraslados() {
        dtTraslados = new DataTable('.dataTables-traslados', {
            "buttons": [{
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Excel',
                    title: 'Tablas Generales'
                },
                {
                    titleAttr: 'Imprimir',
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Imprimir',
                    customize: function(win) {
                        $(win.document.body).addClass('white-bg');
                        $(win.document.body).css('font-size', '10px');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                }
            ],
            "responsive": true,
            "processing": true,
            ajax: {
                url: "{{ route('almacenes.traslados.getTraslados') }}",
                type: "GET",
                data: function(d) {
                    d.estado = $('#estado').val();
                    d.producto_id = $('#filtroProducto').val();
                }
            },
            "order": [
                [0, 'desc']
            ],
            "columns": [{
                    data: 'id',
                    className: "text-center",
                    visible: false
                },
                {
                    data: 'simbolo',
                    className: "text-center",
                    render: function(data, type, row) {
                        return `<div style="width:100px;">
                                <p style="margin:0;padding:0;font-weight:bold;">${data}</p>
                            </div>`;
                    }
                },
                {
                    data: 'venta_serie',
                    className: "text-center"
                },
                {
                    data: 'guia',
                    className: "text-center"
                },
                {
                    data: 'almacen_origen_nombre',
                    className: "text-center"
                },
                {
                    data: 'almacen_destino_nombre',
                    className: "text-center"
                },
                {
                    data: 'sede_origen_direccion',
                    className: "text-center"
                },
                {
                    data: 'sede_destino_direccion',
                    className: "text-center"
                },
                {
                    data: 'observacion',
                    className: "text-center"
                },
                {
                    data: 'fecha_registro',
                    className: "text-center"
                },
                {
                    data: 'fecha_traslado',
                    className: "text-center"
                },
                {
                    data: 'registrador_nombre',
                    className: "text-center"
                },
                {
                    data: 'estado',
                    className: "text-center",
                    render: function(data, type, row) {
                        if (data === 'PENDIENTE') {
                            return '<span class="badge badge-danger">PENDIENTE</span>';
                        } else if (data === 'ENVIADO') {
                            return '<span class="badge badge-primary">ENVIADO</span>';
                        } else if (data === 'RECIBIDO') {
                            return '<span class="badge badge-success">RECIBIDO</span>';
                        } else if (data === 'ENTREGADO') {
                            return '<span class="badge badge-info">ENTREGADO</span>';
                        }

                        return data;
                    }
                },
                {
                    data: null,
                    className: "text-center",
                    render: function(data) {

                        let url_detalles = '{{ route('almacenes.traslados.show', ':id') }}';
                        url_detalles = url_detalles.replace(':id', data.id);

                        let urlPdfOne = '{{ route('almacenes.traslados.pdfOne', ':id') }}';
                        urlPdfOne = urlPdfOne.replace(':id', data.id);

                        let acciones = `<div class='btn-group' style='text-transform:capitalize;'>
                                        <button data-toggle='dropdown' class='btn btn-success btn-sm dropdown-toggle'>
                                        <i class='fa fa-bars'></i>
                                        </button>
                                        <ul class='dropdown-menu'>
                                            <li>
                                                <a class='dropdown-item' href='${url_detalles}' title='Detalles'>
                                                <b><i class='fa fa-eye'></i> Detalles</b>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="${urlPdfOne}" title="PDF" target="_blank">
                                                    <b><i class="fa fa-file-pdf text-danger"></i> PDF</b>
                                                </a>
                                            </li>`;

                        if (data.estado == 'PENDIENTE') {
                            acciones += `<li>
                                                <a class='dropdown-item' href='javascript:void(0)' onclick="enviarTraslado(${data.id})" title='Detalles'>
                                                <b><i class='fa fa-paper-plane'></i> Enviar</b>
                                                </a>
                                            </li>`;
                        }

                        /*if(!data.guia_id){
                            acciones        +=  ` <li>
                                                    <a class='dropdown-item' href='javascript:void(0);' title='Guía Remisión' onclick='generarGuia(${data.id})'>
                                                        <b><i class='fa fa-file-pdf-o'></i> Guía Remisión</b>
                                                    </a>
                                                </li>`;
                        }*/

                        acciones += `</ul></div>`;

                        return acciones;
                    }
                }

            ],
            "language": {
                "url": "{{ asset('Spanish.json') }}"
            },
            "order": [
                [0, "desc"]
            ],
        });
    }

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger',
        },
        buttonsStyling: false
    })

    function comprobante(id) {
        var url = '{{ route('almacenes.nota_salidad.getPdf', ':id') }}';
        url = url.replace(':id', id + '-100');
        window.open(url, "Comprobante SISCOM", "width=900, height=600")
    }

    function generarGuia(id) {
        Swal.fire({
            title: 'Desea generar una guía de remisión',
            text: "Será redirigido a un formulario de guía de remisión",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {

                //==== RUTA GUÍA REMISIÓN ====
                let guia_create = '{{ route('almacenes.traslados.generarGuiaCreate', ':id') }}';
                guia_create = guia_create.replace(':id', id);

                window.location.href = guia_create;

            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'La Solicitud se ha cancelado.',
                    'error'
                )
            }
        })
    }

    function enviarTraslado(id) {

        const traslado = getRowById(dtTraslados, id);
        const simbolo = traslado.simbolo;
        const origen = traslado.almacen_origen_nombre;
        const destino = traslado.almacen_destino_nombre;

        const message = `
            <div style="text-align:center; font-size:15px; color:#555;">
                <div style="margin-bottom:10px; font-size:17px;">
                    <i class="fas fa-exchange-alt" style="color:#1ab394; font-size:22px;"></i>
                    <span style="font-weight:600;"> ${simbolo}</span>
                </div>

                <div style="margin:8px 0;">
                    <i class="fas fa-warehouse" style="color:#3498db;"></i>
                    <span style="margin-left:5px;">Desde:</span>
                    <span style="font-weight:600;">${origen}</span>
                </div>

                <div style="margin:8px 0;">
                    <i class="fas fa-truck-loading" style="color:#e67e22;"></i>
                    <span style="margin-left:5px;">Hacia:</span>
                    <span style="font-weight:600;">${destino}</span>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Desea enviar el Traslado?',
            html: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: "#1ab394",
            confirmButtonText: 'Si, Confirmar',
            cancelButtonText: "No, Cancelar",
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Marcando como enviado...',
                    html: 'Por favor, espere un momento.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    const formData = new FormData();
                    formData.append('id', id);

                    const res = await axios.post(route('almacenes.traslados.setEnviado'), formData);
                    if (res.data.success) {
                        dtTraslados.ajax.reload(null, false);
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ENVIAR TRASLADO');
                } finally {
                    Swal.close();
                }


            } else if (
                /* Read more about handling dismissals below */
                result.dismiss === Swal.DismissReason.cancel
            ) {
                swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'La Solicitud se ha cancelado.',
                    'error'
                )
            }
        })
    }

    function filterDataTable() {
        dtTraslados.ajax.reload();
    }

    function downloadExcel() {

        toastr.clear();
        const res = validarFiltros();
        if (!res) return;

        const url = @json(route('almacenes.traslados.excel'));

        const params = {
            estado: document.querySelector('#estado').value,
            // fecha_inicio: document.querySelector('#fecha_inicio').value,
            // fecha_fin: document.querySelector('#fecha_fin').value,
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.location.href = finalUrl;

    }

    function downloadPdf() {

        toastr.clear();
        const res = validarFiltros();
        if (!res) return;

        const url = @json(route('almacenes.traslados.pdf'));

        const params = {
            estado: document.querySelector('#estado').value,
            // fecha_inicio: document.querySelector('#fecha_inicio').value,
            // fecha_fin: document.querySelector('#fecha_fin').value,
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.open(finalUrl, '_blank');

    }

    function validarFiltros() {
        return true;
    }
</script>
@endpush
