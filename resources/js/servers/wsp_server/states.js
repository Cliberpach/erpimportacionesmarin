export let previousQRCode   = null;
export let currentQRCode    = null;

export let sessionStarted   = false;
export const clientInfo     = {userNumber:null,userName:null,userPlatform:null};


export let client           = null;


export function setClient(newClient) {
    client = newClient;
}

export function getClient() {
    return client;
}

export function setPreviousQRCode(qr) {
    previousQRCode = qr;
}

export function getPreviousQRCode() {
    return previousQRCode;
}

export function setCurrentQRCode(qr) {
    currentQRCode = qr;
}

export function getCurrentQRCode() {
    return currentQRCode;
}

export function setSessionStarted(status) {
    sessionStarted = status;
    clientInfo.userNumber   =   null;
    clientInfo.userName     =   null;
    clientInfo.userPlatform =   null;
}

export function setClientInfo(_clientInfo) {
    clientInfo.userNumber   =   _clientInfo.me.user;
    clientInfo.userName     =   _clientInfo.pushname;
    clientInfo.userPlatform =   _clientInfo.platform
}

export function isSessionStarted() {
    return sessionStarted;
}
