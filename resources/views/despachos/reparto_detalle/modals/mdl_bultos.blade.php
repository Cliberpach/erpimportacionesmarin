<div class="modal inmodal" id="modal-bultos" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-xs">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <i class="fas fa-box-open modal-icon"></i>
                <h4 class="modal-title" style="font-weight: bold;">N° BULTOS</h4>
            </div>
            <div class="modal-body content_cliente">
                @include('components.overlay_search')
                @include('components.overlay_save')

                @include('despachos.reparto.forms.form_bultos')

            </div>
            <div class="modal-footer">
                <div class="col-md-6 text-left">
                    <i class="fa fa-exclamation-circle leyenda-required"></i> <small class="leyenda-required">Los
                        campos
                        marcados con asterisco (*) son obligatorios.</small>
                </div>
                <div class="col-md-6 text-right">
                    <button type="submit" class="btn btn-success btn-sm" form="form-pdf-bultos"><i
                            class="fas fa-file-pdf"></i> GENERAR PDF </button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i
                            class="fa fa-times"></i> Cerrar</button>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <script>
        function eventsModalBultos() {

            document.getElementById("inputNroBultos").addEventListener("input", function() {
                var inputValue = this.value;
                var valorNumerico = inputValue.replace(/[^0-9]/g, '');
                this.value = valorNumerico;
            });

            document.querySelector('#form-pdf-bultos').addEventListener('submit', (e) => {
                e.preventDefault();
                toastr.clear();

                const paqueteId = document.querySelector('#paquete_id').value;
                const nroBultos = document.querySelector('#inputNroBultos').value;

                if (nroBultos.length > 0) {
                    generarPdfBultos(paqueteId, nroBultos);
                } else {
                    document.querySelector('#inputNroBultos').focus();
                    toastr.error('INGRESE UN N° DE BULTOS', 'ERROR');
                }

            })

            $('#modal-bultos').on('shown.bs.modal', function() {
                document.querySelector('#inputNroBultos').focus();
            });
        }

        function generarPdfBultos(paqueteId, nroBultos) {

            const url = "{{ route('despachos.reparto_detalle.pdfBultos', [':id', ':nro_bultos']) }}";
            const urlFinal = url.replace(':id', paqueteId).replace(':nro_bultos', nroBultos);

            window.open(urlFinal, '_blank');
            document.querySelector('#nro_bultos').value = '';
        }

        function openMdlBultos(id) {

            document.querySelector('#paquete_id').value = id;
            $('#modal-bultos').modal('show');

        }
    </script>
@endpush
