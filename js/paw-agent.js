/* ============================================================
   PAW AGENT – La Cueva del Güero
   Versión unificada y depurada
   CORRECCIONES APLICADAS:
   - ✅ Bug de minimización arreglado (stopPropagation)
   - ✅ Protección DOM mejorada
   - ✅ Soporte touch para móviles
   - ✅ Compatibilidad cross-browser
   ============================================================ */

console.log("🐾 [PAW] Cargando PAW Agent...");

const PAW_AGENT_NAME = 'El Güero Bot';
const PAW_AGENT_API = `${window.location.origin}/api/api-el-guero-bot.php`;
const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

let pawAgentState = {
    isMinimized: true,
    isDragging: false,
    visitType: 'guest',
    dragStartX: 0,
    dragStartY: 0,
    initialX: 0,
    initialY: 0,
    formStep: 0,
    formData: {},
    storyStep: 0,
    storyData: {}
};

window.addEventListener("load", () => {
    console.log("🐾 [PAW] DOM listo, iniciando agente...");
    initPawAgent();
    initPawWelcomeMessage();
});

/* ---------------------- INIT ---------------------- */

function initPawAgent() {
    const container = document.getElementById('pawAgentContainer');
    if (!container) {
        console.error("❌ [PAW] No se encontró #pawAgentContainer");
        return;
    }

    setupDrag(container);
    setupPawEvents();
    minimizePawAgent();

    console.log("🐾 [PAW] Agente iniciado correctamente.");
}

/* ---------------------- DRAG & DROP (CON SOPORTE TOUCH) ---------------------- */

function setupDrag(container) {
    const pawMain = container.querySelector('.paw-main');
    if (!pawMain) {
        console.error("❌ [PAW] No se encontró .paw-main");
        return;
    }

    function startDrag(clientX, clientY) {
        if (pawAgentState.isMinimized) return;

        pawAgentState.isDragging = true;
        container.classList.add('dragging');

        pawAgentState.dragStartX = clientX;
        pawAgentState.dragStartY = clientY;

        const rect = container.getBoundingClientRect();
        pawAgentState.initialX = rect.left;
        pawAgentState.initialY = rect.top;
    }

    function moveDrag(clientX, clientY) {
        if (!pawAgentState.isDragging) return;

        const deltaX = clientX - pawAgentState.dragStartX;
        const deltaY = clientY - pawAgentState.dragStartY;

        const newX = pawAgentState.initialX + deltaX;
        const newY = pawAgentState.initialY + deltaY;

        container.style.left = Math.max(0, Math.min(newX, window.innerWidth - container.offsetWidth)) + 'px';
        container.style.top = Math.max(0, Math.min(newY, window.innerHeight - container.offsetHeight)) + 'px';
        container.style.right = 'auto';
        container.style.bottom = 'auto';
    }

    function endDrag() {
        if (pawAgentState.isDragging) {
            pawAgentState.isDragging = false;
            container.classList.remove('dragging');
        }
    }

    // MOUSE EVENTS
    pawMain.addEventListener('mousedown', (e) => {
        if (e.target.closest('.paw-toe, .paw-chat-area, select, input, button')) return;
        e.preventDefault();
        startDrag(e.clientX, e.clientY);
    });

    document.addEventListener('mousemove', (e) => {
        moveDrag(e.clientX, e.clientY);
    });

    document.addEventListener('mouseup', endDrag);

    // TOUCH EVENTS (para móviles)
    pawMain.addEventListener('touchstart', (e) => {
        if (e.target.closest('.paw-toe, .paw-chat-area, select, input, button')) return;
        const touch = e.touches[0];
        startDrag(touch.clientX, touch.clientY);
    });

    document.addEventListener('touchmove', (e) => {
        if (pawAgentState.isDragging) {
            const touch = e.touches[0];
            moveDrag(touch.clientX, touch.clientY);
        }
    });

    document.addEventListener('touchend', endDrag);
}

/* ---------------------- MINIMIZAR / RESTAURAR ---------------------- */

