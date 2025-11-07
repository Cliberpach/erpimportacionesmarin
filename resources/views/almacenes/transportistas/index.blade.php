@extends('layout')
@section('content')
@section('almacenes-active', 'active')
@section('transportistas-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-10">
        <h2 style="text-transform:uppercase"><b>LISTA DE TRANSPORTISTAS</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>TRANSPORTISTAS</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2 col-md-2">
        <button onclick="goToCrearTransportista()" id="btn_añadir_producto" class="btn btn-block btn-w-m btn-success m-t-md">
            <i class="fa fa-plus-square"></i> NUEVO
        </button>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    @csrf
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Vehículos</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content" id="div_productos">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="table-responsive">
                                @include('almacenes.transportistas.tables.tbl_list_transportistas')
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


</style>
@endpush

@push('scripts')
<script>
    let dtTransportistas    =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        iniciarDataTableTransportistas();
    })

    function iniciarDataTableTransportistas(){
        const urlGetAll = '{{ route('almacenes.transportistas.getTransportistas') }}';

        dtTransportistas  =   new DataTable('#tbl_list_transportistas',{
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetAll,
                type: 'GET',
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'nombre', name: 'nombre' },
                { data: 'tipo_documento_nombre', name: 'tipo_documento_nombre' },
                { data: 'nro_documento', name: 'nro_documento' },
                { data: 'direccion', name: 'direccion' },
                { data: 'mtc', name: 'mtc' },
                {
                    data: null,
                    render: function(data, type, row) {
                        const baseUrlEdit   =   `{{ route('almacenes.transportistas.edit', ['id' => ':id']) }}`;
                        urlEdit             =   baseUrlEdit.replace(':id', data.id);

                        const urlDelete = `{{ route('almacenes.transportistas.destroy', ':id') }}`.replace(':id', data.id);

                        return `
                            <div class="btn-group dropstart">
                            <button type="button" class="dropdown-toggle btn btn-success" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-th"></i>
                            </button>
                            <ul class="dropdown-menu" style="max-height: 150px; overflow-y: auto;">
                                <li>
                                    <a class="dropdown-item" href="${urlEdit}">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarTransportista(${data.id})">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </li>
                            </ul>
                            </div>
                        `;
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

    function goToCrearTransportista(){
        window.location.href = @json(route('almacenes.transportistas.create'));
    }


    function eliminarTransportista(id){
        toastr.clear();
        let row             =   getRowById(dtTransportistas,id);
        let message         =   '';
        let tipo_documento  =   '';

        if(row.tipo_documento_id == 1){
            tipo_documento  =   'DNI';
        }

        if(row.tipo_documento_id == 2){
            tipo_documento  =   'RUC';
        }


        message =   `Desea eliminar el transportista: ${row.nombre}, ${tipo_documento}:${row.nro_documento}`;

        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: message,
        text: "Operación no reversible!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar!",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cargando...',
                html: 'Eliminando transportista...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                let urlDeleteTransportista      =   `{{ route('almacenes.transportistas.destroy', ['id' => ':id']) }}`;
                urlDeleteTransportista          =   urlDeleteTransportista.replace(':id', id);
                const token                     =   document.querySelector('input[name="_token"]').value;

                const response  =   await fetch(urlDeleteTransportista, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        }
                                    });

                const   res =   await response.json();

                if(res.success){
                    dtTransportistas.draw();
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR AL ELIMINAR TRANSPORTISTA');
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ELIMINAR TRANSPORTISTA');
            }finally{
                Swal.close();
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
</script>

@endpush
