import qrcode from 'qrcode';
import { sessionStarted, previousQRCode, currentQRCode, setCurrentQRCode, setPreviousQRCode, setSessionStarted, setClientInfo, clientInfo, setClient } from './states.js';
import { client } from './states.js';
import axios from 'axios';
import { PROTOCOL, URL } from './path.js';
import fs from 'fs-extra';
import { fileURLToPath } from 'url';
import path from 'path';
import { initClient } from './client.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const SESSION_DIR = path.join(__dirname, '.wwebjs_auth');

export function handleQR(io, qr) {
    if (qr !== previousQRCode) {
        console.log('🔐 Escanea este QR con tu WhatsApp:');

        //======= GENERAR LA NUEVA URL DEL QR =======
        qrcode.toDataURL(qr, (err, url) => {
            if (err) {
                console.error('Error al generar el QR:', err);
                return;
            }

            setCurrentQRCode(url);
            setPreviousQRCode(qr);

            io.emit('wsp-qr', { url });
        });

    }
}

export async function handleReady(io, client) {

    setClient(client);
    setSessionStarted(true);
    setClientInfo(client.info);

    console.log('✅ WhatsApp conectado');
    console.log('Cliente conectado:', client.info);
    io.emit('wsp-connected', { sessionStarted,clientInfo });
}

export async function handleDisconnected(reason, io) {
    setSessionStarted(false);
    console.log('❌ WhatsApp desconectado:', reason);
    io.emit('wsp-disconnected', { sessionStarted });

    if (reason === 'LOGOUT') {
        console.log('⚠️ Sesión cerrada desde el dispositivo. Reiniciando...');

        try {
            if (client) {
                await client.destroy()
                    .then(() => console.log('🛑 Cliente destruido correctamente'))
                    .catch(err => console.warn('⚠️ Error al destruir cliente:', err.message));
            }
        } catch (err) {
            console.warn('⚠️ Excepción inesperada al destruir cliente:', err.message);
        }

        setClient(null);
        setPreviousQRCode(null);
        setCurrentQRCode(null);

        try {
            if (fs.existsSync(SESSION_DIR)) {
                await fs.rm(SESSION_DIR, { recursive: true, force: true });
                console.log('🗑️ Carpeta de sesión eliminada');
            }
        } catch (err) {
            console.warn('⚠️ Error eliminando sesión:', err.message);
        }

        setTimeout(() => {
            console.log('🔄 Reiniciando cliente...');
            initClient(io);
        }, 3000);
    }
}


export function handleMessage(msg) {
    console.log('📨 Mensaje recibido:', msg.body);
}
