<form action="" id="formRegistrarConductor" method="post">
    <div class="row">
        @csrf
        <div class="col-12">
            <div class="row" id="formRegistrarContenido">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
                    <label class="required_field" for="tipo_documento" style="font-weight: bold;">TIPO DOCUMENTO</label>
                    <select required name="tipo_documento" required class="form-select select2_form" id="tipo_documento"
                        data-placeholder="Seleccionar" onchange="changeTipoDoc()">
                        <option></option>
                        @foreach ($tipos_documento as $tipo_documento)
                            <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->descripcion }}</option>
                        @endforeach
                    </select>
                    <span class="tipo_documento_error msgError" style="color:red;"></span>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
                    <label for="nro_documento" style="font-weight: bold;" class="required_field">Nro Doc</label>
                    <div class="input-group mb-3">
                        <button disabled class="btn btn-primary btn_consultar_documento" type="button"
                            id="button-addon1">
                            <i class="fa fa-search"></i>
                        </button>
                        <input required readonly id="nro_documento" name="nro_documento" type="text"
                            class="form-control" placeholder="Nro de Documento"
                            aria-label="Example text with button addon" aria-describedby="button-addon1">
                    </div>
                    <span class="nro_documento_error msgError" style="color:red;"></span>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
                    <label for="nombres" style="font-weight: bold;" class="required_field">Nombres</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa fa-user-check"></i>
                        </span>
                        <input required id="nombres" maxlength="150" name="nombres" type="text"
                            class="form-control" placeholder="Nombre" aria-label="Username"
                            aria-describedby="basic-addon1">
                    </div>
                    <span style="color:rgb(0, 89, 255); font-style: italic;">(150 LONGITUD MÁXIMA)</span>
                    <span class="nombres_error msgError" style="color:red;"></span>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2" id="divApellido">
                    <label for="apellidos" style="font-weight: bold;" class="required_field">Apellidos</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa fa-user-check"></i>
                        </span>
                        <input required id="apellidos" maxlength="150" name="apellidos" type="text"
                            class="form-control" placeholder="Apellidos" aria-label="Username"
                            aria-describedby="basic-addon1">
                    </div>
                    <span style="color:rgb(0, 89, 255); font-style: italic;display:block;">(150 LONGITUD MÁXIMA)</span>
                    <span class="apellidos_error msgError" style="color:red;"></span>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2" id="divLicencia">
                    <label class="required_field" for="licencia" style="font-weight: bold;">LICENCIA</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa fa-id-card"></i>
                        </span>
                        <input minlength="9" maxlength="10" required id="licencia" name="licencia" type="text"
                            class="form-control" placeholder="Licencia" aria-label="Username"
                            aria-describedby="basic-addon1">
                    </div>
                    <span style="color:rgb(0, 89, 255); font-style: italic;display:block;">(9 - 10 CARACTERES
                        ALFANUMÉRICOS)</span>
                    <span class="licencia_error msgError" style="color:red;"></span>
                </div>

            </div>
        </div>
    </div>
</form>
