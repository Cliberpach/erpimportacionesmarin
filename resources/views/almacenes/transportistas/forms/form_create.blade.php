<form action="" id="formRegistrarTransportista" method="post">
    <div class="row">
            @csrf

            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
                <label class="required_field" for="tipo_documento" style="font-weight: bold;">TIPO DOCUMENTO</label>
                <select required name="tipo_documento" required class="form-select select2_form" id="tipo_documento" data-placeholder="Seleccionar" onchange="changeTipoDoc()">
                    <option></option>
                    @foreach ($tipos_documento as $tipo_documento)
                        <option value="{{$tipo_documento->id}}">{{$tipo_documento->descripcion}}</option>
                    @endforeach
                </select>
                <span class="tipo_documento_error msgError"  style="color:red;"></span>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
                <label for="nro_documento" style="font-weight: bold;" class="required_field">N° DOC</label>
                <div class="input-group mb-3">
                    <button id="btn_consultar_documento" disabled class="btn btn-primary" type="button" id="button-addon1">
                        <i class="fas fa-search" style="color:white;"></i>
                    </button>
                    <input required readonly id="nro_documento" name="nro_documento" type="text" class="form-control" placeholder="N° DOCUMENTO" aria-label="Example text with button addon" aria-describedby="button-addon1">
                </div>
                <span class="nro_documento_error msgError"  style="color:red;"></span>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
                <label for="nombre" style="font-weight: bold;" class="required_field">NOMBRE</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-user-tie"></i>
                    </span>
                    <input required id="nombre" maxlength="160"  name="nombre" type="text" class="form-control" placeholder="NOMBRE" aria-label="Username" aria-describedby="basic-addon1">
                </div>
                <span class="nombre_error msgError"  style="color:red;"></span>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2 mb-3">
                <label  for="direccion" style="font-weight: bold;">DIRECCIÓN FISCAL</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-id-card"></i>
                    </span>
                    <input maxlength="200"  id="direccion" name="direccion" type="text" class="form-control" placeholder="DIRECCIÓN FISCAL" aria-label="Username" aria-describedby="basic-addon1">
                </div>
                <span class="direccion_error msgError"  style="color:red;"></span>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2 mb-3">
                <label  for="mtc" style="font-weight: bold;">MTC</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-id-card"></i>
                    </span>
                    <input maxlength="20"  id="mtc" name="mtc" type="text" class="form-control" placeholder="MTC" aria-label="Username" aria-describedby="basic-addon1">
                </div>
                <span class="mtc_error msgError"  style="color:red;"></span>
                <span style="color:rgb(0, 89, 255); font-style: italic;font-size:14px;">(SE PERMITE 20 CARACTERES COMO MÁXIMO, LETRAS MAYÚSCULAS Y NÚMEROS SIN ESPACIOS,SÍMBOLOS.)</span>
            </div>


    </div>
</form>
