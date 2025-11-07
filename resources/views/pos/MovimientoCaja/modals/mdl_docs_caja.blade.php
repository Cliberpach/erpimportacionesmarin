<div class="modal inmodal fade" id="mdl_docs_caja" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width:900px;">
        <div class="modal-content animated bounceInRight">

            {{-- Header --}}
            <div class="modal-header py-2 px-3 bg-success text-white d-flex justify-content-between align-items-center">
                <span class="modal-title h6 font-weight-bold mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    DOCUMENTOS DE CAJA
                </span>
                <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Cerrar"
                    style="font-size: 1.2rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body content_cliente">
                <div class="container-fluid">

                    {{-- Ventas pendientes de pago --}}
                    <h5 class="font-weight-bold text-center text-uppercase my-3">
                        VENTAS CONTADO PENDIENTES DE PAGO
                        <a class="ml-2 text-warning" data-toggle="collapse" href="#leyendaVentasPendientes"
                            role="button" aria-expanded="false" aria-controls="leyendaVentasPendientes">
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h5>

                    <div class="collapse mb-3" id="leyendaVentasPendientes">
                        <div class="card card-body bg-light" style="font-size: 0.9rem;">
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-check text-success"></i> Ventas pendientes de pago</li>
                                <li><i class="fas fa-check text-success"></i> Se encuentran <strong>ACTIVAS</strong>
                                </li>
                                <li><i class="fas fa-times text-danger"></i> No están anuladas</li>
                                <li><i class="fas fa-times text-danger"></i> No provienen de una conversión</li>
                            </ul>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        @include('pos.MovimientoCaja.tables.tbl_ventas_pendientes')
                    </div>

                    {{-- Ventas por convertir --}}
                    <h5 class="font-weight-bold text-center text-uppercase my-3">
                        VENTAS POR CONVERTIR
                        <a class="ml-2 text-info" data-toggle="collapse" href="#leyendaVentasConvertir" role="button"
                            aria-expanded="false" aria-controls="leyendaVentasConvertir">
                            <i class="fas fa-chevron-down"></i>
                        </a>
                    </h5>

                    <div class="collapse mb-3" id="leyendaVentasConvertir">
                        <div class="card card-body bg-light" style="font-size: 0.9rem;">
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-check text-success"></i> Tickets registrados en caja</li>
                                <li><i class="fas fa-check text-success"></i> Deben convertirse en comprobantes para
                                    despachar</li>
                                <li><i class="fas fa-times text-danger"></i> No están anuladas</li>
                            </ul>
                        </div>
                    </div>

                    <div class="table-responsive">
                        @include('pos.MovimientoCaja.tables.tbl_ventas_convertir')
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <div class="col-md-6 text-left">
                    <i class="fa fa-exclamation-circle text-danger"></i>
                    <small class="leyenda-required">Los campos marcados con asterisco (*) son obligatorios.</small>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>



<script>
    function openMdlDocsCaja() {
        $('#mdl_docs_caja').modal('show');
    }

    function pintarVentasContadoPendientes(ventas) {
        const tbody = document.querySelector('#tbl_ventas_pendientes tbody');
        let filas = '';
        ventas.forEach(vta => {
            filas += `
                <tr></tr>
                    <td>${vta.serie}-${vta.correlativo}</td>
                    <td>${vta.cliente}</td>
                    <td>${vta.created_at}</td>
                    <td class="text-right">${formatoMoneda(vta.total_pagar)}</td>
                </tr>
            `;
        });
        tbody.innerHTML = filas;
    }

    function pintarVentasConvertir(ventas) {
        const tbody = document.querySelector('#tbl_ventas_convertir tbody');
        let filas = '';
        ventas.forEach(vta => {
            filas += `
                <tr></tr>
                    <td>${vta.serie}-${vta.correlativo}</td>
                    <td>${vta.cliente}</td>
                    <td>${vta.empresa_envio_nombre}</td>
                    <td>${vta.created_at}</td>
                    <td class="text-right">${formatoMoneda(vta.total_pagar)}</td>
                </tr>
            `;
        });
        tbody.innerHTML = filas;
    }
</script>
