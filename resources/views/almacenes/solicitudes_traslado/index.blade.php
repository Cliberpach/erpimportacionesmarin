@extends('layout')
@section('content')

@section('almacenes-active', 'active')
@section('solicitudes_traslado-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>Solicitudes de traslado</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Solicitudes de traslado</strong>
            </li>
        </ol>
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
                                name="filtroProducto" id="filtroProducto" required>
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
                                @include('almacenes.solicitudes_traslado.tables.tbl_sol_tr_list')
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
    let dtSolicitudesTraslado = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDTSolicitudesTraslado();
        iniciarSelect2();
    })

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

    function iniciarDTSolicitudesTraslado() {
        const urlGetSolicitudesTraslado = '{{ route('almacenes.solicitud_traslado.getSolicitudesTraslado') }}';

        dtSolicitudesTraslado = new DataTable('#tbl_sol_tr_list', {
            serverSide: true,
            processing: true,
            responsive: true,
            ajax: {
                url: urlGetSolicitudesTraslado,
                type: 'GET',
                data: function(d) {
                    d.estado = $('#estado').val();
                    d.producto_id = $('#filtroProducto').val();
                }
            },
            columns: [{
                    data: 'simbolo',
                    className: "text-center",
                    render: function(data, type, row) {
                        return `<div style="width:100px;">
                                <p style="margin:0;padding:0;font-weight:bold;">${data}</p>
                            </div>`;
                    }
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
                    data: 'venta_serie',
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
                    render: function(data, type, row) {

                        let urlConfirmar =
                            `{{ route('almacenes.solicitud_traslado.confirmarShow', ['id' => ':id']) }}`;
                        urlConfirmar = urlConfirmar.replace(':id', data.id);

                        let urlEntregar =
                            `{{ route('almacenes.solicitud_traslado.entregarShow', ['id' => ':id']) }}`;
                        urlEntregar = urlEntregar.replace(':id', data.id);

                        let urlVer =
                            `{{ route('almacenes.solicitud_traslado.show', ['id' => ':id']) }}`;
                        urlVer = urlVer.replace(':id', data.id);

                        let url_etiquetas =
                            '{{ route('almacenes.solicitud_traslado.generarEtiquetas', ':id') }}';
                        url_etiquetas = url_etiquetas.replace(':id', data.id);

                        let urlPdfOne = '{{ route('almacenes.solicitud_traslado.pdfOne', ':id') }}';
                        urlPdfOne = urlPdfOne.replace(':id', data.id);

                        let acciones = `<div class='btn-group' style='text-transform:capitalize;'>
                                            <button data-toggle='dropdown' class='btn btn-success btn-sm dropdown-toggle'>
                                            <i class='fa fa-bars'></i>
                                            </button>
                                            <ul class='dropdown-menu'>
                                            <li>
                                                <a class="dropdown-item" href="${urlPdfOne}" title="PDF" target="_blank">
                                                    <b><i class="fa fa-file-pdf text-danger"></i> PDF</b>
                                                </a>
                                            </li>`;

                        if (data.estado === 'ENVIADO') {
                            acciones += `<li>
                                        <a class='dropdown-item' href='${urlConfirmar}' title='Confirmar'>
                                        <b><i class="fa fa-check"></i> Confirmar</b>
                                        </a>
                                    </li>`;
                        }

                        if (data.estado === 'RECIBIDO') {
                            acciones += `<li>
                                        <a class='dropdown-item' href='${urlEntregar}' title='Entregar'>
                                        <b><i class="fas fa-handshake"></i> Entregar</b>
                                        </a>
                                    </li>`;
                        }

                        acciones += `
                                    <li>
                                        <a class='dropdown-item' href='${urlVer}' title='Ver'>
                                        <b><i class="fa fa-eye"></i> Ver</b>
                                        </a>
                                    </li>
                                    <li>
                                         <a class="dropdown-item" href="${url_etiquetas}" target="_blank" id="adhesivo_${data.id}">
                                            <i class="fa fa-barcode"></i> GENERAR ETIQUETAS
                                        </a>
                                    </li>
                                    </ul>
                                    </div>`;

                        return acciones;
                    },
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar:",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "emptyTable": "No hay datos disponibles en la tabla",
                "aria": {
                    "sortAscending": ": activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

    function filterDataTable() {
        dtSolicitudesTraslado.ajax.reload();
    }

    function downloadExcel() {

        toastr.clear();
        const res = validarFiltros();
        if (!res) return;

        const url = @json(route('almacenes.solicitud_traslado.excel'));

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

        const url = @json(route('almacenes.solicitud_traslado.pdf'));

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
