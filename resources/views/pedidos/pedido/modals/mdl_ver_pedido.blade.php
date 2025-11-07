<div class="modal inmodal fade" id="mdl_ver_pedido" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width:900px;">
        <div class="modal-content animated bounceInRight">

            {{-- Header --}}
            <div class="modal-header py-2 px-3 bg-success text-white d-flex justify-content-between align-items-center">
                <span class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    RESERVA # <span id="pd_id"></span>
                </span>
                <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Cerrar"
                    style="font-size: 1.2rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body content_cliente">
                <div class="container-fluid">

                    {{-- Card Datos Generales --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clipboard-list text-primary"></i> Datos Generales</span>
                            <button class="btn btn-sm btn-link text-dark" data-toggle="collapse"
                                data-target="#cardGenerales">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="cardGenerales" class="collapse show">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><i class="fas fa-barcode text-primary"></i> <strong>Nro Reserva:</strong>
                                            <span id="pd_nro"></span>
                                        </p>
                                        <p><i class="fas fa-calendar-alt text-info"></i> <strong>Fecha
                                                Registro:</strong> <span id="pd_fecha_registro"></span></p>
                                        <p><i class="fas fa-calendar-check text-success"></i> <strong>Fecha
                                                Propuesta:</strong> <span id="pd_fecha_propuesta"></span></p>
                                        <p><i class="fas fa-warehouse text-warning"></i> <strong>Almacén:</strong> <span
                                                id="pd_almacen"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><i class="fas fa-user text-primary"></i> <strong>Cliente:</strong> <span
                                                id="pd_cliente"></span></p>
                                        <p><i class="fas fa-phone text-success"></i> <strong>Teléfono:</strong> <span
                                                id="pd_telefono"></span></p>
                                        <p><i class="fas fa-building text-secondary"></i> <strong>Empresa:</strong>
                                            <span id="pd_empresa"></span>
                                        </p>
                                        <p><i class="fas fa-user-shield text-dark"></i> <strong>Registrado por:</strong>
                                            <span id="pd_usuario"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Estado y Condición --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-tasks text-info"></i> Estado y Condición</span>
                            <button class="btn btn-sm btn-link text-dark" data-toggle="collapse"
                                data-target="#cardEstado">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="cardEstado" class="collapse show">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><i class="fas fa-clipboard-check text-info"></i>
                                            <strong>Estado:</strong> <span id="pd_estado" class="badge"></span>
                                        </p>
                                        <p><i class="fas fa-file-invoice-dollar text-success"></i>
                                            <strong>Condición:</strong> <span id="pd_condicion"></span>
                                        </p>
                                        <p><i class="fas fa-receipt text-danger"></i>
                                            <strong>Factura:</strong> <span id="pd_factura"></span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><i class="fas fa-money-bill-wave text-success"></i>
                                            <strong>Monto Total:</strong> S/ <span id="pd_total"></span>
                                        </p>
                                        <p><i class="fas fa-percent text-info"></i>
                                            <strong>Descuento:</strong> <span id="pd_descuento"></span>
                                        </p>
                                        <p><i class="fas fa-cash-register text-primary"></i>
                                            <strong>Pagado:</strong> S/ <span id="pd_pagado"></span> |
                                            <strong>Saldo:</strong> S/ <span id="pd_saldo"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Totales --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-cash-register text-success"></i> Totales</span>
                            <button class="btn btn-sm btn-link text-dark" data-toggle="collapse"
                                data-target="#cardTotales">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="cardTotales" class="collapse">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><i class="fas fa-box text-secondary"></i>
                                            <strong>Sub Total:</strong> S/ <span id="pd_subtotal"></span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><i class="fas fa-receipt text-info"></i>
                                            <strong>IGV:</strong> S/ <span id="pd_igv"></span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><i class="fas fa-shopping-cart text-success"></i>
                                            <strong>Total a Pagar:</strong> S/ <span id="pd_total_pagar"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clipboard-list text-primary"></i> Detalles de la Reserva</span>
                            <button class="btn btn-sm btn-link text-dark" data-toggle="collapse"
                                data-target="#cardPedidoDetalles">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                        <div id="cardPedidoDetalles" class="collapse show">
                            <div class="card-body">
                                <div class="row">

                                    @include('pedidos.pedido.tables.tbl_pedido_ver')

                                </div>
                            </div>
                        </div>
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
    function eventsMdlVerPedido() {
        $('#mdl_ver_pedido').on('hidden.bs.modal', function() {
            limpiarMdlVerPedido();
        });
    }

    function limpiarMdlVerPedido() {
        document.getElementById("pd_id").textContent = "";
        document.getElementById("pd_nro").textContent = "";
        document.getElementById("pd_fecha_registro").textContent = "";
        document.getElementById("pd_fecha_propuesta").textContent = "";
        document.getElementById("pd_almacen").textContent = "";
        document.getElementById("pd_cliente").textContent = "";
        document.getElementById("pd_telefono").textContent = "";
        document.getElementById("pd_empresa").textContent = "";
        document.getElementById("pd_usuario").textContent = "";

        document.getElementById("pd_estado").textContent = "";
        document.getElementById("pd_estado").className = "badge";
        document.getElementById("pd_condicion").textContent = "";
        document.getElementById("pd_factura").textContent = "";
        document.getElementById("pd_total").textContent = "";
        document.getElementById("pd_descuento").textContent = "";
        document.getElementById("pd_pagado").textContent = "";
        document.getElementById("pd_saldo").textContent = "";

        document.getElementById("pd_subtotal").textContent = "";
        document.getElementById("pd_igv").textContent = "";
        document.getElementById("pd_total_pagar").textContent = "";
    }

    async function openMdlVerPedido(pedidoId) {

        const data = await getPedido(pedidoId);
        if (!data) return;

        pintarDatosPedido(data.pedido);
        pintarDetallePedido(data.detalle);
        $('#mdl_ver_pedido').modal('show');

    }

    function pintarDatosPedido(pedido) {

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value ?? '-';
        };

        setText("pd_nro", pedido.id);
        setText("pd_fecha_registro", pedido.fecha_registro);
        setText("pd_fecha_propuesta", pedido.fecha_propuesta);
        setText("pd_almacen", pedido.almacen_nombre || "SIN NOMBRE");

        setText("pd_cliente", pedido.cliente_nombre);
        setText("pd_telefono", pedido.cliente_telefono);
        setText("pd_empresa", pedido.empresa_nombre);
        setText("pd_usuario", pedido.user_nombre);

        const estadoEl = document.getElementById("pd_estado");
        if (estadoEl) {
            estadoEl.textContent = pedido.estado ?? "-";
            estadoEl.className = "badge"; // reset
            if (pedido.estado === "PENDIENTE") estadoEl.classList.add("badge-warning");
            else if (pedido.estado === "PAGADO") estadoEl.classList.add("badge-success");
            else if (pedido.estado === "ANULADO") estadoEl.classList.add("badge-danger");
            else estadoEl.classList.add("badge-secondary");
        }

        setText("pd_condicion", pedido.condicion_id == 2 ? "Crédito" : "Contado");
        setText("pd_factura", pedido.documento_venta_facturacion_serie || "NO EMITIDA");

        setText("pd_total", formatoMoneda(pedido.total_pagar));
        setText("pd_descuento", `${pedido.porcentaje_descuento ?? 0}% (S/ ${pedido.monto_descuento ?? "0.00"})`);
        setText("pd_pagado", formatoMoneda(pedido.doc_venta_credito_monto_pagado));
        setText("pd_saldo", formatoMoneda(pedido.doc_venta_credito_saldo));

        setText("pd_subtotal", pedido.sub_total);
        setText("pd_igv", pedido.total_igv);
        setText("pd_total_pagar", pedido.total_pagar);

        document.querySelector('#pd_id').textContent = pedido.id;
    }

    function pintarDetallePedido(lstDetalle) {
        const tbody = document.querySelector('#tbl_pedido_ver tbody');
        tbody.innerHTML = "";

        lstDetalle.forEach(det => {
            const tr = document.createElement("tr");

            tr.innerHTML = `
            <td>${det.producto_nombre}</td>
            <td>${det.color_nombre || '-'}</td>
            <td>${det.talla_nombre || '-'}</td>
            <td class="text-right">${parseFloat(det.cantidad).toFixed(2)}</td>
            <td class="text-right">S/ ${formatoMoneda(det.precio_unitario_nuevo)}</td>
            <td class="text-right">${parseFloat(det.porcentaje_descuento).toFixed(2)}%</td>
            <td class="text-right font-weight-bold">S/ ${formatoMoneda(det.importe_nuevo)}</td>
            <td>
                <span class="badge badge-${det.estado === 'EN ESPERA' ? 'warning' : 'success'}">
                    ${det.estado}
                </span>
            </td>
        `;

            tbody.appendChild(tr);
        });
    }



    async function getPedido(pedidoId) {
        try {
            toastr.clear();
            mostrarAnimacion();
            const res = await axios.get(route('pedidos.pedido.show', pedidoId));
            if (!res.data.success) {
                toastr.error(res.data.message);
                return null;
            }
            toastr.info(res.data.message);
            return res.data.data;
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER PEDIDO');
            return null;
        } finally {
            ocultarAnimacion();
        }
    }
</script>
