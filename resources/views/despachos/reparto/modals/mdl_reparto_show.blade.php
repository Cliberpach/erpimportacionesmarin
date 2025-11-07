<div class="modal fade" id="mdl_reparto_show" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow rounded border-0">

            {{-- Header --}}
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title mb-0">
                    <i class="fa fa-info-circle text-light mr-2"></i> DETALLE DEL REPARTO
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" @click.prevent="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body content_cliente pt-2">
                <div class="row">

                    <!-- Datos generales -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-3">
                                <h6 class="text-secondary font-weight-bold mb-3">
                                    <i class="fa fa-list text-primary mr-2"></i> Información General
                                </h6>
                                <p class="mb-2"><i class="fa fa-barcode text-dark mr-2"></i><strong>Código:</strong>
                                    <span id="show_codigo">RT-0000000001</span>
                                </p>
                                <p class="mb-2"><i class="fa fa-clock text-info mr-2"></i><strong>Creado:</strong>
                                    <span id="show_created_at"></span>
                                </p>
                                <p class="mb-2"><i
                                        class="fa fa-sync text-success mr-2"></i><strong>Actualizado:</strong> <span
                                        id="show_updated_at"></span></p>
                                <p class="mb-0"><i class="fa fa-flag text-warning mr-2"></i><strong>Estado:</strong>
                                    <span id="show_estado" class="badge badge-warning"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del registrador -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-3">
                                <h6 class="text-secondary font-weight-bold mb-3">
                                    <i class="fa fa-user text-success mr-2"></i> Registrador
                                </h6>

                                <p class="mb-0"><i
                                        class="fa fa-user-circle text-info mr-2"></i><strong>Nombre:</strong> <span
                                        id="show_registrador_nombre"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Observación -->
                    <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-3">
                                <h6 class="text-secondary font-weight-bold mb-3">
                                    <i class="fa fa-comment-dots text-warning mr-2"></i> Observación
                                </h6>
                                <p id="show_observacion" class="mb-0 text-muted">Sin observación</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tablas de detalle -->
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="" style="font-weight: bold;">
                            <i class="fa fa-boxes text-danger mr-2"></i> PAQUETES
                        </label>
                        <div class="table-responsive">
                            @include('despachos.reparto.tables.tbl_rshow_paquetes')
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                        <label for="" style="font-weight: bold;">
                            <i class="fa fa-truck text-primary mr-2"></i> ENVIOS
                        </label>
                        <div class="table-responsive">
                            @include('despachos.reparto.tables.tbl_rshow_envios')
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer d-flex justify-content-between px-4">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" @click.prevent="Cerrar">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .tr-selected {
        background-color: #fff3cd !important;
        transition: background-color 0.3s;
    }
</style>