function minimizePawAgent() {
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

function restorePawAgent() {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    pawAgentState.isMinimized = false;
    container.classList.remove('paw-minimized');
}

function togglePawMinimize() {
    if (pawAgentState.isMinimized) {
        restorePawAgent();
    } else {
        minimizePawAgent();
    }

    const modal = document.querySelector('.modal-busqueda');
    if (modal) modal.remove();
}

/* ---------------------- EVENTOS (FIX CRÍTICO) ---------------------- */

function setupPawEvents() {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    // 🔥 FIX CRÍTICO: Botón de minimizar DEBE tener stopPropagation()
    // para evitar que el click se propague al contenedor
    const minimizeBtn = container.querySelector('.paw-minimize-btn');
    if (minimizeBtn) {
        minimizeBtn.addEventListener('click', (e) => {
            e.stopPropagation();  // ← CRUCIAL: Detener propagación
            e.preventDefault();
            togglePawMinimize();
        });
    }

    // Click en el contenedor cuando está minimizado -> restaurar
    // PERO SOLO si no fue en el botón de minimizar (ya manejado arriba)
    container.addEventListener('click', (e) => {
        // Si fue en el botón de minimizar, ya se manejó arriba
        if (e.target.closest('.paw-minimize-btn')) return;

        // Si está minimizado y no fue en un elemento interactivo, restaurar
        if (pawAgentState.isMinimized && !e.target.closest('.paw-toe, .paw-chat-area, select, input, button')) {
            restorePawAgent();
        }
    });

    // Toes (dedos)
    container.querySelectorAll('.paw-toe').forEach((toe, index) => {
        toe.addEventListener('click', (e) => {
            e.stopPropagation();
            handleToeClick(index, toe);
        });
    });

    // Botón enviar
    const sendBtn = container.querySelector('.paw-send-btn');
    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessageFromPaw);
    }

    // Selector de tipo de visita
    const visitTypeSelect = document.getElementById('pawVisitType');
    if (visitTypeSelect) {
        visitTypeSelect.addEventListener('change', (e) => {
            changeVisitType(e.target.value);
        });
    }

    // Enter para enviar
    const pawInput = document.getElementById('pawChatInput');
    if (pawInput) {
        pawInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessageFromPaw();
            }
        });
    }
}

/* ---------------------- CAMBIO DE MODO ---------------------- */

function changeVisitType(type) {
    pawAgentState.visitType = type;
    updatePawVisitTypeUI(type);
    addPawMessage(`Modo cambiado a: ${type}`, "bot");
}

function updatePawVisitTypeUI(type) {
    const badge = document.querySelector('.paw-visit-badge');
    if (badge) {
        badge.textContent = type === 'guest' ? '👤 Invitado' : '👥 Equipo';
        badge.className = `paw-visit-badge paw-visit-${type}`;
    }
}

/* ---------------------- MANEJO DE TOES ---------------------- */

function handleToeClick(index, toe) {
    const options = [
        { label: 'Generar Escaleta', action: 'escaleta' },
        { label: 'Buscar Invitado', action: 'buscar' },
        { label: 'Storytelling', action: 'storytelling' }
    ];

    if (index < options.length) {
        const option = options[index];
        console.log(`🐾 [PAW] Toe ${index} clickeado: ${option.label}`);
        executePawAction(option.action);
    }
}

function executePawAction(action) {
    switch (action) {
        case 'escaleta':
            addPawMessage("Abriendo generador de escaletas...", "bot");
            break;
        case 'buscar':
            addPawMessage("Buscando invitados...", "bot");
            openPawSearchModal();
            break;
        case 'storytelling':
            addPawMessage("Iniciando modo storytelling...", "bot");
            break;
        default:
            console.warn("Acción desconocida:", action);
    }
}

/* ---------------------- CHAT ---------------------- */

function addPawMessage(text, sender = "user") {
    const chatArea = document.querySelector('.paw-chat-area');
    if (!chatArea) return;

    const msgDiv = document.createElement('div');
    msgDiv.className = `paw-message paw-msg-${sender}`;
    msgDiv.textContent = text;
    chatArea.appendChild(msgDiv);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function sendMessageFromPaw() {
    const input = document.getElementById('pawChatInput');
    if (!input || !input.value.trim()) return;

    const message = input.value.trim();
    addPawMessage(message, "user");
    input.value = '';

    // Simular respuesta del bot
    setTimeout(() => {
        addPawMessage("Entendido. ¿Qué necesitas?", "bot");
    }, 500);
}

/* ---------------------- MODAL DE BÚSQUEDA ---------------------- */

function openPawSearchModal() {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    let modal = document.querySelector('.modal-busqueda');
    if (modal) modal.remove();

    modal = document.createElement('div');
    modal.className = 'modal-busqueda';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Buscar Invitado</h3>
            <input type="text" id="pawSearchInput" placeholder="Nombre del invitado...">
            <button id="pawSearchBtn">Buscar</button>
            <button id="pawCloseSearchBtn">Cerrar</button>
        </div>
    `;

    container.appendChild(modal);

    document.getElementById('pawCloseSearchBtn').addEventListener('click', () => {
        modal.remove();
    });

    document.getElementById('pawSearchBtn').addEventListener('click', () => {
        const searchTerm = document.getElementById('pawSearchInput').value.trim();
        if (searchTerm) {
            addPawMessage(`Buscando: ${searchTerm}`, "bot");
            modal.remove();
        }
    });
}

/* ---------------------- NOTIFICACIONES ---------------------- */

function showPawNotification(text) {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    let notif = document.querySelector('.paw-notification');
    if (notif) notif.remove();

    notif = document.createElement('div');
    notif.className = 'paw-notification';
    notif.textContent = text;
    container.appendChild(notif);

    setTimeout(() => {
        if (notif && notif.parentNode) notif.remove();
    }, 3000);
}

/* ---------------------- INICIALIZACIÓN DE BIENVENIDA ---------------------- */

function initPawWelcomeMessage() {
    setTimeout(() => {
        addPawMessage(`¡Hola! Soy ${PAW_AGENT_NAME}. ¿Cómo puedo ayudarte?`, "bot");
    }, 1000);
}
