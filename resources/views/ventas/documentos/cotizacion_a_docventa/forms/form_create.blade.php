<form action="" method="POST" id="enviar_documento">
    {{ csrf_field() }}

    @if (!empty($cotizacion))
        <input type="hidden" name="cotizacion_id" value="{{ $cotizacion->id }}">
        <input type="hidden" name="data_envio" id="data_envio">
    @endif
    <div class="row">
        <div class="col-12 col-md-6 b-r">
            <div class="row">
                <div class="col-12 col-md-6" id="fecha_documento">
                    <div class="form-group">
                        <label class="" style="font-weight: bold;">FECHA DOCUMENTO</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            <input type="date" id="fecha_documento_campo" name="fecha_documento_campo"
                                class="form-control" value="{{ old('fecha_documento_campo', date('Y-m-d')) }}"
                                autocomplete="off" required readonly>

                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 select-required">
                    <div class="form-group">
                        <label class="required" style="font-weight: bold;">COMPROBANTE: </label>
                        <select
                            class="select2_form form-control required_field"
                            style="text-transform: uppercase; width:100%" value="{{ old('tipo_comprobante') }}"
                            name="tipo_comprobante" id="tipo_comprobante" required
                            onchange="cambiarTipoComprobante(this.value)">
                            <option></option>
                            @foreach ($tipos_venta as $tipo)
                                <option value="{{ $tipo->id }}" @if ($tipo->id == 129) selected @endif>
                                    {{ $tipo->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-6" id="fecha_entrega">
                    <div class="form-group d-none">
                        <label class="">FECHA ATENCIÓN</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            @if (!empty($cotizacion))
                                <input type="date" id="fecha_atencion_campo" name="fecha_atencion_campo"
                                    class="form-control {{ $errors->has('fecha_atencion') ? ' is-invalid' : '' }}"
                                    value="{{ old('fecha_atencion', $cotizacion->fecha_atencion) }}" autocomplete="off"
                                    readonly disabled>
                            @else
                                <input type="date" id="fecha_atencion_campo" name="fecha_atencion_campo"
                                    class="form-control input-required {{ $errors->has('fecha_atencion') ? ' is-invalid' : '' }}"
                                    value="{{ old('fecha_atencion', $fecha_hoy) }}" autocomplete="off" required
                                    readonly disabled>
                            @endif

                            @if ($errors->has('fecha_atencion'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('fecha_atencion') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: bold;">OBSERVACIÓN</label>
                        <textarea placeholder="Ingresar observación" class="form-control input-required" name="observacion" id="observacion"
                            onkeyup="return mayus(this)" maxlength="200" rows="3">{{ old('observacion') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-12 col-md-6 select-required d-none">
                    <div class="form-group">
                        <label>Moneda:</label>
                        <select id="moneda" name="moneda"
                            class="select2_form form-control {{ $errors->has('moneda') ? ' is-invalid' : '' }}"
                            disabled>
                            <option selected>SOLES</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">

            <div class="row">
                <div class="col-12 col-md-6 select-required">
                    <div class="form-group">
                        @if (!empty($cotizacion))
                            <label class="required" style="font-weight: bold;">CONDICIÓN</label>
                            <select id="condicion_id" name="condicion_id" class="select2_form form-control"
                                onchange="changeFormaPago()" disabled>
                                <option></option>
                                @foreach ($condiciones as $condicion)
                                    <option value="{{ $condicion->id }}-{{ $condicion->descripcion }}"
                                        {{ old('condicion_id') == $condicion->id . '-' . $condicion->descripcion || $condicion->id == $cotizacion->condicion_id ? 'selected' : '' }}
                                        data-dias="{{ $condicion->dias }}">
                                        {{ $condicion->descripcion }}
                                        {{ $condicion->dias > 0 ? $condicion->dias . ' dias' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <label class="required">Condición</label>
                            <select id="condicion_id" name="condicion_id"
                                class="select2_form form-control {{ $errors->has('condicion_id') ? ' is-invalid' : '' }}"
                                required onchange="changeFormaPago()">
                                <option></option>
                                @foreach ($condiciones as $condicion)
                                    <option value="{{ $condicion->id }}-{{ $condicion->descripcion }}"
                                        {{ old('condicion_id') == $condicion->id . '-' . $condicion->descripcion || $condicion->descripcion == 'CONTADO' ? 'selected' : '' }}
                                        data-dias="{{ $condicion->dias }}">
                                        {{ $condicion->descripcion }}
                                        {{ $condicion->dias > 0 ? $condicion->dias . ' dias' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6" id="fecha_vencimiento">
                    <div class="form-group">
                        <label class="required" style="font-weight: bold;">FECHA VENCIMIENTO</label>
                        <div class="input-group date">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <input type="date" id="fecha_vencimiento_campo" name="fecha_vencimiento_campo"
                                class="form-control input-required" autocomplete="off"
                                {{ $errors->has('fecha_vencimiento_campo') ? ' is-invalid' : '' }}
                                value="{{ old('fecha_vencimiento_campo', date('Y-m-d')) }}" required>
                            @if ($errors->has('fecha_vencimiento_campo'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('fecha_vencimiento_campo') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-end">

                <div class="col-lg-12 col-md-6 col-sm-12 col-xs-12 select-required mb-3">
                    <label class="required" style="font-weight: bold;">CLIENTE:
                        <button type="button" class="btn btn-outline btn-primary" onclick="openModalCliente()">
                            Registrar
                        </button>
                    </label>
                    <select id="cliente" name="cliente" onchange="cambiarCliente()"
                        class="select2_form form-control required_field" required>
                        <option value="{{ $cliente->id }}"
                            data-telefono="{{ $cliente->telefono_movil }}"
                            data-departamento-id="{{ $cliente->departamento_id }}"
                            data-provincia-id="{{ $cliente->provincia_id }}"
                            data-distrito-id="{{ $cliente->distrito_id }}">
                            {{ $cliente->tipo_documento . ':' . $cliente->documento . '-' . $cliente->nombre }}
                        </option>
                    </select>
                    <span style="font-weight: bold;color:red;" class="fecha_propuesta_error msgError"></span>
                </div>
                <div class="col-12 col-lg-6 col-md-6 mb-3">
                    <label for="telefono" style="font-weight: bold;">TELÉFONO</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="fas fa-phone"></i>
                            </span>
                        </div>
                        <input value="{{ $cotizacion->telefono }}" name="telefono" id="telefono" type="text"
                            class="form-control inputEnteroPositivo input-required" placeholder="Número de teléfono"
                            aria-label="Telefono" aria-describedby="basic-addon1">
                    </div>
                </div>

            </div>


        </div>
    </div>

    <div class="row">

        <div class="col-lg-12">
            <div class="panel panel-success" id="panel_detalle">
                <div class="panel-heading">
                    <h4 class=""><b>Detalle del Documento de Venta</b></h4>
                </div>
                <div class="panel-body ibox-content">

                    <div class="mt-3" style="margin-top: 10px;">
                        <small><strong>🔎 Leyenda de validación:</strong></small>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 4px; flex-wrap: wrap;">
                            <span>
                                <i class="fas fa-square" style="color: #ff6666;"></i>
                                <span style="color: #ff6666; font-weight: bold;">STOCK INSUFICIENTE</span>
                            </span>
                            <span>
                                <i class="fas fa-square" style="color: #9966cc;"></i>
                                <span style="color: #9966cc; font-weight: bold;">TALLA NO EXISTE</span>
                            </span>
                            <span>
                                <i class="fas fa-square" style="color: #000000;"></i>
                                <span style="font-weight: bold;">STOCK VÁLIDO</span>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                @include('ventas.documentos.cotizacion_a_docventa.tables.table-detalle-cvc')
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                @include('ventas.documentos.cotizacion_a_docventa.tables.tbl_montos')
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

    </div>
</form>
