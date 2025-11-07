@extends('layout')
@section('content')
@section('almacenes-active', 'active')
@section('transportistas-active', 'active')


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
        <h2 style="text-transform:uppercase"><b>EDITAR TRANSPORTISTA</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('almacenes.transportistas.index') }}">Transportistas</a>
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
                    @include('almacenes.transportistas.forms.form_edit')
                </div>
                <div class="ibox-footer d-flex justify-content-between align-items-center">
                    <span style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son
                        obligatorios</span>

                    <div style="display:flex;">
                        <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                            <i class="fa fa-reply-all"></i> VOLVER
                        </button>
                        <button class="btn btn-success" type="submit" form="formActualizarTransportista">
                            <i class="fa fa-save"></i> ACTUALIZAR
                        </button>
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
    document.addEventListener('DOMContentLoaded', () => {
        iniciarSelect2();
        events();
        setConfiguracion();
    })

    function events() {

        document.querySelector('#formActualizarTransportista').addEventListener('submit', (e) => {
            e.preventDefault();
            actualizarTransportista();
        })

        document.addEventListener('click', (e) => {
            if (e.target.closest('.btnVolver')) {
                const rutaIndex = '{{ route('almacenes.transportistas.index') }}';
                window.location.href = rutaIndex;
            }
        })

        //======= CONSULTAR API DOCUMENTO DNI ========
        document.querySelector('#btn_consultar_documento').addEventListener('click', () => {
            const nro_documento = document.querySelector('#nro_documento').value;
            const tipo_documento = document.querySelector('#tipo_documento').value;
            toastr.clear();

            if (tipo_documento != 8) {
                toastr.error('SOLO SE PUEDE CONSULTAR TIPO DE DOCUMENTO RUC');
                return;
            }

            if (nro_documento.length != 11) {
                toastr.error('NRO DE RUC DEBE CONTAR CON 11 DÍGITOS');
                return;
            }

            consultarDocumento(tipo_documento, nro_documento);

        })

        //===== PERMITIR SOLO NUMEROS ========
        document.querySelector('#nro_documento').addEventListener('input', (e) => {
            const input = e.target;

            input.value = input.value.replace(/\D/g, '');
        });

    }

    function iniciarSelect2() {
        $('.select2_form').select2({
            width: '100%',
            placeholder: $(this).data('placeholder'),
        });
    }

    function actualizarTransportista() {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger me-2"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ACTUALIZAR EL TRANSPORTISTA?",
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                try {

                    limpiarErroresValidacion('msgError');
                    const id = '{{ $transportista->id }}';
                    const token = document.querySelector('input[name="_token"]').value;
                    const formActualizarTransportista = document.querySelector(
                        '#formActualizarTransportista');
                    const formData = new FormData(formActualizarTransportista);
                    let url = `{{ route('almacenes.transportistas.update', ['id' => ':id']) }}`;
                    url = url.replace(':id', id);

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Actualizando transportista...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: formData
                    });

                    const res = await response.json();

                    console.log(res);

                    if (response.status === 422) {
                        if ('errors' in res) {
                            pintarErroresValidacion(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        const colaborador_index = @json(route('almacenes.transportistas.index'));
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = colaborador_index;
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR TRANSPORTISTA');
                    Swal.close();
                }

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire({
                    title: "OPERACIÓN CANCELADA",
                    text: "NO SE REALIZARON ACCIONES",
                    icon: "error"
                });
            }
        });
    }

    //======== CHANGE TIPO DOCUMENTO ======
    function changeTipoDoc(params) {
        const tipo_documento = document.querySelector('#tipo_documento').value;
        const inputNroDoc = document.querySelector('#nro_documento');
        const btnConsultarDocumento = document.querySelector('#btn_consultar_documento');

        //======== DNI =======
        if (tipo_documento == 6) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 8;
            btnConsultarDocumento.disabled = false;
        }

        //======= RUC =======
        if (tipo_documento == 8) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 11;
            btnConsultarDocumento.disabled = false;
        }

        //====== CARNET EXTRANJERÍA =====
        if (tipo_documento == 7) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 20;
            btnConsultarDocumento.disabled = true;
        }
    }

    //======= CONSULTAR DOCUMENTO IDENTIDAD =====
    async function consultarDocumento(tipo_documento, nro_documento) {
        mostrarAnimacion1();
        try {
            const datos = {
                tipo_documento,
                nro_documento
            };
            const queryString = new URLSearchParams(datos).toString();
            const token = document.querySelector('input[name="_token"]').value;
            const url =
                `{{ route('almacenes.transportistas.consultarDocumento') }}?tipo_documento=${encodeURIComponent(tipo_documento)}&nro_documento=${encodeURIComponent(nro_documento)}`;

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                },
            });

            const res = await response.json();

            if (res.success) {

                if (!res.data.success) {
                    toastr.error(res.data.message);
                    return;
                }

                if (tipo_documento == 2) {
                    setDatosRuc(res.data.data);
                    toastr.info(res.message);
                }

            } else {
                toastr.error(res.message, 'ERROR EN EL SERVIDOR AL CONSULTAR DOCUMENTO');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN CONSULTAR DOCUMENTO');
        } finally {
            ocultarAnimacion1();
        }
    }

    function setDatosRuc(data) {
        console.log(data);
        const nombre_completo = `${data.nombre_o_razon_social}`;
        const direccion = `${data.direccion}`;

        document.querySelector('#nombre').value = nombre_completo;
        document.querySelector('#direccion').value = direccion;
    }
</script>
@endpush
