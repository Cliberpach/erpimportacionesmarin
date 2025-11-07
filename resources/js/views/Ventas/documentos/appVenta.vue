<style>
.overlay_venta {
    position: fixed;
    /* Fija el overlay para que cubra todo el viewport */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    /* Color oscuro con opacidad */
    z-index: 99999999999 !important;
    /* Asegura que el overlay esté sobre todo */
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 24px;
    visibility: hidden;
}

/*========== LOADER SPINNER =======*/
.loader_cotizacion_create {
    position: relative;
    width: 75px;
    height: 100px;
    background-repeat: no-repeat;
    background-image: linear-gradient(#DDD 50px, transparent 0),
        linear-gradient(#DDD 50px, transparent 0),
        linear-gradient(#DDD 50px, transparent 0),
        linear-gradient(#DDD 50px, transparent 0),
        linear-gradient(#DDD 50px, transparent 0);
    background-size: 8px 100%;
    background-position: 0px 90px, 15px 78px, 30px 66px, 45px 58px, 60px 50px;
    animation: pillerPushUp 4s linear infinite;
}

.loader_cotizacion_create:after {
    content: '';
    position: absolute;
    bottom: 10px;
    left: 0;
    width: 10px;
    height: 10px;
    background: #de3500;
    border-radius: 50%;
    animation: ballStepUp 4s linear infinite;
}

@keyframes pillerPushUp {

    0%,
    40%,
    100% {
        background-position: 0px 90px, 15px 78px, 30px 66px, 45px 58px, 60px 50px
    }

    50%,
    90% {
        background-position: 0px 50px, 15px 58px, 30px 66px, 45px 78px, 60px 90px
    }
}

@keyframes ballStepUp {
    0% {
        transform: translate(0, 0)
    }

    5% {
        transform: translate(8px, -14px)
    }

    10% {
        transform: translate(15px, -10px)
    }

    17% {
        transform: translate(23px, -24px)
    }

    20% {
        transform: translate(30px, -20px)
    }

    27% {
        transform: translate(38px, -34px)
    }

    30% {
        transform: translate(45px, -30px)
    }

    37% {
        transform: translate(53px, -44px)
    }

    40% {
        transform: translate(60px, -40px)
    }

    50% {
        transform: translate(60px, 0)
    }

    57% {
        transform: translate(53px, -14px)
    }

    60% {
        transform: translate(45px, -10px)
    }

    67% {
        transform: translate(37px, -24px)
    }

    70% {
        transform: translate(30px, -20px)
    }

    77% {
        transform: translate(22px, -34px)
    }

    80% {
        transform: translate(15px, -30px)
    }

    87% {
        transform: translate(7px, -44px)
    }

    90% {
        transform: translate(0, -40px)
    }

    100% {
        transform: translate(0, 0);
    }
}
</style>

<template>
    <div>
        <div class="overlay_venta">
            <span class="loader_cotizacion_create"></span>
        </div>
        <div class="row wrapper border-bottom white-bg page-heading align-items-end">
            <div class="col-12 col-md-10">
                <h2 style="text-transform:uppercase">
                    <b v-if="ruta == 'index'">Lista de Documentos de Venta</b>
                    <b v-if="ruta == 'create'">REGISTRAR NUEVO DOCUMENTO DE VENTA</b>
                </h2>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a :href="routes('HOME')">Panel de Controls</a>
                    </li>
                    <li class="breadcrumb-item" :class="ruta == 'index' ? 'active' : ''">
                        <template v-if="ruta == 'index'">
                            <strong>Documentos de Ventas</strong>
                        </template>
                        <template v-else>
                            <a href="javascript:void(0)" @click.prevent="ruta = 'index'">
                                Documentos de Ventas
                            </a>
                        </template>
                    </li>
                    <li class="breadcrumb-item" v-if="ruta == 'create' ? 'active' : ''"
                        :class="ruta == 'create' ? 'active' : ''">
                        <strong>Registrar</strong>
                    </li>
                </ol>
            </div>
            <div class="col-lg-2 col-md-2" v-if="ruta == 'index'">
                <button type="button" class="btn btn-block btn-w-m btn-success m-t-md" @click.prevent="irVentaNueva">
                    <i class="fa fa-plus-square"></i> NUEVO
                </button>
            </div>
        </div>
        <template v-if="ruta == 'index'">
            <venta-lista :imginicial="imginicial" :lst_modos_pago="this.lst_modos_pago" />
        </template>
        <template v-if="ruta == 'create'">
            <venta-create :ruta.sync="ruta" :v_sede="this.v_sede" :registrador="this.registrador"
                :idcotizacion="idcotizacion"
                :lst_almacenes="this.lst_almacenes"
                :lst_departamentos_base="this.lst_departamentos_base"
                :lst_provincias_base="this.lst_provincias_base"
                :lst_distritos_base="this.lst_distritos_base"
                :lst_metodos_pago = "this.lst_modos_pago"
                :lst_origenes_ventas="this.lst_origenes_ventas" />
        </template>
    </div>
</template>

<script>

export default {
    name: "AppVue",
    props: [
        "imginicial",
        "v_sede",
        "registrador",
        "lst_almacenes",
        "ls_origenes_ventas",
        "lst_departamentos_base",
        "lst_provincias_base",
        "lst_distritos_base",
        "lst_modos_pago",
        "lst_origenes_ventas"
    ],
    components: {
    },
    data() {
        return {
            ruta: "index",
            idcotizacion: 0
        }
    },
    watch: {
        ruta(data) {

            if (data == "create") {
                let cotizacion = this.idcotizacion == 0 ? '' : '?cotizacion=' + this.idcotizacion;
                let url = route('ventas.documento.create') + cotizacion;
                history.pushState(null, "", url);
            }

            if (data == "index") {
                history.pushState(null, "", route('ventas.documento.index'));
                this.idcotizacion = 0;
            }
        }
    },
    created() {
        try {

            let url = location.href.split('/');
            let pathUrl = url[url.length - 1];
            let pathUrlGet = pathUrl.split('?');
            if (pathUrlGet.length == 1) {
                if (pathUrlGet.shift() == 'create') {
                    this.ruta = "create";
                }

            } else {
                let parametros = pathUrlGet[1];
                parametros = parametros.split("=");
                if (parametros[0] === "cotizacion") {
                    this.idcotizacion = Number(parametros[1]);
                    this.ruta = "create";
                } else {
                    throw "la variable " + parametros + " no es valido.";
                }
            }
        } catch (ex) {
            console.log(ex);
        }

    },
    methods: {
        async irVentaNueva() {
            try {
                this.mostrarAnimacionVenta();
                const res = await this.axios.get(route('utilidades.getCajaMovimiento'));
                if (res.data.success) {
                    toastr.success(res.data.message,'OPERACIÓN COMPLETADA');
                    this.ruta   =   'create';
                } else {
                    toastr.error(res.data.message,'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN VALIDAR CAJA');
            }finally{
                this.ocultarAnimacionVenta();
            }
        },
        routes(tipo) {
            switch (tipo) {
                case "HOME": {
                    return route('home');
                    break;
                }
                case "CREATE": {
                    return route('ventas.documento.create');
                    break;
                }
            }
        },
        mostrarAnimacionVenta() {
            document.querySelector('.overlay_venta').style.visibility = 'visible';
        },
        ocultarAnimacionVenta() {
            document.querySelector('.overlay_venta').style.visibility = 'hidden';
        }
    },
    mounted() {

    }

}


</script>
