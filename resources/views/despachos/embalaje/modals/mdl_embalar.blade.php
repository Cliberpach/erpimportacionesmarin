<div class="modal fade" id="mdl_embalar" tabindex="-1" role="dialog" aria-labelledby="miModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <!-- Encabezado -->
            <div class="modal-header">
                <h5 class="modal-title mb-0 d-flex align-items-center" id="miModalLabel">
                    <i class="fas fa-box text-success mr-2"></i> EMBALAR
                    <button type="button" class="btn btn-sm btn-success ml-2" id="btnReloadEmbalaje" title="Recargar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div id="alertaBoletaGuia" class="alert alert-danger d-none animated fadeInDown mb-2" role="alert">
            </div>

            <div class="alert alert-warning alert-dismissible fade show small mb-2" role="alert">
                <strong><i class="fas fa-exclamation-triangle"></i> Atención:</strong>
                <ul class="mb-0 pl-3">
                    <li>Solo se permite embalar ventas <b>pagadas</b></li>
                    <li>No se permiten ventas <b>anuladas</b></li>
                    <li>Las ventas se agrupan por <b>Cliente</b>, <b>Empresa</b>, <b>Sede</b>,
                        <b>Destinatario</b>, <b>Estado</b> y <b>Modo de venta</b>
                    </li>
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Cuerpo -->
            <div class="modal-body">
                @include('despachos.embalaje.lists.lst_embalar')
            </div>

            <!-- Footer -->
            <div class="modal-footer justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Revisa bien antes de embalar.
                </small>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>


        </div>
    </div>
</div>

