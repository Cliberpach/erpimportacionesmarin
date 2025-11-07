@extends('layout')
@section('content')

@section('despachos-active', 'active')
@section('reparto-active', 'active')


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>CREAR REPARTO</b></h2>
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
                    <div class="row">
                        <div class="col-12">
                            @include('despachos.reparto.forms.form_create')
                        </div>
                    </div>
                </div>
                <div class="ibox-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary mr-2 btn-cancelar-reparto"
                        id="btn-cancelar-reparto">
                        <i class="fas fa-arrow-left"></i> VOLVER
                    </button>
                    <button type="submit" class="btn btn-success btn-guardar-reparto" id="btn-guardar-reparto"
                        form="form-reparto">
                        <i class="fas fa-check"></i> REGISTRAR
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    let lstDetalleReparto = [];
    let dtPaquetesPendientes = null;
    let dtDetalleReparto = null;

    document.addEventListener('DOMContentLoaded', () => {
        events();
        iniciarDtPaquetesPendientes();
        dtDetalleReparto = iniciarDataTable('tbl_detalle_reparto');
    })

    function events() {

        document.querySelector('#form-reparto').addEventListener('submit', function(e) {
            e.preventDefault();
            guardarReparto(e.target);
        });

        document.getElementById('input-codigo').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-agregar')) {
                let id = e.target.closest('.btn-agregar').dataset.id;
                agregarPaquete(id);
            }
            if (e.target.closest('.btn-eliminar')) {
                let id = e.target.closest('.btn-eliminar').dataset.id;
                eliminarPaquete(id);
            }
            if (e.target.closest('.btn-cancelar-reparto')) {
                window.location.href = "{{ route('despachos.reparto.index') }}";
            }
        })

        document.getElementById('input-codigo').addEventListener('input', function() {
            let codigo = this.value.trim();

            if (codigo.length === 14) {
                dtPaquetesPendientes.search(codigo).draw();

                dtPaquetesPendientes.one('draw', function() {
                    let $btn = $('#tbl_paquetes_pendientes tbody tr td .btn-agregar').first();
                    if ($btn.length) {
                        $btn.trigger('click');
                        dtPaquetesPendientes.search('').draw();
                        $('#tbl_paquetes_pendientes_filter input').val('');
                    }
                });
                this.value = '';
            }
        });
    }

    function iniciarDtPaquetesPendientes() {
        dtPaquetesPendientes = $('#tbl_paquetes_pendientes').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('despachos.reparto.getPaquetesEmbaladosPendientes') }}",
                type: 'GET',
            },
            rowCallback: function(row, data) {
                let existe = lstDetalleReparto.some(item => item.id == data.id);
                if (existe) {
                    $(row).hide();
                }
            },
            columns: [{
                    data: null,
                    className: "text-center letrapequeña",
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<button type="button" class="btn btn-sm btn-success btn-agregar" data-id="${row.id}">
                                <i class="fas fa-plus"></i>
                            </button>`;
                    }
                },
                {
                    data: 'id',
                    name: 'p.id',
                    searchable: false,
                    className: "text-center letrapequeña"
                },
                {
                    data: 'qr_codigo',
                    name: 'p.qr_codigo',
                    className: "text-center letrapequeña"
                },
                {
                    data: 'cliente_nombre',
                    name: 'p.cliente_nombre',
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
                    className: "text-left letrapequeña",
                    searchable: false
                },
                {
                    data: null,
                    searchable: false,
                    className: "text-left letrapequeña",
                    render: function(data, type, row) {
                        return row.destinatario_tipo_doc + ':' + row.destinatario_nro_doc;
                    }
                },
                {
                    data: 'destinatario_nombre',
                    name: 'p.destinatario_nombre',
                    searchable: false,
                    className: "text-left letrapequeña"
                },
                {
                    data: 'registrador_nombre',
                    name: 'p.registrador_nombre',
                    searchable: false,
                    className: "text-left letrapequeña"
                },
                {
                    data: 'estado',
                    searchable: false,
                    className: "text-center letrapequeña",
                    render: function(data) {
                        let estado = '';
                        if (data == "PENDIENTE") {
                            estado = `<div class="col-estado-pendiente">${data}</div>`;
                        } else if (data == "RESERVADO") {
                            estado = `<div class="col-estado-reservado">${data}</div>`;
                        } else if (data == "DESPACHADO") {
                            estado = `<div class="col-estado-despachado">${data}</div>`;
                        }
                        return estado;
                    }
                }
            ],
            order: [
                [1, 'desc']
            ],
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                infoFiltered: "(filtrado de un total de _MAX_ registros)",
                infoPostFix: "",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron resultados",
                emptyTable: "Ningún dato disponible en esta tabla",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                },
                aria: {
                    sortAscending: ": Activar para ordenar la columna de manera ascendente",
                    sortDescending: ": Activar para ordenar la columna de manera descendente"
                },
                buttons: {
                    copyTitle: 'Copiado al portapapeles',
                    copySuccess: {
                        _: '%d líneas copiadas',
                        1: '1 línea copiada'
                    },
                    print: 'Imprimir'
                }
            },
            responsive: true,
            lengthMenu: [10, 25, 50, 100],
        });
    }

    function eliminarPaquete(id) {
        toastr.clear();
        let index = lstDetalleReparto.findIndex(item => item.id == id);
        if (index !== -1) {
            lstDetalleReparto.splice(index, 1);
            destruirDataTable(dtDetalleReparto);
            limpiarTabla('tbl_detalle_reparto');
            pintarTableDetalleReparto(lstDetalleReparto);
            dtDetalleReparto = iniciarDataTable('tbl_detalle_reparto');
            toastr.info('PAQUETE ELIMINADO DEL DETALLE DE REPARTO.');
            dtPaquetesPendientes.ajax.reload();
        } else {
            toastr.error('EL PAQUETE NO SE ENCONTRÓ EN EL DETALLE.');
        }
    }

    function agregarPaquete(id) {
        toastr.clear();
        let rowData = getRowById(dtPaquetesPendientes, id);

        let existe = lstDetalleReparto.some(item => item.id === rowData.id);
        if (existe) {
            toastr.error('EL PAQUETE YA ESTÁ AGREGADO EN EL DETALLE.');
            return;
        }

        lstDetalleReparto.unshift(rowData);

        destruirDataTable(dtDetalleReparto);
        limpiarTabla('tbl_detalle_reparto');
        pintarTableDetalleReparto(lstDetalleReparto)
        dtDetalleReparto = iniciarDataTable('tbl_detalle_reparto');
        toastr.info('PAQUETE AGREGADO AL DETALLE DE REPARTO.');

        dtPaquetesPendientes.ajax.reload();

    }

    function pintarTableDetalleReparto(lstItems) {
        let tbody = document.querySelector('#tbl_detalle_reparto tbody');
        tbody.innerHTML = '';

        lstItems.forEach((item, index) => {
            let row = `<tr>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                <td class="text-center">${item.id}</td>
                <td class="text-center">${item.qr_codigo}</td>
                <td class="text-center">${item.cliente_nombre}</td>
                <td class="text-center">${item.empresa_envio_nombre}</td>
                <td class="text-left">${item.sede_envio_nombre}</td>
                <td class="text-left">${item.destinatario_tipo_doc}:${item.destinatario_nro_doc}</td>
                <td class="text-left">${item.destinatario_nombre}</td>
                <td class="text-left">${item.registrador_nombre}</td>
                <td class="text-center">${item.estado}</td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }

    function guardarReparto(formReparto) {
        toastr.clear();
        if (lstDetalleReparto.length === 0) {
            toastr.error('NO HAY PAQUETES EN EL DETALLE DE REPARTO.');
            return;
        }

        Swal.fire({
            title: 'CONFIRMAR REGISTRO',
            text: "¿ESTÁS SEGURO DE REGISTRAR EL REPARTO?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, registrar'
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'REGISTRANDO REPARTO...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const formData = new FormData(formReparto);
                    formData.append('lstDetalleReparto', JSON.stringify(lstDetalleReparto));

                    const res = await axios.post("{{ route('despachos.reparto.store') }}", formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = "{{ route('despachos.reparto.index') }}";
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR REPARTO');
                } finally {

                }

            }
        });

    }
</script>
@endpush
