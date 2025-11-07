<form action="" method="POST" id="form-convertir-doc">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-12">
            <h4 class=""><b>Documento de venta</b></h4>
            <div class="row">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="sede" style="font-weight: bold;">SEDE DEL DOCUMENTO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-building"></i>
                    </span>
                </div>
                <input value="{{ $sede->nombre }}" readonly name="sede" id="sede" type="text"
                    class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="registrador" style="font-weight: bold;">REGISTRADOR</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-user-shield"></i>
                    </span>
                </div>
                <input value="{{ $registrador->usuario }}" readonly name="registrador" id="registrador" type="text"
                    class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>

        <!-- Fecha Registro -->
        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="fecha_registro" style="font-weight: bold;">FECHA REGISTRO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                </div>
                <input value="{{ date('Y-m-d') }}" readonly name="fecha_registro" id="fecha_registro" type="date"
                    class="form-control">
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="almacen" style="font-weight: bold;">ALMACÉN DEL DOCUMENTO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-warehouse"></i>
                    </span>
                </div>
                <input value="{{ $almacen->descripcion }}" readonly name="almacen" id="almacen" type="text"
                    class="form-control">
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
            <label for="cliente" class="required" style="font-weight: bold;">CLIENTE</label>
            <button type="button" class="btn btn-outline btn-primary" onclick="openModalCliente()">Registrar</button>
            <select id="cliente" name="cliente" required onchange="cambiarCliente()">
                <option></option>
                <option value="{{ $cliente->id }}" selected>
                    {{ $cliente->tipo_documento . ':' . $cliente->documento . '-' . $cliente->nombre }}</option>
            </select>
            <span style="font-weight: bold;color:red;" class="cliente_error msgError"></span>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="telefono" style="font-weight: bold;">TELÉFONO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-phone"></i>
                    </span>
                </div>
                <input readonly value="{{ $cliente->telefono_movil }}" name="telefono" id="telefono" type="text"
                    class="form-control inputEnteroPositivo" placeholder="Número de teléfono" aria-label="Telefono"
                    aria-describedby="basic-addon1">
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="documento" style="font-weight: bold;">DOCUMENTO</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-file-invoice"></i>
                    </span>
                </div>
                <input value="{{ $documento->serie . '-' . $documento->correlativo }}" readonly name="documento"
                    id="documento" type="text" class="form-control">
            </div>
        </div>

        <div class="col-12 col-lg-3 col-md-3 mb-3">
            <label for="tipo_comprobante" style="font-weight: bold;">COMPROBANTE:</label>
            <select required name="tipo_comprobante" id="tipo_comprobante" class="select2_form"
                onchange="cambiarTipoComprobante(this.value)">
                <option value=""></option>
                @foreach ($tipos_comprobante as $comprobante)
                    <option
                    @if ($cliente->tipo_documento == 'DNI' && $comprobante->id == 128)
                        selected
                    @endif
                    @if ($cliente->tipo_documento == 'RUC' && $comprobante->id == 127)
                        selected
                    @endif
                    value="{{ $comprobante->id }}">{{ $comprobante->descripcion }}</option>
                @endforeach
            </select>
        </div>

    </div>

</form>
