<div class="row mb-3">

    <!-- Punto de Partida -->
    <div class="col-12 col-md-6 col-lg-6 mb-3">
        <div class="p-2 bg-primary text-white rounded shadow-sm mb-2">
            <i class="fas fa-map-marker-alt"></i> <strong>PUNTO DE PARTIDA</strong>
        </div>

        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="fas fa-building"></i> SEDE DEL ALMACÉN DE LA VENTA
                </h6>
                <p class="card-text font-weight-bold mb-0">
                    {{$sede_origen->nombre}}
                </p>
            </div>
        </div>
    </div>

    <!-- Punto de Llegada -->
    <div class="col-12 col-md-6 col-lg-6 mb-3">
        <div class="p-2 bg-success text-white rounded shadow-sm mb-2">
            <i class="fas fa-map-marker-alt"></i> <strong>PUNTO DE LLEGADA</strong>
        </div>

        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="fas fa-user-shield"></i> CLIENTE
                </h6>
                <p class="card-text font-weight-bold mb-0">
                    {{$cliente->tipo_documento.':'.$cliente->documento.'-'.$cliente->nombre}}
                </p>

                <hr>

                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="fas fa-map-marker"></i> UBIGEO
                </h6>
                <p class="card-text font-weight-bold mb-0">
                    {{$cliente->departamento_nombre.' - '.$cliente->provincia_nombre.' - '.$cliente->distrito_nombre}}
                </p>

                <hr>

                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="fas fa-code"></i> CÓDIGO UBIGEO
                </h6>
                <p class="card-text font-weight-bold mb-0">
                    {{$cliente->distrito_id}}
                </p>

                <hr>

                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="fas fa-map"></i> DIRECCIÓN
                </h6>
                <p class="card-text font-weight-bold mb-0">
                    {{$cliente->direccion}}
                </p>
            </div>
        </div>
    </div>

</div>
