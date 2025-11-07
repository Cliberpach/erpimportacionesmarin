@extends('layout')
@section('content')

@section('almacenes-active', 'active')
@section('traslados-active', 'active')

<div class="row wrapper border-bottom white-bg page-heading">

    <div class="col-lg-12">
        <h2 style="text-transform:uppercase"><b>VER TRASLADO</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('almacenes.traslados.index') }}">Traslados</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Vizualizar</strong>
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
                        @include('almacenes.traslados.lists.list_show')
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
                            <a href="{{ route('almacenes.traslados.index') }}" id="btn_cancelar"
                                class="btn btn-w-m btn-default">
                                <i class="fa fa-arrow-left"></i> Regresar
                            </a>
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
        dtTrasladosShow = iniciarDataTable('tbl_traslado_show',100);
    })


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
</script>
@endpush
