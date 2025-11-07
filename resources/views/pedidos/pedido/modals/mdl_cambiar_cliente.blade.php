<div class="modal inmodal fade" id="mdl_cambiar_cliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width:900px;">
        <div class="modal-content animated bounceInRight">

            {{-- Header --}}
            <div class="modal-header py-2 px-3 bg-success text-white d-flex justify-content-between align-items-center">
                <span class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    RESERVA # <span id="pd_id_mdl_cambiar_cliente"></span>
                </span>
                <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Cerrar"
                    style="font-size: 1.2rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body content_cliente">
                <form action="" id="form-cambiar-cliente">
                    <div class="row">
                        <div class="col">
                            <label for="cliente_cambio_id" style="font-weigt:bold;">CLIENTE</label>
                            <select name="cliente_cambio_id" id="cliente_cambio_id">
                            </select>
                            <span style="font-weight: bold;color:red;" class="cliente_cambio_id_error msgError"></span>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <div class="col-md-6 text-left">
                    <i class="fa fa-exclamation-circle text-danger"></i>
                    <small class="leyenda-required">
                        Los campos marcados con asterisco (*) son obligatorios.
                    </small>
                </div>

                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm" id="btnGuardarCambios"
                        form="form-cambiar-cliente">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </div>


        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    const parametrosMdlCambiarCliente = {
        pedidoId: null
    };

    function eventsMdlCambiarCliente() {
        iniciarSelectMdlCambiarCliente();

        document.querySelector('#form-cambiar-cliente').addEventListener('submit', (e) => {
            e.preventDefault();
            cambiarCliente(e.target);
        })

        $('#mdl_cambiar_cliente').on('hidden.bs.modal', function() {
            window.clienteSelect?.clear();
        });

    }

    async function openMdlCambiarCliente(pedidoId) {
        const pedido = getRowById(pedidos_data_table, pedidoId);
        parametrosMdlCambiarCliente.pedidoId = pedidoId;
        document.querySelector('#pd_id_mdl_cambiar_cliente').textContent = pedido.id;
        await setClienteDefault(pedido.cliente_id);
        $('#mdl_cambiar_cliente').modal('show');
    }

    function iniciarSelectMdlCambiarCliente() {
        window.clienteSelect = new TomSelect('#cliente_cambio_id', {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            load: function(query, callback) {
                if (!query.length || query.length < 2) {
                    return callback(); // mínimo 2 caracteres
                }
                fetch("{{ route('utilidades.getClientes') }}?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const clientes = data.clientes.map(item => ({
                                id: item.id,
                                text: item.descripcion
                            }));
                            callback(clientes);
                        } else {
                            toastr.error(data.message, 'ERROR EN EL SERVIDOR');
                            callback();
                        }
                    })
                    .catch(() => {
                        toastr.error("Error al obtener clientes", "ERROR");
                        callback();
                    });
            },
            render: {
                option: function(item, escape) {
                    return `<div>${escape(item.text)}</div>`;
                },
                item: function(item, escape) {
                    return `<div>${escape(item.text)}</div>`;
                },
                no_results: function(data, escape) {
                    return `<div class="no-results">No se encontraron clientes</div>`;
                },
                loading: function(data, escape) {
                    return `<div><i class="fa fa-spinner fa-spin text-primary"></i> Buscando...</div>`;
                }
            },
        });
    }

    async function setClienteDefault(clienteId) {
        try {
            const response = await fetch(`{{ route('utilidades.getClientes') }}?cliente_id=${clienteId}`);
            const data = await response.json();

            if (data.success && data.clientes.length > 0) {
                const cliente = data.clientes[0];

                window.clienteSelect.addOption({
                    id: cliente.id,
                    text: cliente.descripcion
                });

                window.clienteSelect.setValue(cliente.id);

            }
        } catch (error) {
            toastr.error("Error al cargar cliente inicial", "ERROR");
        }
    }

    function cambiarCliente(formCambiarCliente) {
        Swal.fire({
            title: `DESEA CAMBIAR EL CLIENTE DEL PEDIDO #${parametrosMdlCambiarCliente.pedidoId}`,
            text: "Confirmar!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: `SÍ`
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: `Cambiando cliente`,
                    text: 'Por favor, espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const formData = new FormData(formCambiarCliente);
                    formData.append('pedido_id', parametrosMdlCambiarCliente.pedidoId);

                    const res = await axios.post(route('pedidos.pedido.cambiarCliente'), formData);

                    if (res.data.success) {

                        pedidos_data_table.ajax.reload();
                        toastr.success(res.data.message, 'Exito');
                        $('#mdl_cambiar_cliente').modal('hide');
                    } else {
                        toastr.error(res.data.message, `ERROR EN EL SERVIDOR`);
                    }
                } catch (error) {
                    if (error.response && error.response.status === 422) {
                        const errores = error.response.data.errors;
                        pintarErroresValidacion(errores,'error');
                        toastr.error('ERRORES DE VALIDACIÓN EN EL FORMULARIO');
                        return;
                    }
                    toastr.error(error, 'ERROR EN LA PETICIÓN CAMBIAR CLIENTE DE PEDIDO');
                } finally {
                    Swal.close();
                }

            }
        });
    }
</script>
