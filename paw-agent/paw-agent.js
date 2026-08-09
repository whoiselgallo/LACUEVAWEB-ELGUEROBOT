// paw-agent.js
import { initPawAgent, pawAgentState } from './paw-core.js';
import { addPawMessage, showPawNotification, initPawWelcomeMessage } from './paw-chat.js';
import { manejarFormularioInvitado } from './paw-form.js';
import { manejarModoEquipo, manejarModoSeguidor } from './paw-modes.js';
import { manejarStorytelling, generarStorytellingDesdeBD } from './paw-storytelling.js';
import { enviarAlBackend } from './paw-api.js';
import { minimizePawAgent, restorePawAgent, togglePawMinimize } from './paw-minimize.js';


// ═════════════════════════════════════════════════════════════════════════════════
// EXPONER FUNCIONES GLOBALES PARA MODAL
// ═════════════════════════════════════════════════════════════════════════════════
window.generarStorytellingDesdeInvitado = generarStorytellingDesdeInvitado;

document.addEventListener('DOMContentLoaded', () => {
    initPawAgent(setupDrag, setupEvents, minimizePawAgent);
    initPawWelcomeMessage(addPawMessage);
});

// ============================
// BOTÓN PRINCIPAL DEL AGENTE
// ============================

document.querySelector('.paw-minimize-btn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    togglePawMinimize();
});

function sendMessage() {
    const input = document.getElementById('pawChatInput');
    if (!input.value.trim()) return showPawNotification("Escribe algo, perro...");

    const message = input.value.trim();
    input.value = '';
    addPawMessage(message, 'user');

    if (pawAgentState.visitType === 'guest' && manejarFormularioInvitado(message)) return;
    if (pawAgentState.visitType === 'story' && manejarStorytelling(message)) return;
    if (pawAgentState.visitType === 'team' && manejarModoEquipo(message)) return;
    if (pawAgentState.visitType === 'follower' && manejarModoSeguidor(message)) return;

    enviarAlBackend(message);
}

// ============================
// MODAL DE BÚSQUEDA DE INVITADO
// ============================

function abrirModalEscaleta() {
  const modal = document.createElement('div');
  modal.className = 'modal-busqueda';
  modal.innerHTML = `
    <div class="modal-contenido">
      <h2>🎬 Buscar invitado</h2>
      <input type="text" id="busquedaInvitado" placeholder="Escribe el nombre del invitado...">
      <div id="resultadosInvitado"></div>
      <button id="cerrarModal" class="btn-cerrar">Cerrar</button>
    </div>
  `;
  document.body.appendChild(modal);

  // Animación de entrada
  setTimeout(() => modal.classList.add('modal-abierto'), 50);

  // Cerrar modal
  document.getElementById('cerrarModal').addEventListener('click', () => modal.remove());

  // Buscar invitado
  const input = document.getElementById('busquedaInvitado');
  input.addEventListener('input', () => buscarInvitado(input.value));
}

async function buscarInvitado(nombre) {
  const resultados = document.getElementById('resultadosInvitado');
  if (!nombre || nombre.length < 2) {
    resultados.innerHTML = '<p>Escribe al menos 2 letras...</p>';
    return;
  }

  try {
    const response = await fetch(`/api/api-invitados-get.php?nombre=${encodeURIComponent(nombre)}`);
    const data = await response.json();

    if (!data || data.length === 0) {
      resultados.innerHTML = '<p>No se encontró ningún invitado.</p>';
      return;
    }

    resultados.innerHTML = data.map(inv => `
      <div class="invitado-item">
        <strong>${inv.nombre}</strong><br>
        <small>${inv.ficha?.ocupacion || 'Sin ocupación registrada'}</small><br>
        <button onclick="generarDesdeInvitado('${inv.nombre}')">Generar Escaleta</button>
        <button onclick="generarGuionDesdeInvitado('${inv.nombre}')">Generar Guion</button>
        <button onclick="generarCueCardsDesdeInvitado('${inv.nombre}')">Generar Cue Cards</button>
        <button onclick="generarStorytellingDesdeInvitado('${inv.nombre}')">Generar Storytelling</button>

      </div>
    `).join('');
  } catch (err) {
    resultados.innerHTML = '<p>Error al buscar invitado.</p>';
    console.error(err);
  }
}

// ============================
// ACCIONES DE GENERACIÓN
// ============================

function generarDesdeInvitado(nombre) {
  showPawNotification(`Generando escaleta para ${nombre}...`);
  manejarIntencion('escaleta', { nombre });
}

function generarGuionDesdeInvitado(nombre) {
  showPawNotification(`Generando guion para ${nombre}...`);
  manejarIntencion('guion', { nombre });
}

function generarCueCardsDesdeInvitado(nombre) {
  showPawNotification(`Generando cue cards para ${nombre}...`);
  manejarIntencion('cuecards', { nombre });
}
