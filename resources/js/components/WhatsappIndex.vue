<template>
    <div class="row justify-content-center align-items-center">
        <!-- Instrucciones (Izquierda) -->
        <div class="col-md-6 mb-4 mb-md-0">
            <!-- Título -->
            <div class="d-flex align-items-center mb-3">
                <i class="fab fa-whatsapp fa-3x text-success mr-3"></i>
                <h3 class="font-weight-bold text-dark mb-0">
                    Usa WhatsApp en tu teléfono
                </h3>
            </div>
            <p class="text-muted mb-4">Sigue estos pasos para escanear el código QR:</p>

            <!-- Mensajes dinámicos -->
            <div v-if="serverMessage" class="alert alert-info py-2 px-3 small mb-2">
                {{ serverMessage }}
            </div>
            <div v-if="whatsappMessage" class="alert alert-success py-2 px-3 small mb-3">
                {{ whatsappMessage }}
            </div>

            <!-- Lista de pasos -->
            <ul class="list-unstyled">
                <li class="d-flex align-items-start mb-3">
                    <i class="fas fa-mobile-alt text-success mr-3 fa-lg"></i>
                    <div><strong>Abre</strong> WhatsApp en tu teléfono</div>
                </li>
                <li class="d-flex align-items-start mb-3">
                    <i class="fas fa-bars text-success mr-3 fa-lg"></i>
                    <div>Toca <strong>Menú</strong> o <strong>Configuración</strong></div>
                </li>
                <li class="d-flex align-items-start mb-3">
                    <i class="fas fa-link text-success mr-3 fa-lg"></i>
                    <div>Selecciona <strong>Dispositivos vinculados</strong></div>
                </li>
                <li class="d-flex align-items-start mb-3">
                    <i class="fas fa-plus-circle text-success mr-3 fa-lg"></i>
                    <div>Toca <strong>Vincular un dispositivo</strong></div>
                </li>
                <li class="d-flex align-items-start">
                    <i class="fas fa-qrcode text-success mr-3 fa-lg"></i>
                    <div>Escanea el código QR que aparece <strong>a la derecha</strong></div>
                </li>
            </ul>
        </div>

        <!-- QR (Derecha) -->
        <div class="col-md-6 text-center">
            <div v-if="sessionStarted" class="mt-4 mt-md-0">
                <div class="card shadow-sm border-0 rounded-lg p-3 bg-light">
                    <p class="text-success font-weight-bold mb-3">
                        <i class="fas fa-check-circle mr-2"></i> Sesión iniciada, ya no es necesario escanear el QR.
                    </p>

                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-user text-primary mr-2"></i>
                            <strong>Nombre:</strong> {{ clientInfo.userName || 'No disponible' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone text-success mr-2"></i>
                            <strong>Número:</strong> {{ clientInfo.userNumber || 'No disponible' }}
                        </li>
                        <li>
                            <i class="fas fa-mobile-alt text-info mr-2"></i>
                            <strong>Plataforma:</strong> {{ clientInfo.userPlatform || 'Desconocida' }}
                        </li>
                    </ul>
                </div>
            </div>
            <div v-else class="mt-4 mt-md-0">
                <div v-if="cargandoQR">
                    <p class="text-primary small mb-2">Esperando QR...</p>
                    <div class="d-flex justify-content-center">
                        <Spinner />
                    </div>
                </div>
                <div v-else>
                    <img :src="qrCode" alt="QR de WhatsApp" class="img-fluid rounded border mb-3 fade-in"
                        style="max-width: 310px; height: 310px; object-fit: contain;" />
                    <p class="text-muted small">Escanea el QR con tu WhatsApp</p>
                </div>
            </div>
        </div>

    </div>
</template>



<!-- <template>
  <div class="p-4 bg-white rounded shadow max-w-md mx-auto mt-8">
    <h2 class="text-xl font-bold mb-4">Enviar WhatsApp</h2>
    <div class="mb-2">
      <label class="block text-sm">Número (ej: 51912345678)</label>
      <input v-model="number" class="w-full border p-2 rounded" type="text" />
    </div>
    <div class="mb-4">
      <label class="block text-sm">Mensaje</label>
      <textarea v-model="message" class="w-full border p-2 rounded"></textarea>
    </div>
    <button @click="enviar" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
      Enviar
    </button>
  </div>
</template>  -->

<script>

import axios from 'axios';
import Spinner from './animations/Spinner.vue';


export default {
    components: {
        Spinner
    },
    data() {
        return {
            number: '',
            message: '',
            qrCode: null,
            cargandoQR: false,
            serverMessage: '', // Mensaje de estado del servidor
            whatsappMessage: '', // Mensaje de estado de WhatsApp
            socketUrl: null,
            sessionStarted: false, // Estado de la sesión
            clientInfo: { userNumber: null, userName: null, userPlatform: null }
        };
    },
    mounted() {
        this.socketUrl = process.env.MIX_WSP_URL;
        console.log('SOCKET SERVER URL:', this.socketUrl);

        //======= VERIFICAR STATUS SESSION WSP ========
        this.checkSessionStatus();

        //====== INICIAR TIMTER QR WSP =======
        this.startTimer();

        this.eventsIo();

    },
    methods: {
        eventsIo() {

            window.io.on('wsp-connected', (data) => {
                this.sessionStarted = data.sessionStarted;
                this.clientInfo = data.clientInfo;
                this.whatsappMessage = '✅ WhatsApp conectado';
                console.log('conectado wsp-connected');
            });

            window.io.on('wsp-disconnected', () => {
                this.sessionStarted = false;
                this.whatsappMessage = '🔐 Escanea este QR con tu WhatsApp';
                console.log('desconectad wsp-disconneted');
            });

            window.io.on('wsp-qr', (data) => {

                if (this.sessionStarted) return;
                this.cargandoQR = true;

                if (data && data.url) {
                    setTimeout(() => {
                        this.qrCode = data.url;
                        this.cargandoQR = false;
                        this.whatsappMessage = '🔐 Escanea este QR con tu WhatsApp';
                        console.log('Nuevo QR recibido:', this.qrCode);
                    }, 1000);
                }

            });
        },
        async obtenerQR() {
            try {
                this.cargandoQR = true;
                const response = await axios.get(`${this.socketUrl}/get-qr`);
                this.qrCode = response.data.qr;
            } catch (err) {
                console.error('Error al obtener el QR:', err);
            } finally {
                this.cargandoQR = false;
            }
        },
        async checkSessionStatus() {
            try {
                const res = await axios.get(`${this.socketUrl}/session-status`);
                this.sessionStarted =   res.data.session_status;
                this.clientInfo     =   res.data.clientInfo;
                this.serverMessage  =   `🚀 Servidor WhatsApp activo en ${this.socketUrl}`;

                if (this.sessionStarted) {
                    console.log('conectado');
                    this.whatsappMessage = '✅ WhatsApp conectado';
                } else {
                    console.log('desconectado wsp');
                    this.whatsappMessage = '🔐 Escanea este QR con tu WhatsApp';
                    this.obtenerQR(); 
                }
            } catch (err) {
                console.error('Error al obtener el estado de la sesión:', err);
            }
        },
        startTimer() {

            //======= OBTENER NUEVO QR CUANDO TIMER LLEGA A 0 =======
            if (!this.sessionStarted && !this.cargandoQR) {
                this.obtenerQR();
            }

        },
        async enviar() {

            if (!this.number || !this.message) {
                alert('Completa todos los campos.');
                return;
            }

            try {
                const res = await axios.post('/general_herramientas/whatsapp/enviar-wsp', {
                    number: this.number,
                    message: this.message
                });
                alert('✅ Mensaje enviado');
                console.log(res.data);
            } catch (err) {
                console.error(err.response?.data || err.message);
                alert('❌ Error al enviar el mensaje');
            }
        }
    },
    beforeDestroy() {
    }
};
</script>

<style scoped>
.fade-in {
    opacity: 0;
    transform: scale(0.95);
    animation: fadeIn 1s ease-out forwards;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: scale(0.95);
    }

    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.img-fluid {
    transition: transform 0.5s ease-in-out;
}

.img-fluid:hover {
    transform: scale(1.05);
}
</style>
