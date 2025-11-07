require('./bootstrap');

import Vue from 'vue';
import { io } from 'socket.io-client';

window.Vue = Vue;

//======== SOCKET =======
const socketUrl = process.env.MIX_WSP_URL;

// Configuración de la conexión de Socket.IO
const socket = io(socketUrl);

// Evento de conexión
socket.on('connect', () => {
    console.log('Conectado al servidor de Socket.IO');
});

window.io = socket;


Vue.component('ventas-component', require('./components/caja/VentasComponent.vue').default);
// Vue.component('modal-cliente', require('./components/ventas/ModalCliente.vue').default);
Vue.component('v-select', () => import('vue-select'));
Vue.component('wsp-index', require('./components/WhatsappIndex.vue').default);

const app = new Vue({
    el: '#app',
});
