<!-- Card general de información -->
<div class="col-12 mb-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h5 class="card-title text-primary mb-4">
                <i class="fas fa-truck-loading"></i> Detalle del Traslado
            </h5>

            <div class="row gy-3">

                <!-- Registrador -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-user text-info fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Registrador</small>
                            <span class="font-weight-bold">{{ $traslado->registrador_nombre }}</span>
                        </div>
                    </div>
                </div>

                <!-- Aprobador -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-check text-success fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Aprobador</small>
                            <span class="font-weight-bold">{{ $traslado->aprobador_nombre }}</span>
                        </div>
                    </div>
                </div>

                <!-- Fecha Aprobación -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-calendar-check text-warning fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Fecha Aprobación</small>
                            <span class="font-weight-bold">
                                {{ $traslado->fecha_aprobacion ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-signal text-primary fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Estado</small>
                            <span
                                class="badge
                                    @if ($traslado->estado == 'PENDIENTE') badge-danger
                                    @elseif($traslado->estado == 'ENVIADO') badge-primary
                                    @elseif($traslado->estado == 'RECIBIDO') badge-success
                                    @elseif($traslado->estado == 'ENTREGADO') badge-info
                                    @else badge-secondary @endif">
                                {{ $traslado->estado }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Fecha Registro -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-calendar text-muted fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Fecha Registro</small>
                            <span class="font-weight-bold">
                                {{ $traslado->created_at->format('Y-m-d') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Usuario Envío -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-paper-plane text-info fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Usuario Envío</small>
                            <span class="font-weight-bold">
                                {{ $traslado->usuario_envio_nombre ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Fecha Envío -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-calendar-day text-primary fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Fecha Envío</small>
                            <span class="font-weight-bold">
                                {{ $traslado->fecha_traslado ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Usuario Entrega -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-user-tag text-success fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Usuario Entrega</small>
                            <span class="font-weight-bold">
                                {{ $traslado->usuario_entrega_nombre ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Fecha Entrega -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-calendar-check text-success fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Fecha Entrega</small>
                            <span class="font-weight-bold">
                                {{ $traslado->fecha_entrega ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Almacén Origen -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-warehouse text-secondary fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Almacén Origen</small>
                            <span class="font-weight-bold">{{ $almacen_origen->descripcion }}</span>
                        </div>
                    </div>
                </div>

                <!-- Almacén Destino -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-warehouse text-secondary fa-lg mr-2"></i>
                        <div>
                            <small class="text-muted d-block">Almacén Destino</small>
                            <span class="font-weight-bold">{{ $almacen_destino->descripcion }}</span>
                        </div>
                    </div>
                </div>

                <!-- Observación -->
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <div class="d-flex align-items-start">
                        <i class="fa fa-comment-dots text-warning fa-lg mr-2 mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Observación</small>
                            <span class="font-weight-bold d-block">{{ $traslado->observacion ?? '-' }}</span>
                        </div>
                    </div>
                </div>

            </div> <!-- /row -->
        </div>
    </div>
</div>
