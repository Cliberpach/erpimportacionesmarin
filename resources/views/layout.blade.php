<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ErpCalzado | Siscom</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link rel="icon" href="/img/siscom.ico" />  --}}

    <style>
        .pantalla-carga {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 1);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
    </style>

    <style>
        .swal2-cancel {
            margin-right: 10px;
        }

        .list-alerts {
            max-height: calc(100vh - 325px);
            overflow-y: auto;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: white;
        }

        .content-alert {
            min-height: 20px;
            max-height: 80px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .content-alert::-webkit-scrollbar,
        .list-alerts::-webkit-scrollbar {
            -webkit-appearance: none;
        }

        .content-alert::-webkit-scrollbar:vertical,
        .list-alerts::-webkit-scrollbar:vertical {
            width: 8px;
        }

        .content-alert::-webkit-scrollbar-button:increment,
        .content-alert::-webkit-scrollbar-button,
        .list-alerts::-webkit-scrollbar-button:increment,
        .list-alerts::-webkit-scrollbar-button {
            display: none;
        }

        .content-alert::-webkit-scrollbar:horizontal,
        .list-alerts::-webkit-scrollbar:horizontal {
            height: 10px;
        }

        .content-alert::-webkit-scrollbar-thumb,
        .list-alerts::-webkit-scrollbar-thumb {
            background-color: #6BBD99;
            border-radius: 20px;
            border: 1px solid #f1f2f3;
        }

        .content-alert::-webkit-scrollbar-track,
        .list-alerts::-webkit-scrollbar-track {
            border-radius: 10px;
        }
    </style>

    <link href="{{ asset('Inspinia/css/plugins/select2/select2.min.css') }}" rel="stylesheet">

    <!-- DATATABLES -->
    <link href="https://cdn.datatables.net/v/bs4/dt-2.3.2/r-3.0.5/datatables.min.css" rel="stylesheet"
        integrity="sha384-57j+ilFSg5URotSQqwt2DpHtNkoi7sy+Qj1phKYVWmfSRDx3biVnhnx2mzJTEhu+" crossorigin="anonymous">

    <link href="/Inspinia/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="/Inspinia/font-awesome/css/font-awesome.css" rel="stylesheet"> --}}

    <link href="/Inspinia/css/animate.css" rel="stylesheet">
    <link href="/Inspinia/css/style.css" rel="stylesheet">

    <!-- Toastr style -->
    <link href="/Inspinia/css/plugins/toastr/toastr.min.css" rel="stylesheet">

    <!-- Styles -->
    <link href="/css/style.css" rel="stylesheet">

    <style>
        /* Scrollbar general para todo el sitio */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f0f0f0;
            /* fondo del scroll */
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #1C84C6;
            /* azulito */
            border-radius: 10px;
            border: 2px solid #f0f0f0;
            /* espacio alrededor */
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: #1C84C6;
            /* azul un poco más oscuro al pasar el mouse */
        }

        /* Scrollbar para Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #1C84C6 #f0f0f0;
        }
    </style>

    <style>
        .loader-project {
            transform: rotateZ(45deg);
            perspective: 1000px;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            color: #2196f3;
            position: relative;
        }

        .loader-project:before,
        .loader-project:after {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: inherit;
            height: inherit;
            border-radius: 50%;
            transform: rotateX(70deg);
            animation: 1s spin linear infinite;
        }

        .loader-project:before {
            color: #07497f;
            /* azul oscuro: empieza primero */
        }

        .loader-project:after {
            color: #ff9800;
            /* naranjita: aparece con delay */
            transform: rotateY(70deg);
            animation-delay: .4s;
        }

        @keyframes rotate {
            0% {
                transform: translate(-50%, -50%) rotateZ(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotateZ(360deg);
            }
        }

        @keyframes rotateccw {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(-360deg);
            }
        }

        @keyframes spin {

            0%,
            100% {
                box-shadow: .4em 0px 0 0px currentcolor;
            }

            12% {
                box-shadow: .4em .4em 0 0 currentcolor;
            }

            25% {
                box-shadow: 0 .4em 0 0px currentcolor;
            }

            37% {
                box-shadow: -.4em .4em 0 0 currentcolor;
            }

            50% {
                box-shadow: -.4em 0 0 0 currentcolor;
            }

            62% {
                box-shadow: -.4em -.4em 0 0 currentcolor;
            }

            75% {
                box-shadow: 0px -.4em 0 0 currentcolor;
            }

            87% {
                box-shadow: .4em -.4em 0 0 currentcolor;
            }
        }
    </style>
    <style>
        .overlay_animacion {
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

        .loader_animacion {
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

        .loader_animacion:after {
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

    <link href="{{ mix('css/fontawesome.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="/css/appNotify.css">
    @yield('vue-css')

    @stack('styles')

    @routes
</head>

<body>

    <div class="overlay_animacion">
        <span class="loader_animacion"></span>
    </div>

    <div id="">

        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <!-- Sidebar  Menu -->
                    @include('partials.nav')
                    <!-- /.Sidebar Menu -->
                </ul>

            </div>
        </nav>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-success " href="#"><i
                                class="fa fa-bars"></i> </a>
                    </div>
                    <ul class="nav navbar-top-links">
                        <li>
                            <a href="{{ route('ventas.documento.create') }}" title="DOC. DE VENTA">
                                <i class="fa fa-plus"></i> DV
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reporte.producto.informe') }}" title="PRODUCTO INFORME">
                                <i class="fa fa-plus"></i> PI
                            </a>
                        </li>
                        @if (auth()->check() && auth()->user()->roles()->where('name', 'ADMIN')->exists())
                            <li>
                                <a href="javascript:void(0);" onclick="restaurarStock()" title="RESTAURAR STOCK">
                                    <i class="fas fa-balance-scale"></i> RESTAURAR STOCK
                                </a>
                            </li>
                            {{-- <li>
                                <a href="{{route('descargarBD')}}" title="DESCARGAR BD">
                                    <i class="fa-solid fa-database"></i> DATABASE
                                </a>
                            </li>                         --}}
                        @endif
                    </ul>
                    <ul class="nav navbar-top-links navbar-right" id="appNotify">
                        <notify-component></notify-component>
                        <li>
                            <div style="display:flex;flex-direction:column;">
                                <span class="m-r-sm text-muted welcome-message">
                                    <b>{{ auth()->user()->usuario }}</b>
                                </span>
                                <span>
                                    <b>

                                        {{ auth()->user()->sede->nombre }}

                                    </b>
                                </span>
                            </div>

                        </li>
                        <li>
                            <a href="{{ route('logout') }}">
                                <i class="fa fa-sign-out"></i> Cerrar Sesión
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>

            <div class="pantalla-carga" id="pantalla-carga">
                <span class="loader-project"></span>
            </div>



            <div id="content-system" style="display:block;">
                @yield('content')
                {{-- <div id="appTables"></div> --}}
            </div>

            <div class="footer">
                <div class="float-right" onkeyup="return mayus(this)">
                    DEVELOPER <strong>SISCOM SAC</strong>
                </div>
                <div onkeyup="return mayus(this)">
                    <strong>Copyright</strong> SisCom SAC &copy; {{ date('Y') }}
                </div>
            </div>

        </div>

    </div>

    <div class="position-fixed d-none"
        style="bottom:50px; top:auto; right:30px; left:auto; -webkit-box-shadow: 8px 8px 3px 0px rgba(0,0,0,0.75); -moz-box-shadow: 8px 8px 3px 0px rgba(0,0,0,0.75); box-shadow: 6px 6px 4px 0px rgba(0,0,0,0.75); border-radius: 50%;">
        <a class="d-sm-block" href="{{ route('configuracion.index') }}" target="_blank">
            {{-- <img tag src="/img/config_.png" style="width: 50px"> --}}
        </a>
    </div>


    <script src="{{ '/js/appNotify.js?v=' . rand() }}"></script>
    @stack('scripts-vue-js')
    @yield('vue-js')

    {{-- <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script> --}}

    <!-- Mainly scripts -->
    <script src="/Inspinia/js/jquery-3.1.1.min.js"></script>
    <script src="/Inspinia/js/popper.min.js"></script>
    <script src="/Inspinia/js/bootstrap.js"></script>
    <script src="/Inspinia/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/Inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="/Inspinia/js/inspinia.js"></script>

    {{-- <script src="/Inspinia/js/plugins/pace/pace.min.js"></script> --}}

    <!-- jQuery UI -->
    {{-- <script src="/Inspinia/js/plugins/jquery-ui/jquery-ui.min.js"></script> --}}

    <!-- Toastr script -->
    <script src="/Inspinia/js/plugins/toastr/toastr.min.js"></script>

    <!-- Propio scripts -->
    <script src="/Inspinia/js/scripts.js"></script>

    <!-- SweetAlert -->
    <script src="/SweetAlert/sweetalert2@10.js"></script>


    <script src="{{ asset('Inspinia/js/plugins/select2/select2.full.min.js') }}"></script>

    <!-- Datatables -->
    <script src="https://cdn.datatables.net/v/bs4/dt-2.3.2/r-3.0.5/datatables.min.js"
        integrity="sha384-9m1/ul4UUfv6yoZjjPpf4EtIPDGd505EmvdZmpntp4ljXDaH5wT57N/Z2jXTXg2/" crossorigin="anonymous">
    </script>

    <script src="{{ asset('js/utils.js') }}?v={{ filemtime(public_path('js/utils.js')) }}"></script>

    @stack('scripts')


    <script>
        @if (session('message_error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('message_error') }}',
                confirmButtonText: 'OK',
                showConfirmButton: true,
                allowOutsideClick: false
            });
        @endif

        @if (session('message_success'))
            Swal.fire({
                icon: 'success',
                title: 'Operación completada',
                text: '{{ session('message_success') }}',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 1000,
                timerProgressBar: true
            });
        @endif



        @if (session('message_info'))
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: '{{ session('message_error') }}',
                confirmButtonText: 'OK',
                showConfirmButton: true,
                allowOutsideClick: false
            });
        @endif
    </script>

    <script>
        function consultaExitosa() {

            Swal.fire({
                icon: 'success',
                title: '¡Búsqueda Exitosa!',
                text: 'Datos ingresados.',
                customClass: {
                    container: 'my-swal'
                },
                showConfirmButton: false,
                timer: 1500
            })
        }
        //Loader
        window.addEventListener("load", function() {
            const loader = document.getElementById("pantalla-carga");
            if (loader) {
                loader.style.display = "none";
            }
        })

        async function restaurarStock() {
            try {
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger"
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: "Desea emparejar el stock?",
                    text: "Esta acción no es reversible!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí!",
                    cancelButtonText: "No, cancelar!",
                    reverseButtons: true
                }).then(async (result) => {
                    if (result.isConfirmed) {

                        const res = await axios.post(route('restaurarStock'));
                        console.log(res);
                        if (res.data.success) {
                            const message = res.data.message;
                            toastr.success(message, 'OPERACIÓN COMPLETADA');
                        } else {
                            const message = res.data.message;
                            const exception = res.data.exception;

                            toastr.error(`${message} - ${exception}`, 'ERROR');
                        }

                    } else if (
                        /* Read more about handling dismissals below */
                        result.dismiss === Swal.DismissReason.cancel
                    ) {
                        swalWithBootstrapButtons.fire({
                            title: "Operación cancelada",
                            text: "No se realizaron acciones",
                            icon: "error"
                        });
                    }
                });


            } catch (error) {
                console.log(error)
                toastr.error(`${error.response.data.message}`, 'ERROR EN EL SERVIDOR');
            }
        }

        /*setTimeout(() => {
            //document.querySelector('#row-loading-spinner').style.display = "none";
            $('.loader-spinner').hide();
            $("#content-system").css("display", "");

        }, 0);*/


        function mostrarAnimacion() {
            document.querySelector('.overlay_animacion').style.visibility = 'visible';
        }

        function ocultarAnimacion() {
            document.querySelector('.overlay_animacion').style.visibility = 'hidden';
        }
    </script>
</body>

</html>
