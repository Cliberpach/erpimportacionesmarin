<form action="" id="form-reparto">
    <div class="row">

        <div class="col-12 mb-3">

            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                    <label for="input-observacion" class="font-weight-bold">
                        <i class="fas fa-sticky-note"></i> OBSERVACIÓN
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-comment-dots"></i>
                            </span>
                        </div>
                        <input type="text" id="observacion" class="form-control" name="observacion"
                            placeholder="Ingrese una observación..." maxlength="200">
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12">


            <div class="card">
                <div class="card-header text-white" style="background-color: #0070b0;">
                    <h5 class="card-title mb-0">DETALLE REPARTO</h5>
                </div>
                <div class="card-body">

                    <div class="row">

                        <div class="col-12 mb-3">
                            <label for="input-codigo" class="col-sm-3 col-form-label font-weight-bold">
                                <i class="fas fa-qrcode"></i> AGREGAR CON CÓDIGO
                            </label>
                            <div class="col-sm-9">
                                <input type="text" id="input-codigo" class="form-control"
                                    placeholder="Escanee o ingrese código QR">
                            </div>
                        </div>

                        <div class="col-12">
                            @include('despachos.reparto.tables.tbl_detalle_reparto')
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header text-white" style="background-color: #007bff;">
                    <h5 class="card-title mb-0">PAQUETES EMBALADOS PENDIENTES</h5>
                </div>
                <div class="card-body">

                    <div class="row">

                        <div class="col-12">
                            <div class="table-responsive">
                                @include('despachos.reparto.tables.tbl_paquetes_pendientes')
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </div>
    </div>
</form>
