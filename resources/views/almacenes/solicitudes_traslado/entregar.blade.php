@extends('layout')
@section('content')

@section('almacenes-active', 'active')
@section('solicitudes_traslado-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">

    <div class="col-lg-12">
        <h2 style="text-transform:uppercase"><b>Marcar como entregado</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('almacenes.solicitud_traslado.index') }}">Marcar como entregado</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Entregar</strong>
            </li>

        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                    <div class="row">
                        @include('almacenes.solicitudes_traslado.lists.list_show')
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h4 class=""><b>DETALLE DEL TRASLADO</b></h4>
                                </div>
                                <div class="panel-body">
                                    <hr>
                                    <div class="table-responsive">
                                        @include('almacenes.traslados.tables.tbl_traslado_show')
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
                            <a href="{{ route('almacenes.solicitud_traslado.index') }}" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
                            <button class="btn btn-success" id="btnConfirmar">GUARDAR</button>
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
    let dtTrasladosShow = null;

    document.addEventListener('DOMContentLoaded', () => {
        cargarSelect2();
        pintarDetalleTraslado();
        dtTrasladosShow = iniciarDataTable('tbl_traslado_show');
        events();
    })

    function events() {
        document.querySelector('#btnConfirmar').addEventListener('click', () => {
            setEntregado();
        })
    }

    function cargarSelect2() {
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            width: '100%',
        });
    }

    function pintarDetalleTraslado() {
        const detalles = @json($detalle);
        const tallas = @json($tallas);
        const bodyTablaDetalles = document.querySelector('#tbl_traslado_show tbody');
        let fila = ``;
        const producto_color_procesado = [];

        detalles.forEach((d) => {
            if (!producto_color_procesado.includes(`${d.producto_id}-${d.color_id}`)) {
                let htmlTallas = ``;

                fila += `<tr>
                        <td style="font-weight:bold;">${d.producto_nombre} - ${d.color_nombre}</td>`;

                tallas.forEach((t) => {

                    let cantidad = detalles.filter((det) => {
                        return det.producto_id == d.producto_id &&
                            det.color_id == d.color_id &&
                            t.id == det.talla_id;
                    });

                    cantidad.length != 0 ? cantidad = cantidad[0].cantidad : cantidad = '';

                    htmlTallas += `<td>${cantidad}</td>`;
                });

                fila += htmlTallas;
                fila += `</tr>`;

                producto_color_procesado.push(`${d.producto_id}-${d.color_id}`);
            }
        });

        bodyTablaDetalles.innerHTML = fila;
    }

    function setEntregado() {

        const traslado = @json($traslado);
        const mensaje = "SE ESTABLECERÁ EL ESTADO COMO ENTREGADO AL CLIENTE";

        const tituloLoading = "Estableciendo estado entregado";

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea marcar como entregado al cliente?",
            text: mensaje,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: tituloLoading,
                    html: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const formData = new FormData();
                    formData.append('traslado_id', @json($traslado->id));
                    const res = await axios.post(route('almacenes.solicitud_traslado.entregarStore'),
                        formData);
                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        window.location = route('almacenes.solicitud_traslado.index');
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR!!!');
                        Swal.close();
                    }
                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ESTABLECER ENTREGADO');
                    Swal.close();
                } finally {

                }


            } else if (result.dismiss === Swal.DismissReason.cancel) {
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
