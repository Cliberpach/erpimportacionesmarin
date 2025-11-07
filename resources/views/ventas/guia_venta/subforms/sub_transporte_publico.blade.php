<div class="row mb-3" id="divTransportista">

    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
        <label class="required" for="vehiculo" style="font-weight: bold;">VEHÍCULO</label>
        <select required required name="vehiculo" required class="form-select select2_form" id="vehiculo" data-placeholder="Seleccionar">
            <option></option>
            @foreach ($vehiculos as $vehiculo)

                <option value="{{$vehiculo->id}}">{{$vehiculo->placa.'-'.$vehiculo->modelo}}</option>

            @endforeach
        </select>
        <span class="vehiculo_error msgError"  style="color:red;"></span>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
        <label class="required" for="conductor" style="font-weight: bold;">CONDUCTOR</label>
        <select required required name="conductor" required class="form-select select2_form" id="conductor" data-placeholder="Seleccionar">
            <option></option>
            @foreach ($conductores as $conductor)
                <option value="{{$conductor->id}}">{{$conductor->tipo_documento_nombre.':'.$conductor->nro_documento.'-'.$conductor->nombre_completo}}</option>
            @endforeach
        </select>
        <span class="conductor_error msgError"  style="color:red;"></span>
    </div>
</div>