<script>
    let dtPaquetes = null;
    let dtEnviosReparto = null;

    function eventsMdlRShow() {
        $('#mdl_reparto_show').on('hidden.bs.modal', function() {
            limpiarMdlRShow();
        });
        inicializarDtMdlRShow();
    }

    function inicializarDtMdlRShow() {
        destruirDataTable(dtPaquetes);
        destruirDataTable(dtEnviosReparto);
        limpiarTabla('tbl_rshow_envios');
        limpiarTabla('tbl_rshow_paquetes');
        dtEnviosReparto = iniciarDataTable('tbl_rshow_envios');
        dtPaquetes = iniciarDataTable('tbl_rshow_paquetes')
    }

    function limpiarMdlRShow() {
        // Código
        document.getElementById('show_codigo').innerText = '';

        // Fechas
        document.getElementById('show_created_at').innerText = '';
        document.getElementById('show_updated_at').innerText = '';

        // Estado
        const estadoEl = document.getElementById('show_estado');
        estadoEl.innerText = '';
        estadoEl.className = 'badge badge-secondary';

        // Registrador
        document.getElementById('show_registrador_nombre').innerText = '';

        // Observación
        document.getElementById('show_observacion').innerText = '';

        destruirDataTable(dtPaquetes);
        destruirDataTable(dtEnviosReparto);
        limpiarTabla('tbl_rshow_envios');
        limpiarTabla('tbl_rshow_paquetes');
        dtEnviosReparto = iniciarDataTable('tbl_rshow_envios');
        dtPaquetes = iniciarDataTable('tbl_rshow_paquetes')
    }


    async function openMdlRShow(id) {
        toastr.clear();
        mostrarAnimacion();
        const datos = await getMdlRShow(id);
        pintarReparto(datos.reparto);

        destruirDataTable(dtPaquetes);
        limpiarTabla('tbl_rshow_paquetes');
        pintarTblPaquetes(datos.paquetes);
        dtPaquetes = iniciarDataTable('tbl_rshow_paquetes');

        $('#mdl_reparto_show').modal('show');
        ocultarAnimacion();
    }

    function pintarTblPaquetes(lstPaquetes) {
        const tbody = document.querySelector('#tbl_rshow_paquetes tbody');
        tbody.innerHTML = '';

        lstPaquetes.forEach((p) => {
            const tr = document.createElement('tr');

            // Primera columna con botón
            const tdAccion = document.createElement('td');
            tdAccion.classList.add('text-center');
            const btn = document.createElement('button');
            btn.classList.add('btn', 'btn-sm', 'btn-success');
            btn.innerHTML = '<i class="fas fa-eye"></i>';

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                getEnvios(p.id);

                // Limpiar otras filas seleccionadas
                tbody.querySelectorAll('tr').forEach(row => row.classList.remove('tr-selected'));

                // Resaltar la fila actual
                tr.classList.add('tr-selected');
            });

            tdAccion.appendChild(btn);
            tr.appendChild(tdAccion);

            // Resto de columnas
            const tdQR = document.createElement('td');
            tdQR.classList.add('text-center');
            tdQR.textContent = p.qr_codigo ?? '';
            tr.appendChild(tdQR);

            const tdEstado = document.createElement('td');
            tdEstado.classList.add('text-center');
            let estadoBadge = '';
            switch (p.estado) {
                case 'PENDIENTE':
                    estadoBadge = '<span class="badge badge-warning">PENDIENTE</span>';
                    break;
                case 'DESPACHADO':
                    estadoBadge = '<span class="badge badge-success">DESPACHADO</span>';
                    break;
                case 'ANULADO':
                    estadoBadge = '<span class="badge badge-danger">ANULADO</span>';
                    break;
                default:
                    estadoBadge = `<span class="badge badge-secondary">${p.estado ?? ''}</span>`;
            }
            tdEstado.innerHTML = estadoBadge;
            tr.appendChild(tdEstado);

            // Otras columnas
            const campos = [
                p.cliente_nombre,
                p.cliente_celular,
                p.destinatario_nombre,
                `${p.destinatario_tipo_doc ?? ''}: ${p.destinatario_nro_doc ?? ''}`,
                p.direccion_entrega || '-',
                `${p.departamento ?? ''} / ${p.provincia ?? ''} / ${p.distrito ?? ''}`,
                p.empresa_envio_nombre,
                p.sede_envio_nombre,
                p.tipo_pago_envio
            ];

            campos.forEach(campo => {
                const td = document.createElement('td');
                td.classList.add('text-center');
                td.textContent = campo ?? '';
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });

        if (lstPaquetes.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="13" class="text-center text-muted">No se encontraron paquetes</td>`;
            tbody.appendChild(tr);
        }
    }


    function pintarReparto(reparto) {
        // Código
        document.getElementById('show_codigo').innerText = reparto.codigo ?? '';

        // Fechas
        document.getElementById('show_created_at').innerText = formatearFecha(reparto.created_at);
        document.getElementById('show_updated_at').innerText = formatearFecha(reparto.updated_at);


        // Estado
        const estadoEl = document.getElementById('show_estado');
        estadoEl.innerText = reparto.estado ?? '';
        estadoEl.className = 'badge';
        switch (reparto.estado) {
            case 'PENDIENTE':
                estadoEl.classList.add('badge-danger');
                break;
            case 'DESPACHADO':
                estadoEl.classList.add('badge-success');
                break;
            default:
                estadoEl.classList.add('badge-secondary');
        }

        // Registrador
        document.getElementById('show_registrador_nombre').innerText = reparto.registrador_nombre ?? '';

        // Observación
        document.getElementById('show_observacion').innerText = reparto.observacion ?? 'Sin observación';
    }

    async function getMdlRShow(id) {
        try {
            const res = await axios.get(route('despachos.reparto.getMdlRShow', id));

            if (!res.data.success) {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                return null;
            }

            toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            return res.data.data;
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER DATOS DE VER REPARTO');
            return null;
        }
    }

    async function getEnvios(paqueteId) {
        try {
            toastr.clear();
            mostrarAnimacion();
            const res = await axios.get(route('despachos.reparto.getEnviosPorPaquete', paqueteId));
            if (res.data.success) {
                toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');

                destruirDataTable(dtEnviosReparto);
                limpiarTabla('tbl_rshow_envios')
                pintarEnviosReparto(res.data.data);
                dtEnviosReparto = iniciarDataTable('tbl_rshow_envios')
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER ENVÍOS DEL PAQUETE');
        } finally {
            ocultarAnimacion();
        }
    }

    function pintarEnviosReparto(lstEnvios) {
        const tbody = document.querySelector('#tbl_rshow_envios tbody');
        tbody.innerHTML = ''; // Limpiar tabla antes de pintar

        if (!lstEnvios || lstEnvios.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="18" class="text-center text-muted">No se encontraron envíos</td>`;
            tbody.appendChild(tr);
            return;
        }

        lstEnvios.forEach((e, index) => {
            const tr = document.createElement('tr');

            // Número
            const tdNum = document.createElement('td');
            tdNum.classList.add('text-center');
            tdNum.textContent = index + 1;
            tr.appendChild(tdNum);

            //DOC
            const tdCod = document.createElement('td');
            tdCod.classList.add('text-center');
            tdCod.textContent = e.documento_nro ?? '';
            tr.appendChild(tdCod);

            // ESTADO con badge
            const tdEstado = document.createElement('td');
            tdEstado.classList.add('text-center');
            let estadoBadge = '';
            switch (e.estado) {
                case 'PENDIENTE':
                    estadoBadge = '<span class="badge badge-warning">PENDIENTE</span>';
                    break;
                case 'DESPACHADO':
                    estadoBadge = '<span class="badge badge-success">DESPACHADO</span>';
                    break;
                case 'ANULADO':
                    estadoBadge = '<span class="badge badge-danger">ANULADO</span>';
                    break;
                default:
                    estadoBadge = `<span class="badge badge-secondary">${e.estado ?? ''}</span>`;
            }
            tdEstado.innerHTML = estadoBadge;
            tr.appendChild(tdEstado);

            // Columnas adicionales
            const campos = [
                e.cliente_nombre,
                e.cliente_celular,
                e.destinatario_nombre,
                `${e.destinatario_tipo_doc ?? ''}: ${e.destinatario_nro_doc ?? ''}`,
                e.direccion_entrega || '-',
                `${e.departamento ?? ''} / ${e.provincia ?? ''} / ${e.distrito ?? ''}`,
                e.empresa_envio_nombre,
                e.sede_envio_nombre,
                e.tipo_envio,
                e.tipo_pago_envio,
                e.monto_envio,
                e.obs_despacho,
                e.obs_rotulo,
                e.origen_venta,
                e.usuario_nombre
            ];

            campos.forEach(campo => {
                const td = document.createElement('td');
                td.classList.add('text-center');
                td.textContent = campo ?? '';
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
    }
</script>
