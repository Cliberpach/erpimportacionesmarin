import { handleReady, handleQR, handleDisconnected, handleMessage } from './events.js';
import puppeteer from 'puppeteer';
import whatsapp from 'whatsapp-web.js';
const { Client, LocalAuth, MessageMedia } = whatsapp;
import { client, previousQRCode, currentQRCode, sessionStarted, setClient } from './states.js';


// ==== INICIAR CLIENTE WSP ====
export function initClient(io) {
    console.log('🟡 Inicializando cliente WhatsApp...');

    const _client = new Client({
        authStrategy: new LocalAuth({ clientId: 'bot1' }),
        puppeteer: {
            //executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            executablePath: '/usr/bin/google-chrome', //vps amd64 linux produccion
            //executablePath: '/snap/bin/chromium', //vps arm64 linux demovps
            //executablePath: '/usr/bin/chromium-browser', //karlz
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-zygote',
                //'--single-process' //========== USAR SOLO EN CHROMIUM, NO USAR EN GOOGLE CHROME ========
            ]
        }
    });

    _client.on('qr', async (qr) => {
        console.log('🔐 QR recibido:', qr);
        await handleQR(io, qr);
    });
    _client.on('ready', () => handleReady(io, _client).catch((err) => console.error(err)));
    _client.on('message', (message) => handleMessage(io, message).catch((err) => console.error(err)));
    _client.on('disconnected', (reason) => handleDisconnected(reason, io).catch((err) => console.error(err)));
    _client.on('authenticated', () => {
        console.log('Sesión autenticada correctamente');
    });

    _client.initialize();

    console.log('📤 Cliente inicializado');

}
