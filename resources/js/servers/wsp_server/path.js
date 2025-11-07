import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import path from 'path';

const __filename    = fileURLToPath(import.meta.url);
const __dirname     = path.dirname(__filename);
dotenv.config({ path: path.resolve(__dirname, '../../.env') });

export const environment   =   process.env.VITE_ENV;
export const PORT          =   process.env.PORT_WSP; 
export const PROTOCOL      =   environment === 'development'?process.env.PROTOCOL_DEV:process.env.PROTOCOL_PROD;
export const URL           =   environment === 'development'?process.env.URL_DEV:process.env.URL_PROD;
export const cert          =   process.env.SSL_CERT_PATH;
export const cors_origin   =   process.env.CORS_ORIGIN
export const key           =   process.env.SSL_KEY_PATH;