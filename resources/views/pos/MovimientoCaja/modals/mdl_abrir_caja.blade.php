<style>
    #modal_crear_caja .modal-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        border-bottom: none;
        padding-top: 2rem;
        padding-bottom: 1rem;
    }

    #modal_crear_caja .modal-title {
        font-size: 1.5rem;
        font-weight: bold;
    }

    #modal_crear_caja small {
        font-size: 0.9rem;
    }

    #modal_crear_caja .modal-body {
        max-height: 60vh;
        overflow-y: auto;
    }

    .select2-container {
        z-index: 9999 !important;
    }

 .modal-dialog {
    max-height: 90vh; /* modal no supera el alto visible */
    display: flex;
    flex-direction: column;
}

.modal-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.modal-body {
  max-height: 60vh;
  overflow-y: auto;
}


</style>

<div class="modal fade" id="modal_crear_caja" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content animated bounceInRight">

            <div class="modal-header text-center position-relative">
                <!-- Botón cerrar -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    style="position: absolute; top: 15px; right: 20px; font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>

                <!-- Icono -->
                <i class="fa fa-cogs" style="font-size: 3rem; color: #fcbc6c;"></i>

                <!-- Título -->
                <h4 class="modal-title mt-2 mb-0">Caja</h4>
                <small class="text-muted font-bold">Apertura de Caja</small>
            </div>

            <div class="modal-body">
                @include('pos.MovimientoCaja.forms.form_abrir_caja')
            </div>

            <div class="modal-footer">
                <div class="col-md-6 text-left" style="color:#fcbc6c">
                    <i class="fa fa-exclamation-circle"></i>
                    <small>Los campos marcados con asterisco (<label class="required"></label>) son
                        obligatorios.</small>
                </div>
                <div class="col-md-6 text-right">
                    <button type="submit" class="btn btn-primary btn-sm" id="btnEnviarAperturaCaja"
                        form="formAbrirCaja">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


@push('styles')
    <style>
        .swal2-container {
            z-index: 9999 !important;
        }
    </style>
@endpush
@push('scripts')
    <script>
        function eventsMdlAbrirCaja() {
            document.querySelector('#formAbrirCaja').addEventListener('submit', (e) => {
                e.preventDefault();
                abrirCaja(e.target);
            })

            $('#modal_crear_caja').on('hidden.bs.modal', function(e) {
                limpiarFormAbrirCaja();
            });

        }

        async function openMdlAbrirCaja() {
            await getDatosAperturaCaja();
        }

        function openCaja(btnOpenCaja) {
            btnOpenCaja.disabled = true;
            document.querySelector('#crear_caja_movimiento').submit();
            btnOpenCaja.innerHTML = `<i class="fa fa-save fa-spin" ></i> Guardando`;
        }

        function abrirCaja(formAperturarCaja) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: "Desea aperturar la caja?",
                text: "Operación no reversible!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    toastr.clear();
                    limpiarErroresValidacion('msgError');

                    Swal.fire({
                        title: "Abriendo caja...",
                        text: "Por favor, espera",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });


                    try {
                        const formData = new FormData(formAperturarCaja);
                        formData.append('sede_id', @json($sede_id));
                        const res = await axios.post(route('Caja.apertura'), formData);
                        if (res.data.success) {
                            dtMovimientoCajas.ajax.reload();
                            $('#modal_crear_caja').modal('hide');
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {

                        if (error.response) {
                            if (error.response.status === 422) {
                                const errors = error.response.data.errors;
                                pintarErroresValidacion(errors, 'error');
                                toastr.error('Errores de validación encontrados.', 'ERROR DE VALIDACIÓN');
                            } else {
                                toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                            }
                        } else if (error.request) {
                            toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                                'ERROR DE CONEXIÓN');
                        } else {
                            toastr.error(error.message, 'ERROR DESCONOCIDO');
                        }
                    } finally {
                        Swal.close();
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



        async function getDatosAperturaCaja() {
            try {
                mostrarAnimacion();
                const res = await axios.get(route('Caja.getDatosAperturaCaja', {
                    sede_id: @json($sede_id)
                }));

                if (res.data.success) {
                    setDatosMdlAperturaCaja(res.data);
                    $('#modal_crear_caja').modal('show');
                } else {
                    toastr.error(res.data.messaage, 'ERROR EN EL SERVIDOR');
                }

            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER DATOS DE APERTURA');
            } finally {
                ocultarAnimacion();
            }
        }

        function setDatosMdlAperturaCaja(datos) {
            const cajas_desocupadas = datos.cajas_desocupadas;
            const cajeros_desocupados = datos.cajeros_desocupados;

            $('#caja').empty().append('<option value="" disabled selected>Seleccionar</option>');
            cajas_desocupadas.forEach((cd) => {
                $('#caja').append(new Option(cd.nombre, cd.id));
            })

            $('#cajero_id').empty().append('<option value="" disabled selected>Seleccionar</option>');
            cajeros_desocupados.forEach((cd) => {
                $('#cajero_id').append(new Option(cd.nombre, cd.id));
            })

        }

        function limpiarFormAbrirCaja() {

            $('#caja').val(null).trigger('change');
            $('#caja').empty().trigger('change');

            $('#cajero_id').val(null).trigger('change');
            $('#cajero_id').empty().trigger('change');

            $('#turno').val(null).trigger('change');

            document.querySelector('#saldo_inicial').value = 0;

        }
    </script>
@endpush
