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
    minimizePawAgent(false); // Iniciar minimizado en silencio sin notificaciones

    const subName = localStorage.getItem('paw_sub_name');
    if (subName) {
        const toes = container.querySelectorAll('.paw-toe');
        if (toes && toes.length > 0) {
            toes[0].setAttribute('data-label', `¡Hola, ${subName}!`);
        }
    }

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

function minimizePawAgent(showNotif = true) {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return;

    pawAgentState.isMinimized = true;
    container.classList.add('paw-minimized');

    container.style.bottom = '20px';
    container.style.right = '20px';
    container.style.left = 'auto';
    container.style.top = 'auto';

    if (showNotif) {
        showPawNotification('Huella minimizada ✨');
    }
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
        minimizePawAgent(true);
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
        let label = '👤 Invitado';
        if (type === 'team') label = '👥 Equipo';
        if (type === 'follower') label = '💖 Fandom';
        badge.textContent = label;
        badge.className = `paw-visit-badge paw-visit-${type}`;
    }
    // Sincronizar el select dropdown en el panel inferior
    const selectEl = document.getElementById('pawVisitType');
    if (selectEl) {
        selectEl.value = type;
    }
}

/* ---------------------- MANEJO DE TOES ---------------------- */

function handleToeClick(index, toe) {
    const options = [
        { label: 'Suscribirse', action: 'suscribirse' },
        { label: 'Crear Avatar', action: 'avatar' },
        { label: 'Colocar en el Mapa', action: 'mapa' }
    ];

    if (index < options.length) {
        const option = options[index];
        console.log(`🐾 [PAW] Toe ${index} clickeado: ${option.label}`);
        executePawAction(option.action, toe);
    }
}

function executePawAction(action, toe) {
    switch (action) {
        case 'suscribirse':
            addPawMessage("¡Vamos a registrarte en la Cueva! Escribe tu nombre:", "bot");
            pawAgentState.formStep = 1;
            break;
        case 'avatar':
            addPawMessage("Abriendo panel creador de avatar...", "bot");
            // Placeholder for avatar creation logic
            setTimeout(() => {
                addPawMessage("Avatar generado exitosamente. ¡Ya puedes colocarte en el mapa!", "bot");
                localStorage.setItem('paw_avatar_ready', 'true');
            }, 2000);
            break;
        case 'mapa':
            if (localStorage.getItem('paw_avatar_ready') === 'true') {
                addPawMessage("Activando flujo para colocarte en el Mapa Neón...", "bot");
                document.getElementById('mapa').scrollIntoView({ behavior: 'smooth' });
            } else {
                addPawMessage("Primero debes Crear Avatar para poder colocarte en el mapa.", "bot");
            }
            break;
        default:
            console.warn("Acción desconocida:", action);
    }
}

function formatPawMessage(text) {
    // 1. Reemplazar enlaces Markdown [Texto](Enlace) por etiquetas <a>
    let html = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" style="color:#00ffff; text-decoration:underline; font-weight:600; text-shadow:0 0 5px rgba(0,255,255,0.4);" target="_blank">$1</a>');
    
    // 2. Reemplazar URLs crudas que empiecen con http o https por enlaces
    html = html.replace(/(?<!href=")(https?:\/\/[^\s<]+)/g, '<a href="$1" style="color:#00ffff; text-decoration:underline; font-weight:600;" target="_blank">$1</a>');
    
    // 3. Convertir referencias sueltas a "storytelling-invitado.html" en enlaces clicables estilizados
    html = html.replace(/(?<!href=")(?<!">)(storytelling-invitado\.html)/g, '<a href="$1" style="color:#00ffff; text-decoration:underline; font-weight:600; text-shadow:0 0 5px rgba(0,255,255,0.4);" target="_blank">Formulario de Storytelling</a>');
    
    // 4. Cambiar saltos de línea por <br>
    return html.replace(/\n/g, '<br>');
}

function addPawMessage(text, sender = "user") {
    const chatArea = document.getElementById('pawChatBody') || document.querySelector('.paw-chat-body');
    if (!chatArea) return;

    const msgDiv = document.createElement('div');
    msgDiv.className = `paw-message paw-msg-${sender}`;
    
    if (sender === "bot") {
        msgDiv.innerHTML = formatPawMessage(text);
    } else {
        msgDiv.textContent = text;
    }
    
    chatArea.appendChild(msgDiv);
    chatArea.scrollTop = chatArea.scrollHeight;
}

  async function sendMessageFromPaw() {
      const input = document.getElementById('pawChatInput');
      if (!input || !input.value.trim()) return;
  
      const message = input.value.trim();
      addPawMessage(message, "user");
      input.value = '';
  
      const chatArea = document.getElementById('pawChatBody') || document.querySelector('.paw-chat-body');
      if (!chatArea) return;

      if (pawAgentState.formStep === 1) {
          pawAgentState.formData.name = message;
          pawAgentState.formStep = 2;
          addPawMessage("¡Chido! Ahora, pasame tu email:", "bot");
          return;
      } else if (pawAgentState.formStep === 2) {
          pawAgentState.formData.email = message;
          pawAgentState.formStep = 0;
          localStorage.setItem('paw_sub_name', pawAgentState.formData.name);
          localStorage.setItem('paw_sub_email', pawAgentState.formData.email);
          addPawMessage(`¡Ya estás, ${pawAgentState.formData.name}! Te hemos suscrito con el correo ${pawAgentState.formData.email}.`, "bot");
          
          const toes = document.querySelectorAll('.paw-toe');
          if (toes && toes.length > 0) {
              toes[0].setAttribute('data-label', `¡Hola, ${pawAgentState.formData.name}!`);
          }
          return;
      }

      // Crear un indicador de carga neón en el chat
      const loader = document.createElement('div');
      loader.className = 'paw-message paw-msg-bot';
      loader.innerHTML = '<span style="color:#00FFFF; text-shadow:0 0 5px #00FFFF; animation: pulse 1s infinite;">El Güero está escribiendo...</span>';
      chatArea.appendChild(loader);
      chatArea.scrollTop = chatArea.scrollHeight;

      try {
          const response = await fetch(`${window.location.origin}/api/api-el-guero-bot.php`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                  query: message,
                  visitType: pawAgentState.visitType || 'guest',
                  user: 'usuario_paw_web'
              })
          });

          // Quitar indicador de carga
          loader.remove();

          if (!response.ok) {
              throw new Error(`HTTP ${response.status}`);
          }

          const data = await response.json();
          if (data.success && data.answer) {
              addPawMessage(data.answer, "bot");

              // DETECTAR ACTIVACIÓN DE ACCESO DE INVITADO:
              // Si el bot provee el link para registrarse como invitado, habilitamos el token local.
              if (data.answer.indexOf('storytelling-invitado.html') !== -1) {
                  localStorage.setItem('paw_guest_access', 'true');
                  console.log("🔑 [PAW] Acceso a Storytelling Invitado activado localmente.");
              }
          } else {
              addPawMessage("Ese compa no respondió bien, algo falló en la API.", "bot");
          }

      } catch (err) {
          console.error("Error en Paw Agent chat:", err);
          loader.remove();
          addPawMessage("Tuvimos una falla al hablar con el Güero Bot. Revisa tu conexión.", "bot");
      }
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
