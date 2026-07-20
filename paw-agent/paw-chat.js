// paw-chat.js
import { PAW_AGENT_NAME } from './paw-core.js';

export function addPawMessage(text, role, loading = false) {
    const chatBody = document.getElementById('pawChatBody');
    if (!chatBody) return;

    const msg = document.createElement('div');
    msg.className = `paw-message paw-message-${role}`;
    if (loading) msg.classList.add('paw-message-loading');

    msg.innerHTML = `
        <div class="paw-message-meta">
            <strong>${role === 'user' ? 'Tú' : PAW_AGENT_NAME}</strong>
            <span>${new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}</span>
        </div>
        <div class="paw-message-text">${text}</div>
    `;

    chatBody.appendChild(msg);
    chatBody.scrollTop = chatBody.scrollHeight;
}

export function removePawLoadingMessage() {
    document.querySelector('.paw-message-loading')?.remove();
}

export function showPawNotification(message) {
    const n = document.createElement('div');
    n.className = 'paw-notification';
    n.textContent = message;
    document.body.appendChild(n);

    setTimeout(() => n.classList.add('paw-notification-show'), 50);
    setTimeout(() => {
        n.classList.remove('paw-notification-show');
        setTimeout(() => n.remove(), 250);
    }, 2500);
}

export function initPawWelcomeMessage(addPawMessage) {
    const chatBody = document.getElementById('pawChatBody');
    if (!chatBody || chatBody.children.length > 0) return;

    setTimeout(() => {
        addPawMessage('¡Ey! Soy el güero bot. 🐾 ¿Eres equipo, invitado o seguidor?', 'bot');
    }, 400);
}
