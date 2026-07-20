// paw-minimize.js
import { pawAgentState } from './paw-core.js';
import { showPawNotification } from './paw-chat.js';

export function minimizePawAgent() {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    pawAgentState.isMinimized = true;
    container.classList.add('paw-minimized');

    container.style.bottom = '20px';
    container.style.right = '20px';
    container.style.left = 'auto';
    container.style.top = 'auto';

    showPawNotification('Huella minimizada ✨');
}

export function restorePawAgent() {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    pawAgentState.isMinimized = false;
    container.classList.remove('paw-minimized');
}

export function togglePawMinimize() {
    pawAgentState.isMinimized ? restorePawAgent() : minimizePawAgent();
}
