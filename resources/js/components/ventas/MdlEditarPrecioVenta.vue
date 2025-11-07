<template>
    <div>
        <div class="modal inmodal" id="mdl_editar_precio_venta" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated bounceInRight">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title">EDITAR PRECIO VENTA </h4>
                        <small class="font-bold ventas-title"></small>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-6 text-left">
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i
                                    class="fa fa-times"></i> Cancelar</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</template>
<script>
export default {
    name: "MdlEditarPrecioVenta",
    props: {
        // ventasPendientes: [],
        // imgDefault: "",
        // modoPagos: [],
        // cliente_id:null
    },
    components: {
    },
    data() {
        return {
            saldoRecibosCaja:0,
            recibos_caja:[],
            cuentas: [],
            formPago:null,
            loading:false
        }
    },
    methods: {



    }
}
</script>
