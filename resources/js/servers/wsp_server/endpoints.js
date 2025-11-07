import { currentQRCode, sessionStarted, client, clientInfo } from "./states.js";
import express from 'express';
import cors from 'cors';
import { cors_origin } from './path.js';
import whatsapp from 'whatsapp-web.js';
import fs from 'fs';
import path from 'path';
import axios from 'axios';
const { MessageMedia } = whatsapp;

//===== MIDDLEWARE ======
export const app = express();
app.use(cors());
app.use(express.json());
const corsOptions = {
    origin: cors_origin,
    methods: ['GET', 'POST'],
    allowedHeaders: ['Content-Type'],
    credentials: true
};
app.use(cors(corsOptions));
app.use(express.json());


/*app.post('/send-message', async (req, res) => {
    const { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({ success: false, error: 'Faltan número o mensaje' });
    }

    console.log(`${number}@c.us`,message);

    try {
        await client.sendMessage(`${number}@c.us`, message);
        res.json({ success: true });
    } catch (err) {
        console.error('❌ Error al enviar mensaje:', err);
        res.status(500).json({ success: false, error: err.message });
    }
});*/

/*app.post('/send-pdf', async (req, res) => {
    const { number, message, pdfPath } = req.body;

    console.log(`Enviando a: ${number}@c.us - Mensaje: ${message}`);

    if (!number || !message || !pdfPath) {
        console.log('vacio parametro');
        return res.status(400).json({ success: false, error: 'Faltan número, mensaje o ruta del PDF' });
    }

    try {
        const media = MessageMedia.fromFilePath(pdfPath);
        await client.sendMessage(`${number}@c.us`, media, { caption: message });

        res.json({ success: true, message: '📄 PDF enviado correctamente' });
    } catch (err) {
        console.error('❌ Error al enviar el PDF:', err);
        res.status(500).json({ success: false, error: err.message });
    }
});*/

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

app.post('/send-message', async (req, res) => {
    const { numbers, message, pdf_url, pdf_name } = req.body;

    if (!Array.isArray(numbers) || numbers.length === 0 || !message || !pdf_url || !pdf_name) {
        console.log('Faltan parámetros');
        return res.status(400).json({ success: false, error: 'Faltan números, mensaje o PDF' });
    }

    try {
        // Carpeta temporal para descargar PDFs
        const tempDir = path.resolve('./temp_wsp');
        if (!fs.existsSync(tempDir)) fs.mkdirSync(tempDir, { recursive: true });

        const pdfPath = path.join(tempDir, pdf_name);

        // Descargar PDF desde la URL
        const response = await axios.get(pdf_url, { responseType: 'arraybuffer' });
        fs.writeFileSync(pdfPath, Buffer.from(response.data));

        console.log(`PDF descargado en ${pdfPath}`);

        for (const number of numbers) {
            const fullNumber = `${number.trim()}@c.us`;
            console.log(`Enviando a: ${fullNumber} - Mensaje: ${message}`);

            try {
                const media = MessageMedia.fromFilePath(pdfPath);
                await client.sendMessage(fullNumber, media, { caption: message });
            } catch (error) {
                console.error(`❌ Error al enviar a ${fullNumber}:`, error.message);
            }

            await delay(1000);
        }

        // Opcional: eliminar el PDF temporal
        fs.unlinkSync(pdfPath);

        res.json({ success: true, message: '📄 PDF enviado correctamente a todos los números' });
    } catch (err) {
        console.error('❌ Error general al enviar el PDF:', err);
        res.status(500).json({ success: false, error: err.message });
    }
});


