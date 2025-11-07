
import http from 'http';
import { Server } from 'socket.io';
import https from 'https';
import fs from 'fs';
import { initClient } from './client.js';
import { client } from './states.js';
import { environment,PORT,PROTOCOL,URL,cert,cors_origin,key } from './path.js';
import { app, getQrCode, getSessionStatus, wspConnected, wspDisconnected } from './endpoints.js';

let server  =   null;


console.log('Current Environment:', environment);
console.log('cert:', cert);
console.log('cors',cors_origin);


if(environment === 'production'){
    const options = {
        key: fs.readFileSync(key),  // Ruta a tu archivo de >
        cert: fs.readFileSync(cert), // Ruta a tu archivo d>
    };
    server = https.createServer(options, app);
}

if(environment === 'development'){
    server = http.createServer(app);
}

const io = new Server(server, {
    cors: {
        origin: cors_origin, //==== VARIABLE ENTORNO CORS ========
        methods: ['GET', 'POST'],
        credentials: true //====== HABILITAR AUTHENTICACION ======
    }
});

process.on('unhandledRejection', async (reason, promise) => {
    console.warn('⚠️ Unhandled Rejection:', reason);

    if (reason && reason.message && reason.message.includes('Protocol error')) {
        console.log('🔄 Reiniciando cliente tras cierre de sesión...');
        if (client) {
            await client.destroy().catch(() => {});
        }
        setTimeout(() => initClient(io), 3000);
    }
});

app.get('/get-qr', (req, res) => {
    const data  =   getQrCode();
    res.json(data);
});

app.post('/wsp-connected', (req, res) => {
   wspConnected(io,req,res);
});

app.post('/wsp-disconnected', (req, res) => {
    wspDisconnected(io,req,res);
});

app.get('/session-status', (req, res) => {
    getSessionStatus(req,res);
});


initClient(io);

server.listen(PORT, () => {
    console.log(`🚀 Servidor WhatsApp activo en ${PROTOCOL}://${URL}:${PORT}`);
});




// ==== Iniciar servidor HTTP ====
// app.listen(PORT, () => {
//     console.log(`🚀 Servidor WhatsApp activo en ${PROTOCOL}://${URL}:${PORT}`);
// });
