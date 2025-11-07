@extends('layout')
@section('content')
@section('almacenes-active', 'active')
@section('conductores-active', 'active')


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-12">
       <h2  style="text-transform:uppercase"><b>REGISTRAR NUEVO CONDUCTOR</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('almacenes.conductores.index') }}">Productos Terminados</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Registrar</strong>
            </li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    @include('almacenes.conductores.forms.form_create_conductor')
                </div>
                <div class="ibox-footer d-flex justify-content-between align-items-center">
                    <span  style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son obligatorios</span>

                    <div style="display:flex;">
                        <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                            <i class="fa fa-reply-all"></i> VOLVER
                        </button>
                        <button class="btn btn-success" type="submit" form="formRegistrarConductor">
                            <i class="fa fa-save"></i> REGISTRAR
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
    document.addEventListener('DOMContentLoaded',()=>{
        iniciarSelect2();
        events();
    })

    function events(){

        document.querySelector('#formRegistrarConductor').addEventListener('submit',(e)=>{
            e.preventDefault();
            registrarConductor();
        })

        document.addEventListener('click',(e)=>{

            if (e.target.closest('.btnVolver')) {
                const rutaIndex         =   '{{route('almacenes.conductores.index')}}';
                window.location.href    =   rutaIndex;
            }

            if (e.target.closest('.btn_consultar_documento')) {

                const nro_documento     =   document.querySelector('#nro_documento').value;
                const tipo_documento    =   document.querySelector('#tipo_documento').value;
                toastr.clear();

                if(tipo_documento != 6 && tipo_documento != 8){
                    toastr.error('SOLO SE PUEDE CONSULTAR DNI Y RUC');
                    return;
                }

                if(tipo_documento == 6 && nro_documento.length != 8){
                    toastr.error('NRO DE DNI DEBE CONTAR CON 8 DÍGITOS');
                    return;
                }

                if(tipo_documento == 8 && nro_documento.length != 11){
                    toastr.error('NRO DE RUC DEBE CONTAR CON 11 DÍGITOS');
                    return;
                }

                consultarDocumento(tipo_documento,nro_documento);

            }

        })
    }

    function iniciarSelect2(){
        $( '.select2_form' ).select2( {
            width:'100%',
            placeholder: $( this ).data( 'placeholder' ),
        } );
    }

    function registrarConductor(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA REGISTRAR EL CONDUCTOR?",
        text: "Se creará un nuevo conductor!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, REGISTRAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            limpiarErroresValidacion('msgError');
            const token                     =   document.querySelector('input[name="_token"]').value;
            const formRegistrarConductor    =   document.querySelector('#formRegistrarConductor');
            const formData                  =   new FormData(formRegistrarConductor);
            const urlRegistrarConductor     =   @json(route('almacenes.conductores.store'));

            Swal.fire({
                title: 'Cargando...',
                html: 'Registrando nuevo conductor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response  =   await fetch(urlRegistrarConductor, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                console.log(res);

                if(response.status === 422){
                    if('errors' in res){
                        pintarErroresValidacion(res.errors,'error');
                    }
                    Swal.close();
                    return;
                }

                if(res.success){
                    const conductor_index     =   @json(route('almacenes.conductores.index'));
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    window.location.href    =   conductor_index;
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }


            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR CONDUCTOR');
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

    function pintarErroresValidacion(objErroresValidacion){
        for (let clave in objErroresValidacion) {
            const pError        =   document.querySelector(`.${clave}_error`);
            pError.textContent  =   objErroresValidacion[clave][0];
        }
    }

    //======== CHANGE TIPO DOCUMENTO ======
    function changeTipoDoc(params) {
        const tipo_documento        =   document.querySelector('#tipo_documento').value;
        const inputNroDoc           =   document.querySelector('#nro_documento');
        const btnConsultarDocumento =   document.querySelector('.btn_consultar_documento');

        //======== DNI =======
        if(tipo_documento == 6){
            inputNroDoc.value               =   '';
            inputNroDoc.readOnly            =   false;
            inputNroDoc.maxLength           =   8;
            btnConsultarDocumento.disabled  =   false;
        }

         //======== RUC =======
         if(tipo_documento == 8){
            inputNroDoc.value               =   '';
            inputNroDoc.readOnly            =   false;
            inputNroDoc.maxLength           =   11;
            btnConsultarDocumento.disabled  =   false;
        }

        //====== CARNET EXTRANJERÍA =====
        if(tipo_documento != 6 && tipo_documento != 8){
            inputNroDoc.value               =   '';
            inputNroDoc.readOnly            =   false;
            inputNroDoc.maxLength           =   20;
            btnConsultarDocumento.disabled  =   true;
        }
    }



    //======= CONSULTAR DOCUMENTO IDENTIDAD =====
     async function consultarDocumento(tipo_documento,nro_documento){
        mostrarAnimacion();
        try {
            const token                     =   document.querySelector('input[name="_token"]').value;
            const urlConsultarDocumento     =   `{{ route('almacenes.conductores.consultarDocumento') }}?tipo_documento=${encodeURIComponent(tipo_documento)}&nro_documento=${encodeURIComponent(nro_documento)}`;

            const response  =   await fetch(urlConsultarDocumento, {
                                    method: 'GET',
                                    headers: {
                                        'X-CSRF-TOKEN': token
                                    },
                                });

            const   res =   await response.json();

            if(res.success){
                if(tipo_documento == 6){
                    setDatosDni(res.data.data);
                }
                if(tipo_documento == 8){
                    setDatosRuc(res.data.data);
                }

                toastr.info(res.message);
            }else{
                toastr.error(res.message,'ERROR EN EL SERVIDOR AL CONSULTAR DOCUMENTO');
            }
        } catch (error) {
            toastr.error(error,'ERROR EN LA PETICIÓN CONSULTAR DOCUMENTO');
        }finally{
            ocultarAnimacion();
        }
    }

    function setDatosDni(data){

        const nombres           =   data.nombres;
        const apellidos         =   `${data.apellido_paterno} ${data.apellido_materno}`;

        document.querySelector('#nombres').value         =   nombres;
        document.querySelector('#apellidos').value       =   apellidos;

    }

    function setDatosRuc(data){
        const nombre_o_razon_social     =   `${data.nombre_o_razon_social}`;
        const direccion_completa        =   data.direccion_completa;
        const ubigeo                    =   data.ubigeo;

        document.querySelector('#nombre').value     =   nombre_o_razon_social;
        //document.querySelector('#direccion').value  =   direccion_completa;

        //======= COLOCANDO UBIGEO =======
        /*let departamento_ubigeo = parseInt(ubigeo[0]);
        let provincia_ubigeo    = parseInt(ubigeo[1]);
        let distrito_ubigeo     = parseInt(ubigeo[2]);


        if(!isNaN(departamento_ubigeo)){
            $('#departamento').val(departamento_ubigeo).trigger('change');
        }

        if(!isNaN(provincia_ubigeo)){
            $('#provincia').val(provincia_ubigeo).trigger('change');
        }

        if(!isNaN(distrito_ubigeo)){
            $('#distrito').val(distrito_ubigeo).trigger('change');
        }*/

    }

</script>

@endpush