<script>
    const parametrosMdlEmbalar = {
        id: null,
    };

    async function openMdlEmbalar(id) {

        mostrarAnimacion();
        parametrosMdlEmbalar.id = id;
        const data = await getMdlEmbalaje(id);
        if (!data) return;
        ocultarAnimacion();

        if (data.tiene_reservas_pendientes) {
            const result = await Swal.fire({
                title: `El cliente tiene reservas pendientes: ${data.tiene_reservas_pendientes}`,
                text: '¿Desea continuar con el embalaje?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'No',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'No se realizaron acciones',
                    showConfirmButton: false,
                    timer: 2000
                });
                ocultarAnimacion();
                return;
            }
        }

        const rutaGuiaBase = @json(route('despachos.embalaje.createGuiaEnvio', ['envio_id' => '__ID__']));
        const btn = document.getElementById('btn_generar_guia');
        btn.href = rutaGuiaBase.replace('__ID__', encodeURIComponent(id));

        btn.classList.remove('disabled');
        btn.removeAttribute('aria-disabled');

        pintarEnvio(data.envio);
        pintarTblAgrupar(data.similares, data.empresa_envio);
        pintarDetalleEmbalaje(data.detalle);
        pintarAlertaBoletaGuia(data.empresa_envio);

        ocultarAnimacion();
        $('#mdl_embalar').modal('show');

    }

    async function recargarMdlEmbalaje() {
        if (!parametrosMdlEmbalar.id) return;
        mostrarAnimacion();
        const data = await getMdlEmbalaje(parametrosMdlEmbalar.id);
        if (!data) return;
        pintarEnvio(data.envio);
        pintarTblAgrupar(data.similares, data.empresa_envio);
        pintarDetalleEmbalaje(data.detalle);
        pintarAlertaBoletaGuia(data.empresa_envio);

        ocultarAnimacion();
    }

    function eventsMdlEmbalar() {
        document.addEventListener('click', (e) => {

            if (e.target.closest('#btn_generar_paquete')) {
                generarPaquete();
            }

            if (e.target.closest('#btnReloadEmbalaje')) {
                recargarMdlEmbalaje();
            }

        })

        $('#mdl_embalar').on('hide.bs.modal', function() {
            limpiarMdlEmbalar();
        });

    }

    function limpiarMdlEmbalar() {
        parametrosMdlEmbalar.id = null;
        document.getElementById('show_cliente_nombre').textContent = '';
        document.getElementById('show_cliente_celular').textContent = '';
        document.getElementById('show_tipo_pago_envio').textContent = '';
        document.querySelector('#show_empresa_envio_nombre').textContent = '';
        document.querySelector('#show_sede_envio_nombre').textContent = '';
        document.querySelector('#show_tipo_envio').textContent = '';
        document.querySelector('#show_entrega_domicilio').textContent = '';
        document.querySelector('#show_direccion_entrega').textContent = '';
        document.querySelector('#show_documento_nro').textContent = '';
        document.querySelector('#show_fecha_envio_propuesta').textContent = '';
        document.querySelector('#show_estado').textContent = '';
        document.querySelector('#show_destinatario_tipo_doc').textContent = '';
        document.querySelector('#show_destinatario_nro_doc').textContent = '';
        document.querySelector('#show_destinatario_nombre').textContent = '';
        limpiarTabla('tbl_agrupar_embalaje');
        limpiarTabla('tbl_detalle_embalaje');
    }

    function pintarEnvio(envio) {
        document.getElementById('show_cliente_nombre').textContent = envio.cliente_nombre;
        document.getElementById('show_cliente_celular').textContent = envio.cliente_celular;
        document.getElementById('show_tipo_pago_envio').textContent = envio.tipo_pago_envio;
        document.querySelector('#show_empresa_envio_nombre').textContent = envio.empresa_envio_nombre;
        document.querySelector('#show_sede_envio_nombre').textContent = envio.sede_envio_nombre;
        document.querySelector('#show_tipo_envio').textContent = envio.tipo_envio;
        document.querySelector('#show_entrega_domicilio').textContent = envio.entrega_domicilio;
        document.querySelector('#show_direccion_entrega').textContent = envio.direccion_entrega;
        document.querySelector('#show_documento_nro').textContent = envio.documento_nro;
        document.querySelector('#show_fecha_envio_propuesta').textContent = envio.fecha_envio_propuesta;
        document.querySelector('#show_estado').textContent = envio.estado;
        document.querySelector('#show_destinatario_tipo_doc').textContent = envio.destinatario_tipo_doc;
        document.querySelector('#show_destinatario_nro_doc').textContent = envio.destinatario_nro_doc;
        document.querySelector('#show_destinatario_nombre').textContent = envio.destinatario_nombre;
    }

    function pintarTblAgrupar(lstSimilares, empresaEnvio) {
        const tbody = document.querySelector('#tbl_agrupar_embalaje tbody');
        let filas = '';

        lstSimilares.forEach(sim => {

            let badgeSerie = '';
            if (empresaEnvio.boleta_obligatorio == 1 && sim.tipo_venta_id == 129) {
                if (sim.convert_en_serie == null) {
                    badgeSerie = `<span class="badge badge-danger">OBLIGATORIO</span>`;
                } else {
                    badgeSerie = `<span class="badge badge-success">${sim.convert_en_serie}</span>`;
                }
            } else {
                if (sim.convert_en_serie == null) {
                    badgeSerie = `<span class="badge badge-warning">NO GENERADO</span>`;
                } else {
                    badgeSerie = `<span class="badge badge-info">${sim.convert_en_serie}</span>`;
                }
            }

            let badgeGuia = '';
            if (empresaEnvio.guia_obligatorio) {
                if (!sim.guia_serie) {
                    badgeGuia = `<span class="badge badge-danger">OBLIGATORIO</span>`;
                } else {
                    badgeGuia = `<span class="badge badge-success">${sim.guia_serie}</span>`;
                }
            } else {
                if (!sim.guia_serie) {
                    badgeGuia = `<span class="badge badge-warning">NO GENERADO</span>`;
                } else {
                    badgeGuia = `<span class="badge badge-info">${sim.guia_serie}</span>`;
                }
            }


            let badgePago = '';
            if (sim.estado_pago === 'PAGADA') {
                badgePago = `<span class="badge badge-success">${sim.estado_pago}</span>`;
            } else {
                badgePago = `<span class="badge badge-danger">${sim.estado_pago}</span>`;
            }

            filas += `
            <tr>
                <td>${sim.documento_nro}</td>
                <td>${badgeSerie}</td>
                <td>${badgeGuia}</td>
                <td>${badgePago}</td>
                <td>${formatoMoneda(sim.monto_envio)}</td>
            </tr>
        `;
        });

        tbody.innerHTML = filas;
    }


    function pintarDetalleEmbalaje(lstDetalles) {
        const tbody = document.querySelector('#tbl_detalle_embalaje tbody');
        tbody.innerHTML = '';

        lstDetalles.forEach(detalle => {
            const tr = document.createElement('tr');

            const tdProducto = document.createElement('td');
            tdProducto.textContent = detalle.producto_nombre;
            tdProducto.classList.add('text-center');
            tr.appendChild(tdProducto);

            const tdColor = document.createElement('td');
            tdColor.textContent = detalle.color_nombre;
            tdColor.classList.add('text-center');
            tr.appendChild(tdColor);

            const tdTalla = document.createElement('td');
            tdTalla.textContent = detalle.talla_nombre;
            tdTalla.classList.add('text-center');
            tr.appendChild(tdTalla);

            const tdCantidad = document.createElement('td');
            tdCantidad.textContent = detalle.cantidad;
            tdCantidad.classList.add('text-center');
            tr.appendChild(tdCantidad);

            const tdPrecioVenta = document.createElement('td');
            tdPrecioVenta.textContent = formatoMoneda(detalle.precio_unitario_nuevo);
            tdPrecioVenta.classList.add('text-center');
            tr.appendChild(tdPrecioVenta);

            tbody.appendChild(tr);
        });
    }


    async function getMdlEmbalaje(id) {
        try {

            const res = await axios.get(route('despachos.embalaje.getMdlEmbalaje', id));

            if (res.data.success) {
                toastr.info(res.data.message, 'OPERACIÓN COMPLETA');
                return res.data.data;
            } else {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                ocultarAnimacion();
                return null;
            }

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER DATOS MDL EMBALAJE');
            ocultarAnimacion();
            return null;
        }
    }

    function generarPaquete() {
        Swal.fire({
            title: 'Desea generar el paquete?',
            text: "El estado de todos los envíos serán agrupados y su estado cambiará a EMBALADO",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí'
        }).then(async (result) => {
            if (result.isConfirmed) {

                try {

                    toastr.clear();

                    Swal.fire({
                        title: 'Generando paquete de embalaje',
                        text: 'Por favor espere...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const res = await axios.post(route('despachos.embalaje.generarPaqueteEmbalaje'), {
                        id: parametrosMdlEmbalar.id
                    });

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETA');
                        dtDespachos.ajax.reload(null, false);
                        $('#mdl_embalar').modal('hide');
                        const url_rotulo = res.data.data;
                        window.open(url_rotulo, '_blank');
                        Swal.close();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en el servidor',
                            text: res.data.message,
                            confirmButtonColor: '#d33',
                            confirmButtonText: '<i class="fas fa-times"></i> Cerrar'
                        });
                        recargarMdlEmbalaje();
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN GENERAR PAQUETE EMBALAJE');
                    Swal.close();
                }

            }
        });
    }

    function pintarAlertaBoletaGuia(empresa) {
        const alerta = document.getElementById('alertaBoletaGuia');

        if (!empresa.boleta_obligatorio && !empresa.guia_obligatorio) {
            alerta.classList.add('d-none');
            alerta.innerHTML = '';
            return;
        }

        let contenido = `
            <h6 class="mb-1">
                <i class="fas fa-exclamation-circle"></i> Requisitos de la empresa envío
            </h6>
            <ul class="mb-0 pl-3">
        `;

        if (empresa.boleta_obligatorio) {
            contenido +=
                `<li><i class="fas fa-file-invoice text-primary"></i> <b>Boleta o Factura</b> obligatoria</li>`;
        }
        if (empresa.guia_obligatorio) {
            contenido += `<li><i class="fas fa-truck text-success"></i> <b>Guía</b> obligatoria</li>`;
        }

        contenido += `</ul>`;

        alerta.innerHTML = contenido;
        alerta.classList.remove('d-none');
    }
</script>
