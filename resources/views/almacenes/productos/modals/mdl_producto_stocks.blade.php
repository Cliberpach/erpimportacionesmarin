<div class="modal fade" id="mdl_producto_stocks" aria-labelledby="mdl_producto_stocks" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 160vh;width:160vh;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">STOCK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                        <label for="almacen" style="font-weight: bold;">ALMACÉN</label>
                        <select onchange="getColores();" name="almacen" id="almacen" class="select2_almacen">
                            <option value=""></option>
                            @foreach ($almacenes as $almacen)
                                <option value="{{ $almacen->id }}">{{ $almacen->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 mb-3">
                        <label for="inputProducto" style="font-weight: bold;">PRODUCTO</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-shoe-prints"></i>
                            </span>
                            <input readonly type="text" id="inputProducto" class="form-control"
                                placeholder="Ingrese producto">
                        </div>
                    </div>

                    <div class="col-12"></div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <h5 style="font-weight: bold;">COLORES</h5>
                        <div class="table-responsive">
                            @include('almacenes.productos.tables.tbl_producto_colores_ad')
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <h5 style="font-weight: bold;">TALLAS</h5>
                        <div class="table-responsive">
                            @include('almacenes.productos.tables.tbl_producto_tallas')
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Salir</button>
            </div>
        </div>
    </div>
</div>


<script>
    const parametrosMdlStocks = {
        producto_id: null
    };

    function eventsMdlStocks() {
        document.querySelector('#tbl_producto_colores tbody').addEventListener('click', function(event) {

            // Verificar si el clic ocurrió en una fila
            const clickedRow = event.target.closest('tr');
            if (!clickedRow) return;

            // Obtener los datos de la fila seleccionada
            const color_id = clickedRow.getAttribute('data-color-id');

            if (color_id) {
                getTallas(color_id);
            }

        });

        $('#mdl_producto_stocks').on('hidden.bs.modal', function() {
            limpiarMdlProductoStocks();
        });

        $('#mdl_producto_stocks').on('shown.bs.modal', function() {
            setDefaultAlmacen();
            //dtProductoTallas = iniciarDataTable('tbl_producto_tallas');
        });

    }

    function setDefaultAlmacen() {
        const almacenenesBD = @json($almacenes);
        const almacenPrincipal = almacenenesBD.find(a => a.tipo_almacen === 'PRINCIPAL');
        if (almacenPrincipal) {
            $('#almacen').val(almacenPrincipal.id).trigger('change');
        }
    }

    async function openMdlStocks(producto_id) {
        parametrosMdlStocks.producto_id = producto_id;

        itemFila = getRowById(dtProductos, producto_id);
        document.querySelector('#inputProducto').value = itemFila.nombre;

        $('#mdl_producto_stocks').modal('show');
    }

    async function getColores() {
        try {
            mostrarAnimacion();
            toastr.clear();
            const almacen_id = document.querySelector('#almacen').value;
            const producto_id = parametrosMdlStocks.producto_id;

            if (!almacen_id || !producto_id) {
                destruirDataTable(dtProductoColores);
                limpiarTabla('tbl_producto_colores');
                dtProductoColores = iniciarDataTable('tbl_producto_colores');

                destruirDataTable(dtProductoTallas);
                limpiarTabla('tbl_producto_tallas');
                dtProductoTallas = iniciarDataTable('tbl_producto_tallas');

                ocultarAnimacion();
                return;
            }

            const res = await axios.get(route('almacenes.producto.getColores', {
                almacen_id,
                producto_id
            }));

            if (res.data.success) {
                destruirDataTable(dtProductoColores);
                limpiarTabla('tbl_producto_colores');
                pintarTablaColores(res.data.data);
                dtProductoColores = iniciarDataTable('tbl_producto_colores');
                toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            } else {
                toastr.error(res.data.message, 'ERROR AL OBTENER COLORES');
            }
        } catch (error) {
            toastr.error(error.message, 'ERROR EN LA PETICIÓN OBTENER COLORES');
        } finally {
            ocultarAnimacion();
        }
    }

    async function getTallas(color_id) {
        try {
            toastr.clear();
            mostrarAnimacion();
            const almacen_id = document.querySelector('#almacen').value;
            const producto_id = parametrosMdlStocks.producto_id;

            if (!almacen_id || !producto_id || !color_id) {
                ocultarAnimacion();
                return;
            }

            const res = await axios.get(route('almacenes.producto.getTallas', {
                almacen_id,
                producto_id,
                color_id
            }));

            if (res.data.success) {
                destruirDataTable(dtProductoTallas);
                limpiarTabla('tbl_producto_tallas');
                pintarTablaTallas(res.data.data);
                dtProductoTallas = iniciarDataTable('tbl_producto_tallas');
                toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            } else {
                toastr.error(res.data.message, 'ERROR AL OBTENER TALLAS');
            }
        } catch (error) {
            toastr.error(error.message, 'ERROR EN LA PETICIÓN OBTENER TALLAS');
        } finally {
            ocultarAnimacion();
        }
    }

    function pintarTablaColores(lstColores) {
        const tbody = document.querySelector('#tbl_producto_colores tbody');
        let filas = ``;
        lstColores.forEach((c) => {
            filas += `<tr data-color-id="${c.color_id}">
                            <td>${c.color_id}</td>
                            <td>${c.color_nombre}</td>
                            <td>
                            <button type="button" class="btn btn-sm btn-success" onclick="generarAdhesivos(${c.color_id},'${c.color_nombre}')">
                                <i class="fa fa-tag"></i> ADHESIVO
                            </button>
                        </td>
                        </tr>`;
        })

        tbody.innerHTML = filas;
    }

    function pintarTablaTallas(lstTallas) {
        const tbody = document.querySelector('#tbl_producto_tallas tbody');
        let filas = ``;
        lstTallas.forEach((t) => {
            filas += `<tr>
                            <td>${t.talla_nombre}</td>
                            <td>${t.stock}</td>
                        </tr>`;
        })

        tbody.innerHTML = filas;
    }

    function limpiarMdlProductoStocks() {
        $('#almacen').val(null).trigger('change');

        destruirDataTable(dtProductoColores);
        limpiarTabla('tbl_producto_colores');
        dtProductoColores = iniciarDataTable('tbl_producto_colores');

        destruirDataTable(dtProductoTallas);
        limpiarTabla('tbl_producto_tallas');
        dtProductoTallas = iniciarDataTable('tbl_producto_tallas');
    }

    function generarAdhesivos(colorId, colorNombre) {

        const fila = getRowById(dtProductos, parametrosMdlStocks.producto_id);
        const productoNombre = fila.nombre;
        const almacenId = document.querySelector('#almacen').value;

        Swal.fire({
            title: '¿Desea generar adhesivos?',
            html: `
            <div style="text-align:center">
                <strong>PRODUCTO:</strong> ${productoNombre} <br>
                <strong>COLOR:</strong> ${colorNombre}
            </div>
        `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33'
        }).then(async (result) => {
            if (result.isConfirmed) {

                try {

                    Swal.fire({
                        title: 'Generando adhesivos...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const res = await axios.post(
                        route('almacenes.producto.generarAdhesivos'), {
                            almacen_id: almacenId,
                            producto_id: parametrosMdlStocks.producto_id,
                            color_id: colorId
                        }, {
                            responseType: 'blob'
                        }
                    );

                    const contentType = res.headers['content-type'];

                    if (contentType === 'application/json') {
                        const text = await res.data.text();
                        const json = JSON.parse(text);
                        toastr.error(json.message, 'ERROR EN EL SERVIDOR');
                        return;
                    }

                    const file = new Blob([res.data], {
                        type: 'application/pdf'
                    });
                    const fileURL = URL.createObjectURL(file);
                    window.open(fileURL, '_blank');

                } catch (error) {
                    toastr.error('No se pudo generar el PDF', 'ERROR');
                    console.error(error);
                }finally{
                    Swal.close();
                }

            }
        });
    }
</script>
