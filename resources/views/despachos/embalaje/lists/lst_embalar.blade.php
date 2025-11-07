<div class="row">

    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <!-- ================= DATOS DEL CLIENTE ================= -->
        <h6 class="text-success font-weight-bold mb-3">
            <i class="fas fa-user"></i> Cliente
        </h6>
        <div class="row">
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-user text-success"></i> Cliente:</label>
                <div id="show_cliente_nombre">Juan Pérez</div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-phone text-success"></i> Celular:</label>
                <div id="show_cliente_celular">987654321</div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-credit-card text-success"></i> Tipo Pago:</label>
                <div id="show_tipo_pago_envio">Yape</div>
            </div>
        </div>
        <hr>

        <!-- ================= INFORMACIÓN DE ENVÍO ================= -->
        <h6 class="text-success font-weight-bold mb-2">
            <i class="fas fa-truck"></i> Envío
        </h6>
        <div class="row">
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-building text-success"></i> Empresa
                    Envío:</label>
                <div id="show_empresa_envio_nombre"></div>
            </div>
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-map text-success"></i> Sede Envío:</label>
                <div id="show_sede_envio_nombre"></div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-truck-moving text-success"></i> Tipo
                    Envío:</label>
                <div id="show_tipo_envio">Express</div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-home text-success"></i> Entrega
                    Domicilio:</label>
                <div id="show_entrega_domicilio">Sí</div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-map-marker-alt text-success"></i> Dirección
                    Entrega:</label>
                <div id="show_direccion_entrega">Av. Siempre Viva 123</div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-shopping-cart text-success"></i> Venta:</label>
                <div id="show_documento_nro">12345678</div>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold"><i class="fas fa-calendar-alt text-success"></i> Fecha
                    Envío:</label>
                <div id="show_fecha_envio_propuesta">2025-08-21</div>
            </div>
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-flag text-success"></i> Estado:</label>
                <div id="show_estado">Pendiente</div>
            </div>

        </div>
        <hr>

        <!-- ================= DATOS DEL DESTINATARIO ================= -->
        <h6 class="text-success font-weight-bold mb-3">
            <i class="fas fa-user-tag"></i> Destinatario
        </h6>
        <div class="row">
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-id-badge text-success"></i> Tipo
                    Doc:</label>
                <div id="show_destinatario_tipo_doc">DNI</div>
            </div>
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-file-alt text-success"></i> N°
                    Documento:</label>
                <div id="show_destinatario_nro_doc">87654321</div>
            </div>
            <div class="col-md-4 mb-2" style="background-color:rgb(255, 255, 208);">
                <label class="font-weight-bold"><i class="fas fa-user text-success"></i> Nombre:</label>
                <div id="show_destinatario_nombre">María López</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-12 mb-3">
                <h6 class="text-success font-weight-bold mb-3 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-layer-group"></i> Envios Agrupar
                    </span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success btn-sm" id="btn_generar_paquete">
                            <i class="fas fa-box"></i> Generar Paquete
                        </button>
                        <a href="javascript:void(0);" id="btn_generar_guia" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-file-alt"></i> Generar Guía Remisión
                        </a>
                    </div>
                </h6>

                <div class="table-responsive">
                    @include('despachos.embalaje.tables.tbl_agrupar_embalaje')
                </div>
            </div>

            <div class="col-12">
                <h6 class="text-success font-weight-bold mb-3 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-layer-group"></i> Detalle
                    </span>
                </h6>

                <div class="table-responsive">
                    @include('despachos.embalaje.tables.tbl_detalle_embalaje')
                </div>
            </div>
        </div>
    </div>




</div>
