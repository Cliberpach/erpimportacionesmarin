<form method="POST" action="" id="formActualizarPedido">
    @csrf

    <div class="row">

        <div class="col-12">
            <h4><b>Datos Generales</b></h4>
        </div>

        <input type="hidden" name="data_envio" id="data_envio">

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="registrador" style="font-weight: bold;">REGISTRADOR</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-user-shield"></i>
                    </span>
                </div>
                <input value="{{ $pedido->user_nombre }}" readonly name="registrador" id="registrador" type="text"
                    class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="fecha_registro" style="font-weight: bold;">FECHA REGISTRO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                </div>
                <input value="{{ $pedido->fecha_registro }}" readonly name="fecha_registro" id="fecha_registro"
                    type="date" class="form-control">
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 d-none">
            <div class="form-group">
                <label style="font-weight: bold;" class="required" for="almacen">ALMACÉN</label>
                <select @if ($pedido->estado === 'ATENDIENDO') disabled @endif onchange="cambiarAlmacen(this.value)"
                    id="almacen" name="almacen" class="select2_form form-control" required>
                    <option></option>
                    @foreach ($almacenes as $almacen)
                        <option @if ($almacen->id == $pedido->almacen_id) selected @endif value="{{ $almacen->id }}">
                            {{ $almacen->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span style="font-weight: bold;color:red;" class="almacen_error msgError"></span>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 d-none">
            <div class="form-group">
                <label style="font-weight: bold;" class="required" for="condicion_id">CONDICIÓN</label>
                <select id="condicion_id" name="condicion_id" class="select2_form form-control" required>
                    <option></option>
                    @foreach ($condiciones as $condicion)
                        <option value="{{ $condicion->id }}" @if ($condicion->id == $pedido->condicion_id) selected @endif>
                            {{ $condicion->descripcion }} {{ $condicion->dias > 0 ? $condicion->dias . ' días' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span style="font-weight: bold;color:red;" class="condicion_id_error msgError"></span>
        </div>

        <div class="col-lg-4 col-md-3 col-sm-12 col-xs-12"
            style="display: flex;flex-direction:column;justify-content:center;">
            <label class="required" style="font-weight: bold;">FECHA PROPUESTA</label>
            <div class="d-flex align-items-end">
                <input value="{{ $pedido->fecha_propuesta }}" required type="date" class="form-control"
                    id="fecha_propuesta" name="fecha_propuesta">
            </div>
            <span style="font-weight: bold;color:red;" class="fecha_propuesta_error msgError"></span>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12 select-required">
            <div class="form-group">
                <label class="required" style="font-weight: bold;">CLIENTE:
                    <button type="button" class="btn btn-outline btn-primary" onclick="openModalCliente()">
                        Registrar
                    </button>
                </label>
                <select id="cliente" name="cliente" class="select2_form form-control" required
                    onchange="elegirCliente()">
                    <option value="{{ $cliente->id }}" data-telefono="{{ $cliente->telefono_movil }}"
                        data-departamento-id="{{ $cliente->departamento_id }}"
                        data-provincia-id="{{ $cliente->provincia_id }}"
                        data-distrito-id="{{ $cliente->distrito_id }}">
                        {{ $cliente->tipo_documento . ':' . $cliente->documento . '-' . $cliente->nombre }}
                    </option>
                </select>
                <span style="font-weight: bold;color:red;" class="fecha_propuesta_error msgError"></span>
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="telefono" style="font-weight: bold;">TELÉFONO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon-telefono">
                        <i class="fas fa-phone-alt"></i>
                    </span>
                </div>
                <input value="{{ $pedido->telefono }}" maxlength="9" name="telefono" id="telefono" type="tel"
                    class="form-control inputEnteroPositivo" placeholder="Ingrese teléfono" aria-label="Teléfono"
                    aria-describedby="basic-addon-telefono">
            </div>
            <span style="font-weight: bold;color:red;" class="telefono_error msgError"></span>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group">
                <label style="font-weight: bold;" class="required" for="origen_venta">ORIGEN</label>
                <select id="origen_venta" name="origen_venta" class="select2_form form-control" required>
                    <option></option>
                    @foreach ($origenes_ventas as $origen_venta)
                        <option
                        @if ($origen_venta->id == $pedido->origen_venta_id)
                            selected
                        @endif
                        value="{{ $origen_venta->id }}">
                            {{ $origen_venta->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span style="font-weight: bold;color:red;" class="origen_venta_error msgError"></span>
        </div>
    </div>

    <hr>
    <div class="row">
        <div class="col-lg-12 col-xs-12">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4><b>Seleccionar productos</b></h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">


                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-3">
                                    <label class="required" style="font-weight: bold;">CATEGORÍA - MARCA
                                        - MODELO - PRODUCTO</label>
                                    <select id="producto" class="" onchange="getColoresTallas()">
                                        <option value=""></option>
                                    </select>
                                </div>

                            </div>

                            <div class="row">

                                <!-- Input precio final -->
                                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
                                    <label class="required" style="font-weight: bold;">PRECIO
                                        VENTA</label>
                                    <input class="form-control font-weight-bold" type="text"
                                        oninput="formatearPrecioVentaFinal(event)" id="precio_venta" />
                                </div>

                                <!-- Precio original y descuento -->
                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 d-flex align-items-center">
                                    <div class="d-flex flex-column align-items-start mt-2 mt-md-0">
                                        <span id="precio_original_span" class="text-muted">
                                            S/ <span id="precio_original_text"></span>
                                        </span>

                                        <span id="descuento_span" class="text-success font-weight-bold"
                                            style="display: none;">

                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        @include('pedidos.pedido.tables.table_pedido_stocks')
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-lg-2 col-xs-12">
                                    <button disabled type="button" id="btn_agregar_detalle"
                                        class="btn btn-warning btn-block"><i class="fa fa-plus"></i>
                                        AGREGAR</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4>
                        <b>Detalle del Pedido</b>
                    </h4>
                    <h5>
                        <span>
                            Pueden agregarse o retirarse productos del detalle, pero las cantidades
                            atendidas no podrán modificarse. Si desea modificar cantidades atendidas
                            debe realizar
                            notas de crédito,devolución o cambios de talla sobre el documento venta de
                            atención.
                        </span>
                    </h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        @include('pedidos.pedido.tables.table_pedido_detalle')
                    </div>
                </div>
                <div class="panel-footer panel-success">
                    <div class="table-responsive">
                        @include('pedidos.pedido.tables.table_montos_atender')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($cant_pagos == 0)
        <div class="row">
            <div class="col-12">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h4><b>Datos de Pago</b></h4>
                    </div>
                    <div class="panel-body">

                        @if ($cuenta_cliente)
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <h5 class="mb-3">
                                    <i class="fas fa-user-circle text-primary"></i> Cuenta Cliente
                                </h5>
                                <div class="row">
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <i class="fas fa-file-alt text-secondary"></i>
                                        <strong>N° Documento:</strong> {{ $cuenta_cliente->numero_doc }}
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <i class="fas fa-calendar-alt text-warning"></i>
                                        <strong>Fecha:</strong> {{ $cuenta_cliente->fecha_doc }}
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <strong>Monto:</strong> S/ {{ number_format($cuenta_cliente->monto, 2) }}
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <i class="fas fa-wallet text-info"></i>
                                        <strong>Saldo:</strong> S/ {{ number_format($cuenta_cliente->saldo, 2) }}
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <i class="fas fa-info-circle text-dark"></i>
                                        <strong>Estado:</strong>
                                        @if ($cuenta_cliente->estado === 'PAGADO')
                                            <span class="badge badge-success">{{ $cuenta_cliente->estado }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $cuenta_cliente->estado }}</span>
                                        @endif
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <i class="fas fa-file-signature text-primary"></i>
                                        <strong>Acta:</strong> {{ $cuenta_cliente->acta }}
                                    </div>
                                </div>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                <label for="metodo_pago_1" style="font-weight: bold;">MÉTODO PAGO</label>
                                <select name="metodo_pago_1" id="metodo_pago_1" class="select2_form"
                                    onchange="cambiarMetodoPago1(this.value)">
                                    @foreach ($metodos_pago as $metodo_pago)
                                        <option value="{{ $metodo_pago->id }}">{{ $metodo_pago->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-3 col-sm-6 col-xs-12">
                                <label for="cuenta_1" style="font-weight: bold;">CUENTAS</label>
                                <select name="cuenta_1" id="cuenta_1" class="select2_form">

                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                                <label for="monto_1" style="font-weight: bold;">MONTO</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control monto_1" id="monto_1"
                                        placeholder="Monto" name="monto_1">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
                                <label for="nro_operacion_1" style="font-weight: bold;">N°. OPERACIÓN</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-receipt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control nro_operacion_1" id="nro_operacion_1"
                                        placeholder="N°. operación" name="nro_operacion_1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-3 col-sm-6 col-xs-12">

                                <label for="img_pago_1" style="font-weight: bold;"> IMAGEN</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupFileAddon01">
                                            <i class="fas fa-image"></i>
                                        </span>
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="img_pago_1"
                                            aria-describedby="inputGroupFileAddon01" name="img_pago_1"
                                            accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                                        <label class="custom-file-label" for="img_pago_1">Imagen</label>
                                    </div>
                                </div>
                                <small class="text-muted">Formatos permitidos: JPG, JPEG, PNG. Máx: 2MB</small>
                            </div>
                            <div class="col-12"></div>
                            <div class="col-4 border d-flex align-items-center justify-content-center"
                                style="min-height: 100px;">
                                <img id="previewImage1" class="imgShowLightBox" src="{{ asset('img/default.png') }}"
                                    alt="Vista previa" style="max-width: 100%; max-height: 90px; object-fit: cover;">
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
                                <label for="fecha_operacion_1" style="font-weight: bold;">FECHA OPERACIÓN</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-receipt"></i>
                                        </span>
                                    </div>
                                    <input value="<?php echo date('Y-m-d'); ?>"type="date" name="fecha_operacion_1"
                                        class="form-control fecha_operacion_1" id="fecha_operacion_1"
                                        placeholder="Fecha operación">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
                                <label style="font-weight: bold;" for="modo_despacho"
                                    class="col-form-label required">
                                    MODO DESPACHO</label>
                                <select name="modo_despacho" id="modo_despacho" class="modo_despacho select2_form"
                                    data-placeholder="SELECCIONAR">
                                    <option selected value="RESERVA">RESERVA</option>
                                    <option value="ATENCION">ATENCION</option>
                                </select>
                                <span class="modo_despacho_error msgError"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{--
    <div class="row">
        <div class="col-12">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4><b>Detalle Atendiendo</b></h4>
                </div>
                <div class="panel-body">
                    @include('pedidos.pedido.tables.table_pedido_atencion_historial')
                    </div>
                </div>
            </div>
        </div>
    --}}

</form>
