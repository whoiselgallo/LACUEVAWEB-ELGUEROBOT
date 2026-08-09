// paw-core.js
export const PAW_AGENT_NAME = 'El Güero Bot';
export const PAW_AGENT_API = `${window.location.origin}/api/api-el-guero-bot.php`;

export const pawAgentState = {
    isMinimized: true,
    isDragging: false,
    visitType: 'guest',
    dragStartX: 0,
    dragStartY: 0,
    initialX: 0,
    initialY: 0,
    formStep: 0,
    formData: {},
    // Estados para Storytelling conversacional
    storyStep: 0,
    storyData: {}
};

export function initPawAgent(setupDrag, setupEvents, minimize) {
    const container = document.getElementById('pawAgentContainer');
    if (!container) return console.error("❌ No se encontró #pawAgentContainer");

    setupDrag(container);
    setupEvents();
    minimize();
}
