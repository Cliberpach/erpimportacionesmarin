@extends('layout')
@section('content')

@section('ventas-active', 'active')

<style>
    .resaltar-texto{
        color: rgb(56, 136, 193);
        font-weight: bold;
    }
</style>

@section('documento-active', 'active')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
       <h2  style="text-transform:uppercase"><b>REGISTRAR NUEVA NOTA DE @if(isset($nota_venta)) DEVOLUCIÓN @else CRÉDITO @endif</b></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('home')}}">Panel de Control</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('ventas.documento.index')}}">Documentos</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Nota de @if(isset($nota_venta)) devoluciÓn @else crédito @endif</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12">

     </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">

                <div class="ibox-content">

                   @include('ventas.notas.credito.forms.form_nc_create')

                </div>


            </div>
        </div>

    </div>

</div>
@include('ventas.notas.credito.modal')

@stop
@push('styles')
@endpush

@push('scripts')
 <!-- Ladda -->
 <script src="{{ asset('Inspinia/js/plugins/ladda/spin.min.js') }}"></script>
 <script src="{{ asset('Inspinia/js/plugins/ladda/ladda.min.js') }}"></script>
 <script src="{{ asset('Inspinia/js/plugins/ladda/ladda.jquery.min.js') }}"></script>

<script>
    const bodyTablaDetalles     =   document.querySelector('#tbl-detalles tbody');
    const bodyTableDevoluciones =   document.querySelector('#tbl-detalles-devolucion tbody');
    const inputIndice           =   document.querySelector('#indice');

    const inputCantidadDevolver =   document.querySelector('#cantidad_devolver');
    const inputDescripcion      =   document.querySelector('#descripcion');
    const inputPrecioUnitario   =   document.querySelector('#precio_unitario');
    const inputImporte          =   document.querySelector('#importe_venta');
    const inputProductoId       =   document.querySelector('#input_producto_id');
    const inputColorId          =   document.querySelector('#input_color_id');
    const inputTallaId          =   document.querySelector('#input_talla_id');

    const inputTotalOriginal    =   document.querySelector('#total');

    const inputSubTotalNuevo    =   document.querySelector('#sub_total_nuevo');
    const inputTotalIgvNuevo    =   document.querySelector('#total_igv_nuevo');
    const inputTotalNuevo       =   document.querySelector('#total_nuevo');

    const inputMontoTotalDev    =   document.querySelector('#monto_total_devolucion');

    const btnGuardar            =   document.querySelector('#btn_editar_detalle');

    const formDevolucion        =   document.querySelector('#enviar_documento');

    let detalles        = null;
    let devoluciones    = [];

    document.addEventListener('DOMContentLoaded',()=>{

        loadSelect2();
        getDetalles({{$documento->id}});
        events();
    })

    function events(){

        //====== ENVIAR FORM DEVOLUCION ======
        formDevolucion.addEventListener('submit',(e)=>{
            e.preventDefault();

            if(devoluciones.length === 0){
                toastr.error('NO HAY DEVOLUCIONES','ERROR');
                return;
            }

            enviarFormDevolucion();

        })

        //==== VALIDACION INPUT CANTIDAD DEVOLVER ====
        inputCantidadDevolver.addEventListener('input',(e)=>{
            const max_valor     =   parseInt(e.target.getAttribute('data-cant-max'));
            const valorActual   =   parseInt(e.target.value);
            if(valorActual>max_valor){
                e.target.value  =   max_valor;
            }

            var regexEntero = /^\d+$/;
            // Verificar si el valor es 0 o no es un número entero
            if (inputCantidadDevolver.value === '0' || !regexEntero.test(inputCantidadDevolver.value)) {
                inputCantidadDevolver.value = '';
            }

        })

        //====== BORRAR DEVOLUCION =========
        document.addEventListener('click',(e)=>{
            if(e.target.classList.contains('btn-delete-devolucion')){
                const producto_id   =   e.target.getAttribute('data-producto-id');
                const color_id      =   e.target.getAttribute('data-color-id');
                const talla_id      =   e.target.getAttribute('data-talla-id');

                devoluciones    =   devoluciones.filter((d)=>{
                    return !(d.producto_id==producto_id && d.color_id==color_id && d.talla_id==talla_id);
                })

                pintarDevoluciones();
                pintarMontoTotalDevolucion();
                calcularNuevosMontos();
            }
        })

        //======== BTN GUARDAR CANTIDAD DEVOLUCION ======
        btnGuardar.addEventListener('click',()=>{
            const item_devolver =   getValuesForm();
            if(item_devolver.cantidad_devolver.toString().trim().length === 0){
                toastr.error('INGRESE LA CANTIDAD A DEVOLVER','ERROR');
                return;
            }

            //==== BUSCANDO SI EXISTE DEVOLUCIÓN PARA ESTE ITEM ======
            const existeDevolucion = devoluciones.findIndex((dev)=>{
                return dev.producto_id==item_devolver.producto_id && dev.color_id==item_devolver.color_id && dev.talla_id==item_devolver.talla_id;
            })

            //===== ACTUALIZAR LISTADO DEVOLUCIONES ======
            if(existeDevolucion == -1){
                devoluciones.push(item_devolver);
            }else{
                //==== ELIMINAR DEL LISTADO DEVOLUCIONES SI LA CANTIDAD A DEVOLVER ES 0 =====
                if(item_devolver.cantidad_devolver == 0){
                    devoluciones.splice(existeDevolucion,1);
                }else{
                    devoluciones[existeDevolucion] = item_devolver;
                }
            }

            pintarDevoluciones();
            pintarMontoTotalDevolucion();
            calcularNuevosMontos();
            $('#modal_editar_detalle').modal('hide');

        })

        //====== EDITAR ITEM DE NOTA DE VENTA =======
        document.addEventListener('click',(e)=>{
            if (event.target.classList.contains('btn-edit-item') || event.target.classList.contains('btn-edit-icon')) {
                const cod_motivo = $('#cod_motivo').val();

                if(cod_motivo != '')
                {
                     //======= ACCEDER AL ANCESTRO MÁS CERCANO QUE CUMPLA CON LA CLASE ======
                    //======= PUEDE TOMAR AL MISMO ELEMENTO CLICKEADO SI LLEGA A CUMPLIR ======
                    const producto_id   = event.target.closest('.btn-edit-item').getAttribute('data-producto-id');
                    const color_id      = event.target.closest('.btn-edit-item').getAttribute('data-color-id');
                    const talla_id      = event.target.closest('.btn-edit-item').getAttribute('data-talla-id');



                    limpiarForm();
                    setValuesForm({producto_id,color_id,talla_id});


                    $('#modal_editar_detalle').modal('show');
                }
                else
                {
                    toastr.error('Seleccionar tipo de nota de crédito','Error')
                }
            }
        })
    }

    //===== CARGAR PRODUCTOS DEVOLUCION =======
    function cargarProductos() {
        $('#productos_tabla').val(JSON.stringify(devoluciones));
    }


    //========= ENVIAR DEVOLUCIÓN ======
    function enviarFormDevolucion(){
        let enviar = true;
        let total =  inputMontoTotalDev.value;

        if(parseFloat(total) <= 0)
        {
            enviar = false;
            toastr.error('El monto total de la Nota de Crédito debe ser mayor que 0.')
        }

        if(enviar)
        {
            cargarProductos();
            calcularNuevosMontos();
            let formDocumento = document.getElementById('enviar_documento');
            let formData = new FormData(formDocumento);

            var object = {};
            formData.forEach(function(value, key){
                object[key] = value;
            });

            var datos = object;
            console.log(datos)
            var init = {
                // el método de envío de la información será POST
                method: "POST",
                headers: { // cabeceras HTTP
                    // vamos a enviar los datos en formato JSON
                    'Content-Type': 'application/json'
                },
                // el cuerpo de la petición es una cadena de texto
                // con los datos en formato JSON
                body: JSON.stringify(datos) // convertimos el objeto a texto
            };

            var url = '{{ route("ventas.notas.store") }}';
            var textAlert = "¿Seguro que desea guardar cambios?";
            Swal.fire({
                title: 'Opción Guardar',
                text: textAlert,
                icon: 'question',
                customClass: {
                    container: 'my-swal'
                },
                showCancelButton: true,
                confirmButtonColor: "#1ab394",
                confirmButtonText: 'Si, Confirmar',
                cancelButtonText: "No, Cancelar",
                showLoaderOnConfirm: true,
                allowOutsideClick: false,
                preConfirm: (login) => {
                    return fetch(url,init)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Ocurrió un error`
                            );
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value !== undefined && result.isConfirmed) {
                    if(result.value.errors)
                    {
                        let mensaje = sHtmlErrores(result.value.data.mensajes);
                        toastr.error(mensaje);
                    }
                    else if(result.value.success)
                    {
                        let id = result.value.nota_id;
                        @if(isset($nota_venta))
                            toastr.success('Nota de devolución creada!','Exito')
                        @else
                            toastr.success('Nota de crédito creada!','Exito')
                            let url_open_pdf = '{{ route("ventas.notas.show", ":id")}}';
                            url_open_pdf = url_open_pdf.replace(':id',id);
                            window.open(url_open_pdf, "Comprobante SISCOM", "width=900, height=600");
                        @endif

                        let ruta = "{{route('ventas.notas', $documento->id)}}"
                        @if(isset($nota_venta))
                            ruta = "{{route('ventas.notas_dev', $documento->id)}}";
                        @endif

                        location = ruta;
                    }
                    else
                    {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: '¡'+ result.value.mensaje +'!',
                            customClass: {
                                container: 'my-swal'
                            },
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                }
            });

        }
    }

    //===== CALCULAR NUEVOS MONTOS =====
    function calcularNuevosMontos(){
        let subtotal    = 0;
        let total       = 0;
        let igv         = 0;

        total       =   parseFloat(inputMontoTotalDev.value);
        subtotal    =   total/1.18;
        igv         =   total - subtotal;


        inputTotalIgvNuevo.value    =   formatoMoneda(igv);
        inputSubTotalNuevo.value    =   formatoMoneda(subtotal);
    }

    //======= PINTAR MONTO TOTAL DEVOLUCION =======
    function pintarMontoTotalDevolucion(){
        let monto_total=0;
        devoluciones.forEach((d)=>{
            monto_total+=d.importe;
        })

        inputMontoTotalDev.value    =   formatoMoneda(monto_total);
    }

    //======= ESTABLECER DATOS DEL ITEM EN EL MODAL EDIT ======
    function setValuesForm(item_){

        //==== BUSCANDO ITEM EN DETALLES =====
        const item  =   detalles.filter((d)=>{
            return d.producto_id == item_.producto_id && d.color_id == item_.color_id  && d.talla_id == item_.talla_id;
        })



        if(item.length>0){
            //====== BUSCANDO SI EL DETALLE TIENE DEVOLUCIÓN AGREGADA ====
            inputCantidadDevolver.value = parseInt(item[0].cantidad);
            inputDescripcion.value      = `${item[0].modelo_nombre}-${item[0].producto_nombre}-${item[0].color_nombre}-${item[0].talla_nombre} `;
            inputPrecioUnitario.value   = parseFloat(item[0].precio_unitario_nuevo).toFixed(2);
            inputImporte.value          = parseFloat(item[0].importe_nuevo).toFixed(2);
            inputProductoId.value       = item_.producto_id;
            inputColorId.value          = item_.color_id;
            inputTallaId.value          = item_.talla_id;


            //======= ACTIVANDO EL CAMPO CANTIDAD ======
            inputCantidadDevolver.setAttribute("data-cant-max", parseInt(item[0].cantidad));
            inputCantidadDevolver.removeAttribute('readonly');
        }
    }

    function getValuesForm(){
        const producto_id       =   inputProductoId.value;
        const color_id          =   inputColorId.value;
        const talla_id          =   inputTallaId.value;
        const cantidad_devolver =   inputCantidadDevolver.value;
        const precio_unitario   =   inputPrecioUnitario.value;
        const importe           =   parseFloat(precio_unitario) * parseFloat(cantidad_devolver);

        //==== buscando producto_nombre, color_nombre,modelo_nombre,codigo_producto =====
        const item  =   detalles.filter((d)=>{
            return d.producto_id == producto_id && d.color_id == color_id  && d.talla_id == talla_id;
        })

        const item_devolver = {
            codigo_producto: item[0].codigo_producto,
            producto_id,
            color_id,
            talla_id,
            producto_nombre: item[0].producto_nombre,
            color_nombre: item[0].color_nombre,
            talla_nombre: item[0].talla_nombre,
            modelo_nombre: item[0].modelo_nombre,
            cantidad_devolver,
            precio_unitario,
            importe
        };

        return item_devolver;
    }

    //====== LIMPIAR FORM ====
    function limpiarForm()
    {
        $("#cantidad_devolver").attr('readonly');
        $("#cantidad_devolver").val('');
        $("#descripcion").attr('readonly');
        $("#descripcion").val('');
        $("#precio_unitario").attr('readonly');
        $("#precio_unitario").val('');
        $("#descuento_dev").attr('readonly');
        $("#descuento_dev").val('');
        $("#monto_igv").attr('readonly');
        $("#monto_igv").val('');
        $("#importe_venta").attr('readonly');
        $("#importe_venta").val('');
        inputProductoId.value   =   '';
        inputColorId.value      =   '';
        inputTallaId.value      =   '';
    }

    //======= CARGAR SELECT2 =======
    const loadSelect2 = ()=>{
        $(".select2_form").select2({
            placeholder: "SELECCIONAR",
            allowClear: true,
            height: '200px',
            width: '100%',
        });
    }

    //====== CHANGE TIPO DE NOTA =====
    function changeTipoNota(b)
    {
        const opciones_table_detalles   = document.querySelector('.tbl-detalles-opciones');
        const opciones_table_devolucion =   document.querySelector('.tbl-devolucion-opciones');

        if(b.value != '')
        {
            //==== DEVOLUCIÓN TOTAL ====
            if(b.value == '01')
            {
                devoluciones = [];   //==== LIMPIAR ARRAY ====
                getDetalles({{ $documento->id }})
                opciones_table_detalles.classList.add('d-none');
                opciones_table_devolucion.classList.add('d-none');
                allReturn();
                pintarDevoluciones();
                pintarMontoTotalDevolucion();
                calcularNuevosMontos();
            }
            //===== DEVOLUCIÓN PARCIAL =====
            else
            {
                devoluciones = [];  //==== LIMPIAR ARRAY ====
                getDetalles({{ $documento->id }})
                pintarDevoluciones();
                pintarMontoTotalDevolucion();
                calcularNuevosMontos();
                opciones_table_detalles.classList.remove('d-none');
                opciones_table_devolucion.classList.remove('d-none');
            }
        }else{
            clearTableDetalles();
        }
    }

    //===== DEVOLVER TODO =======
    function allReturn(){
        detalles.forEach((detalle)=>{
            const item_devolver = {
                codigo_producto     :   detalle.codigo_producto,
                producto_id         :   detalle.producto_id,
                color_id            :   detalle.color_id,
                talla_id            :   detalle.talla_id,
                producto_nombre     :   detalle.producto_nombre,
                color_nombre        :   detalle.color_nombre,
                talla_nombre        :   detalle.talla_nombre,
                modelo_nombre       :   detalle.modelo_nombre,
                cantidad_devolver   :   detalle.cantidad,
                precio_unitario     :   detalle.precio_unitario_nuevo,
                importe             :   parseFloat(detalle.cantidad)*parseFloat(detalle.precio_unitario_nuevo)
            };

            devoluciones.push(item_devolver);
        })
    }

    //====== OBTENER DETALLE DEL DOC DE VENTA =======
    const getDetalles= (documento_id)=>{
        $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');
        let url = '{{ route("ventas.getDetalles",":id") }}';
        url = url.replace(':id',documento_id);

        var l = $( '.ladda-button-demo' ).ladda();
        l.ladda( 'start' );


        $.ajax({
            dataType: 'json',
            type: 'get',
            url: url,
        }).done(function(result) {

            detalles        =   result.detalles;
            pintarDetalle(result.detalles);

            l.ladda('stop');
            $('#panel_detalle').children('.ibox-content').toggleClass('sk-loading');
        });
    }

    //======= PINTAR DETALLE =======
    const pintarDetalle = (detalles)=>{
        let fila = ``;
        const cod_motivo = $('#cod_motivo').val();

        detalles.forEach((detalle)=>{


            fila += `
                    <tr>
                        <th scope="row"></th>
                        <td>${parseInt(detalle.cantidad)}</td>
                        <td>${detalle.modelo_nombre} - ${detalle.producto_nombre} - ${detalle.color_nombre} - ${detalle.talla_nombre}</td>
                        <td>${(Math.round(detalle.precio_unitario_nuevo * 100) / 100).toFixed(2)}</td>
                        <td>${(Math.round(detalle.importe_nuevo * 100) / 100).toFixed(2)}</td>
                        ${cod_motivo === '07' ?
                            `<td>

                                <button data-producto-id="${detalle.producto_id}" data-color-id="${detalle.color_id}"
                                    data-talla-id="${detalle.talla_id}"
                                    id="editar" type="button" class="btn btn-sm btn-info btn-rounded btn-edit-item">
                                    <i class="fas fa-plus btn-edit-icon"></i>
                                </button>

                            </td>`: ''
                        }
                    </tr>
                    `;
        })

        bodyTablaDetalles.innerHTML = fila;
    }

    //======= PINTAR DEVOLUCIONES =======
    const pintarDevoluciones = ()=>{
        let fila = ``;

        const cod_motivo = $('#cod_motivo').val();

        devoluciones.forEach((devolucion)=>{
            fila += `
                    <tr>
                        <th scope="row">
                        </th>
                        <td>${parseInt(devolucion.cantidad_devolver)}</td>
                        <td>${devolucion.modelo_nombre} - ${devolucion.producto_nombre} - ${devolucion.color_nombre} - ${devolucion.talla_nombre}</td>
                        <td>${(Math.round(devolucion.precio_unitario * 100) / 100).toFixed(2)}</td>
                        <td>${(Math.round(devolucion.importe * 100) / 100).toFixed(2)}</td>
                        ${cod_motivo === '07' ?
                            `<td>
                                <i class="btn btn-danger fas fa-trash-alt btn-delete-devolucion" data-producto-id="${devolucion.producto_id}"
                                data-color-id="${devolucion.color_id}" data-talla-id="${devolucion.talla_id}"></i>
                            </td>`:''
                        }
                    </tr>
                    `;
        })

        bodyTableDevoluciones.innerHTML = fila;
    }

    const clearTableDetalles = ()=>{
        while(bodyTablaDetalles.firstChild){
            bodyTablaDetalles.removeChild(bodyTablaDetalles.firstChild)
        }
    }



</script>
@endpush