//[{ pdf_datos, telefono, mensaje }]
app.post('/send-embalaje', async (req, res) => {
    const datos = req.body;
    console.log(datos);

    if (!Array.isArray(datos) || datos.length === 0) {
        return res.status(400).json({ success: false, error: 'No hay datos para enviar' });
    }

    try {
        for (const item of datos) {
            const { pdf_datos, telefono, mensaje } = item;

            if (!pdf_datos || !pdf_datos.pdf_url || !pdf_datos.pdf_name || !telefono || !mensaje) {
                console.log('⚠️ Datos incompletos en item', item);
                continue;
            }

            const tempDir = path.resolve('./temp_wsp');
            if (!fs.existsSync(tempDir)) fs.mkdirSync(tempDir, { recursive: true });

            const pdfPath = path.join(tempDir, pdf_datos.pdf_name);

            // Descargar PDF desde la URL
            try {
                const response = await axios.get(pdf_datos.pdf_url, { responseType: 'arraybuffer' });
                fs.writeFileSync(pdfPath, Buffer.from(response.data));
                console.log(`PDF descargado en ${pdfPath}`);
            } catch (err) {
                console.error(`❌ Error descargando PDF ${pdf_datos.pdf_name}:`, err.message);
                continue; // pasa al siguiente item
            }

            const media = MessageMedia.fromFilePath(pdfPath);
            const fullNumber = `${telefono.trim()}@c.us`;
            console.log(`📤 Enviando a: ${fullNumber} - Mensaje: ${mensaje}`);

            try {
                await client.sendMessage(fullNumber, media, { caption: mensaje });
                console.log(`✅ Enviado a ${fullNumber}`);
            } catch (error) {
                console.error(`❌ Error al enviar a ${fullNumber}:`, error.message);
            }

            // Eliminar PDF temporal
            try {
                fs.unlinkSync(pdfPath);
            } catch (err) {
                console.warn(`⚠️ No se pudo eliminar el PDF ${pdf_datos.pdf_name}:`, err.message);
            }

            await delay(1000);
        }

        res.json({ success: true, message: '📄 PDFs enviados correctamente' });
    } catch (err) {
        console.error('❌ Error general en /send-embalaje:', err);
        res.status(500).json({ success: false, error: err.message });
    }
});


//[{ pdf_datos, telefono, mensaje }]
app.post('/send-reparto', async (req, res) => {
    const datos = req.body;
    console.log(datos);

    if (!Array.isArray(datos) || datos.length === 0) {
        return res.status(400).json({ success: false, error: 'No hay datos para enviar' });
    }

    try {
        for (const item of datos) {
            const { pdf_datos, telefono, mensaje } = item;

            if (!pdf_datos || !pdf_datos.pdf_url || !pdf_datos.pdf_name || !telefono || !mensaje) {
                console.log('⚠️ Datos incompletos en item', item);
                continue;
            }

            const tempDir = path.resolve('./temp_wsp');
            if (!fs.existsSync(tempDir)) fs.mkdirSync(tempDir, { recursive: true });

            const pdfPath = path.join(tempDir, pdf_datos.pdf_name);

            // Descargar PDF desde la URL
            try {
                const response = await axios.get(pdf_datos.pdf_url, { responseType: 'arraybuffer' });
                fs.writeFileSync(pdfPath, Buffer.from(response.data));
                console.log(`PDF descargado en ${pdfPath}`);
            } catch (err) {
                console.error(`❌ Error descargando PDF ${pdf_datos.pdf_name}:`, err.message);
                continue;
            }

            const media = MessageMedia.fromFilePath(pdfPath);
            const fullNumber = `${telefono.trim()}@c.us`;
            console.log(`📤 Enviando a: ${fullNumber} - Mensaje: ${mensaje}`);

            try {
                await client.sendMessage(fullNumber, media, { caption: mensaje });
                console.log(`✅ Enviado a ${fullNumber}`);
            } catch (error) {
                console.error(`❌ Error al enviar a ${fullNumber}:`, error.message);
            }

            // Eliminar PDF temporal
            try {
                fs.unlinkSync(pdfPath);
            } catch (err) {
                console.warn(`⚠️ No se pudo eliminar el PDF ${pdf_datos.pdf_name}:`, err.message);
            }

            await delay(1000);
        }

        res.json({ success: true, message: '📄 PDFs enviados correctamente' });
    } catch (err) {
        console.error('❌ Error general en /send-reparto:', err);
        res.status(500).json({ success: false, error: err.message });
    }
});

export function getSessionStatus(req, res) {
    res.json({ session_status: sessionStarted, clientInfo });
}

export function getQrCode() {
    return { qr: currentQRCode || null };
}

export function wspConnected(io, req, res) {
    const { sessionStarted } = req.body;
    console.log('Sesión de WhatsApp iniciada:');
    console.log(req.body);

    io.emit('wsp-connected', { sessionStarted });

    res.status(200).send('Evento de sesión iniciada emitido');
}

export function wspDisconnected(io, req, res) {
    const { sessionStarted } = req.body;
    console.log('Sesión de WhatsApp desconectado:');
    console.log(req.body);

    io.emit('wsp-disconnected', { sessionStarted });

    res.status(200).send('Evento de sesión desconectada emitido');
}
