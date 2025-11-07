@extends('layout')
@section('content')

@section('consulta-active', 'active')
@section('consulta-comprobantes-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>Listado de Documentos</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Documentos</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
                    <div class="form-group">
                        <label for="filtroCliente" style="font-weight: bold;">CLIENTE</label>
                        <select class="select2_form" style="text-transform: uppercase; width:100%" name="filtroCliente"
                            id="filtroCliente" required>
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="filtroUsuario" style="font-weight: bold;">USUARIO</label>
                        <select name="filtroUsuario" id="filtroUsuario" class="select2_form form-control"
                            data-placeholder="SELECCIONAR">
                            <option value=""></option>
                            @foreach ($usuarios as $user)
                                <option value="{{ $user->id }}">{{ $user->usuario }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="filtroFechaInicio" style="font-weight: bold;">FECHA INICIO</label>
                        <input type="date" id="filtroFechaInicio" class="form-control">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="filtroFechaFin" style="font-weight: bold;">FECHA FIN</label>
                        <input type="date" id="filtroFechaFin" class="form-control">
                    </div>
                </div>
                <div class="col-12 col-md-1">
                    <div class="form-group">
                        <button class="btn btn-success btn-block" onclick="filtrarDocumentos()"><i
                                class="fa fa-refresh"></i></button>
                    </div>
                </div>
                <div class="col-12 col-md-1">
                    <div class="form-group">
                        <button class="btn btn-primary btn-block" onclick="downloadExcel()"><i
                                class="fa fa-file-excel-o"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="table-responsive">
                        @include('consultas.documentos.tables.tbl_documentos_list')
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
    let dtRVentas = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDtRVentas();
        iniciarSelect2();
    })

    function iniciarDtRVentas() {
        const url = '{{ route('consultas.documento.getTable') }}';

        dtRVentas = new DataTable('#tbl_documentos_list', {
            serverSide: true,
            processing: true,
            "order": [
                [0, 'desc']
            ],
            ajax: {
                url: url,
                type: 'GET',
                data: function(d) {
                    d.cliente_id    =   document.querySelector('#filtroCliente').value;
                    d.usuario_id    =   document.querySelector('#filtroUsuario').value;
                    d.fecha_inicio  =   document.querySelector('#filtroFechaInicio').value;
                    d.fecha_fin     =   document.querySelector('#filtroFechaFin').value;
                }
            },
            columns: [{
                    data: 'documento',
                    name: 'documento'
                },
                {
                    searchable: false,
                    data: 'cliente',
                    name: 'cd.cliente'
                },
                {
                    searchable: false,
                    data: 'pedido',
                    name: 'pedido'
                },
                {
                    searchable: false,
                    data: 'total_pagar',
                    name: 'cd.total_pagar'
                },
                {
                    searchable: false,
                    data: 'saldo',
                    name: 'cc.saldo'
                },
                {
                    searchable: false,
                    data: 'registrador_nombre',
                    name: 'cd.registrador_nombre'
                },
                {
                    data: 'fecha_registro',
                    name: 'cd.fecha_registro',
                    searchable: false
                },
                {
                    data: 'estado_despacho',
                    name: 'cd.estado_despacho',
                    searchable: false
                },
                {
                    data: 'usuario_embalaje',
                    name: 'ev.usuario_embalaje',
                    searchable: false
                },
                {
                    data: 'usuario_reparto',
                    name: 'ev.usuario_reparto',
                    searchable: false
                },
                {
                    data: 'fecha_embalaje',
                    name: 'ev.fecha_embalaje',
                    searchable: false,
                    className: 'text-end'
                },
                {
                    data: 'fecha_reparto',
                    name: 'ev.fecha_reparto',
                    searchable: false,
                    className: 'text-end'
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "search": "Buscar DOC:",
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

    function iniciarSelect2() {
        $('.select2_form').select2({
            width: "100%",
            placeholder: $(this).data('placeholder'),
            allowClear: true
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

    function filterDataTable() {
        dtRVentas.ajax.reload();
    }

    function changeDateStart(date_start) {

        toastr.clear();
        const date_end = document.querySelector('#fecha_fin').value;

        if (date_start > date_end && date_end) {
            document.querySelector('#fecha_inicio').value = '';
            toastr.error('LA FECHA DE INICIO DEBE SER MENOR IGUAL A LA FECHA FINAL!!');
            return;
        }

        filterDataTable();

    }

    function changeDateEnd(date_end) {

        toastr.clear();
        const date_start = document.querySelector('#fecha_inicio').value;

        if (date_end < date_start && date_start) {
            document.querySelector('#fecha_fin').value = '';
            toastr.error('LA FECHA FINAL DEBE SER MAYOR IGUAL A LA FECHA INICIAL!!');
            return;
        }

        filterDataTable();

    }

    function downloadExcel() {

        const url = @json(route('consultas.documento.getExcel'));
        const params = {
            fecha_inicio: document.querySelector('#filtroFechaInicio').value,
            fecha_fin: document.querySelector('#filtroFechaFin').value,
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.location.href = finalUrl;

    }

    function downloadPdf() {

        const url = null;

        const params = {
            fecha_inicio: document.querySelector('#fecha_inicio').value,
            fecha_fin: document.querySelector('#fecha_fin').value
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.open(finalUrl, '_blank');

    }

    function filtrarDocumentos() {

        toastr.clear();
        const fecha_inicio = document.querySelector('#filtroFechaInicio').value;
        const fecha_fin = document.querySelector('#filtroFechaFin').value;

        if (fecha_inicio > fecha_fin && fecha_fin && fecha_inicio) {
            toastr.error('LA FECHA DE INICIO DEBE SER MENOR IGUAL A LA FECHA FINAL!!');
            document.querySelector('#filtroFechaInicio').focus();
            return;
        }
        dtRVentas.draw();

    }
</script>
@endpush
