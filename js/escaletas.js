/* Generador de Escaletas - La Cueva del Güero
   VERSIÓN CORREGIDA
   - ✅ URLs centralizadas
   - ✅ Funciones duplicadas eliminadas
   - ✅ Protección DOM mejorada
   - ✅ Compatibilidad cross-browser
*/

// ============================================================
// CONFIGURACIÓN CENTRALIZADA
// ============================================================

const API_ESCALETA_URL = `${window.location.origin}/api/api-escaleta.php`;
const API_KNOWLEDGE_URL = `${window.location.origin}/api/api-guero-knowledge.php`;

// ============================================================
// UTILIDADES
// ============================================================

function obtenerValor(id) {
    const elemento = document.getElementById(id);
    return elemento ? elemento.value.trim() : '';
}

function setResultado(html) {
    const resultado = document.getElementById('resultado');
    if (!resultado) return;
    resultado.innerHTML = html;
    resultado.classList.add('active');
    
    // Scroll suave con fallback
    if (resultado.scrollIntoView) {
        try {
            resultado.scrollIntoView({ behavior: "smooth" });
        } catch (e) {
            // Fallback para navegadores que no soportan smooth scroll
            resultado.scrollIntoView();
        }
    }
}

function textoResultadoPlano() {
    const resultado = document.getElementById('resultado');
    return resultado ? resultado.innerText.trim() : '';
}

function escapeHtml(texto) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(texto || '').replace(/[&<>"']/g, (char) => map[char]);
}

// ============================================================
// GENERAR ESCALETA
// ============================================================

async function generarEscaleta() {
    const boton = document.getElementById('botonGenerar');
    const campos = ['nombre', 'ocupacion', 'signo', 'fecha', 'barrio', 'trayectoria', 'herida', 'incomodo', 'gustos'];
    const datos = Object.fromEntries(campos.map((campo) => [campo, obtenerValor(campo)]));

    const faltantes = campos.filter((campo) => !datos[campo]);
    if (faltantes.length > 0) {
        setResultado(`<p>Faltan campos por completar: ${faltantes.join(', ')}</p>`);
        return;
    }
    
    try {
        if (boton) {
            boton.disabled = true;
            boton.textContent = 'Generando...';
        }

        const response = await fetch(API_ESCALETA_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || `Error HTTP ${response.status}`);
        }

        const data = await response.json();

        setResultado(`
            <div class="seccion-resultado">
                <h3>🎬 Escaleta</h3>
                <pre>${escapeHtml(data.escaleta || '')}</pre>
            </div>
            <div class="seccion-resultado">
                <h3>📝 Guion</h3>
                <pre>${escapeHtml(data.guion || '')}</pre>
            </div>
            <div class="seccion-resultado">
                <h3>🎴 Cue Cards</h3>
                <pre>${escapeHtml(data.cue_cards || '')}</pre>
            </div>
            <button id="btnGenerarCueCards" class="btn-cuecards">
                🎴 Generar Cue Cards para impresión
            </button>
        `);

        // Adjuntar evento al botón de cue cards
        const btnCueCards = document.getElementById('btnGenerarCueCards');
        if (btnCueCards && typeof generarCueCardsDesdeEscaleta === 'function') {
            btnCueCards.addEventListener('click', () => {
                generarCueCardsDesdeEscaleta({
                    nombre: datos.nombre,
                    escaleta: data.escaleta,
                    guion: data.guion
                });
            });
        }
        
        // Guardar conocimiento
        guardarConocimiento(datos.nombre, data.escaleta, data.guion, data.cue_cards);

    } catch (error) {
        setResultado(`<p style="color:#FF00FF;">❌ ${escapeHtml(error.message)}</p>`);
    } finally {
        if (boton) {
            boton.disabled = false;
            boton.textContent = '🚀 Generar Escaleta';
        }
    }
}

// ============================================================
// GUARDAR CONOCIMIENTO
// ============================================================

async function guardarConocimiento(nombre, escaleta, guion, cuecards) {
    try {
        await fetch(API_KNOWLEDGE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nombre,
                escaleta,
                guion,
                cuecards
            })
        });
    } catch (err) {
        console.warn("No se pudo guardar conocimiento:", err);
    }
}

// ============================================================
// UTILIDADES DE INTERFAZ
// ============================================================

function copiarResultado() {
    const texto = textoResultadoPlano();
    if (!texto) return;
    
    // Usar navigator.clipboard con fallback
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto)
            .then(() => alert('Resultado copiado al portapapeles.'))
            .catch(() => copiarAlPortapapelesFallback(texto));
    } else {
        copiarAlPortapapelesFallback(texto);
    }
}

function copiarAlPortapapelesFallback(texto) {
    const textarea = document.createElement('textarea');
    textarea.value = texto;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        alert('Resultado copiado al portapapeles.');
    } catch (err) {
        alert('No se pudo copiar. Intenta manualmente.');
    }
    
    document.body.removeChild(textarea);
}

function descargarResultado() {
    const texto = textoResultadoPlano();
    if (!texto) return;

    const blob = new Blob([texto], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const enlace = document.createElement('a');
    enlace.href = url;
    enlace.download = `escaleta-la-cueva-${new Date().toISOString().slice(0, 10)}.txt`;
    
    // Usar appendChild para mejor compatibilidad
    document.body.appendChild(enlace);
    enlace.click();
    document.body.removeChild(enlace);
    
    URL.revokeObjectURL(url);
}

function limpiarFormulario() {
    const formulario = document.getElementById('formularioEscaleta');
    const resultado = document.getElementById('resultado');
    
    if (formulario) formulario.reset();
    if (resultado) {
        resultado.innerHTML = '';
        resultado.classList.remove('active');
    }
}

// ============================================================
// INICIALIZACIÓN
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    // Protección: verificar que los elementos existen antes de adjuntar listeners
    const botonGenerar = document.getElementById('botonGenerar');
    const botonCopiar = document.getElementById('botonCopiar');
    const botonDescargar = document.getElementById('botonDescargar');
    const botonLimpiar = document.getElementById('botonLimpiar');

    if (botonGenerar) botonGenerar.addEventListener('click', generarEscaleta);
    if (botonCopiar) botonCopiar.addEventListener('click', copiarResultado);
    if (botonDescargar) botonDescargar.addEventListener('click', descargarResultado);
    if (botonLimpiar) botonLimpiar.addEventListener('click', limpiarFormulario);

    console.log('✓ Escaletas: módulo cargado correctamente');
});
