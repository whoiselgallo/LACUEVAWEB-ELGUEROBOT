/**
 * Gestor de Blog - La Cueva del Güero
 * Maneja la carga de PDFs, extracción de texto y publicación de posts
 */

// Configurar PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Estado global
let state = {
    archivoSeleccionado: null,
    contenidoPDF: '',
    postActual: null
};

// ========================
// ELEMENTOS DEL DOM
// ========================
const uploadArea = document.getElementById('upload-area');
const pdfInput = document.getElementById('pdf-input');
const btnExtract = document.getElementById('btn-extract');
const btnClearUpload = document.getElementById('btn-clear-upload');
const fileInfo = document.getElementById('file-info');
const fileName = document.getElementById('file-name');
const pdfPreview = document.getElementById('pdf-preview');
const pdfContent = document.getElementById('pdf-content');
const statusUpload = document.getElementById('status-upload');

const postTitulo = document.getElementById('post-titulo');
const postAutor = document.getElementById('post-autor');
const postFecha = document.getElementById('post-fecha');
const postCategoria = document.getElementById('post-categoria');
const postContenido = document.getElementById('post-contenido');
const postExcerpt = document.getElementById('post-excerpt');
const btnPublish = document.getElementById('btn-publish');
const btnPreview = document.getElementById('btn-preview');
const btnClearForm = document.getElementById('btn-clear-form');
const statusEdit = document.getElementById('status-edit');
const postPreview = document.getElementById('post-preview');
const previewTituloText = document.getElementById('preview-titulo-text');
const previewAutor = document.getElementById('preview-autor');
const previewFecha = document.getElementById('preview-fecha');
const previewContenidoText = document.getElementById('preview-contenido-text');

const btnLoadPosts = document.getElementById('btn-load-posts');
const postsContainer = document.getElementById('posts-container');
const statusHistory = document.getElementById('status-history');

// ========================
// MANEJO DE TABS
// ========================
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const tabName = e.currentTarget.dataset.tab;
        
        // Desactivar todos los tabs
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Activar tab seleccionado
        e.currentTarget.classList.add('active');
        document.getElementById(tabName).classList.add('active');
    });
});

// ========================
// TAB 1: SUBIR PDF
// ========================

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        pdfInput.files = files;
        handlePDFSelected();
    }
});

uploadArea.addEventListener('click', () => pdfInput.click());

pdfInput.addEventListener('change', handlePDFSelected);

function handlePDFSelected() {
    const file = pdfInput.files[0];
    
    if (!file) return;
    
    // Validar que es PDF
    if (file.type !== 'application/pdf') {
        mostrarStatus('error', 'El archivo debe ser un PDF', statusUpload);
        pdfInput.value = '';
        return;
    }
    
    // Validar tamaño (máximo 10MB)
    if (file.size > 10 * 1024 * 1024) {
        mostrarStatus('error', 'El PDF no debe exceder 10MB', statusUpload);
        pdfInput.value = '';
        return;
    }
    
    state.archivoSeleccionado = file;
    fileName.textContent = file.name;
    fileInfo.classList.add('show');
    btnExtract.disabled = false;
    
    mostrarStatus('info', 'PDF listo para procesar', statusUpload);
    pdfPreview.classList.remove('show');
}

btnExtract.addEventListener('click', () => extraerTextoDePDF(state.archivoSeleccionado));

btnClearUpload.addEventListener('click', () => {
    pdfInput.value = '';
    state.archivoSeleccionado = null;
    state.contenidoPDF = '';
    fileInfo.classList.remove('show');
    pdfPreview.classList.remove('show');
    btnExtract.disabled = true;
    statusUpload.classList.remove('show');
    mostrarStatus('info', 'Formulario limpiado', statusUpload);
});

/**
 * Extraer texto del PDF usando PDF.js
 */
async function extraerTextoDePDF(archivo) {
    try {
        btnExtract.disabled = true;
        mostrarStatus('info', '🔄 Procesando PDF...', statusUpload);
        
        const arrayBuffer = await archivo.arrayBuffer();
        const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
        
        let textoCompleto = '';
        
        // Extraer texto de cada página
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            const textoPage = textContent.items.map(item => item.str).join(' ');
            textoCompleto += textoPage + '\n\n';
        }
        
        state.contenidoPDF = textoCompleto.trim();
        
        // Mostrar preview
        pdfContent.textContent = textoCompleto.substring(0, 1500) + '...';
        pdfPreview.classList.add('show');
        
        // Copiar contenido al formulario de edición
        postContenido.value = textoCompleto;
        
        mostrarStatus('success', '✅ PDF procesado correctamente. Contenido copiado al formulario de edición.', statusUpload);
        btnExtract.disabled = false;
        
        // Cambiar automáticamente a la pestaña de edición
        setTimeout(() => {
            document.querySelector('[data-tab="editar"]').click();
        }, 500);
        
    } catch (error) {
        console.error('Error al extraer PDF:', error);
        mostrarStatus('error', 'Error al procesar el PDF: ' + error.message, statusUpload);
        btnExtract.disabled = false;
    }
}

// ========================
// TAB 2: EDITAR POST
// ========================

// Establecer fecha actual por defecto
postFecha.valueAsDate = new Date();

// Previsualizar mientras se escribe
[postTitulo, postAutor, postContenido].forEach(input => {
    input.addEventListener('input', actualizarPreview);
});

postFecha.addEventListener('change', actualizarPreview);

function actualizarPreview() {
    if (postTitulo.value) {
        previewTituloText.textContent = postTitulo.value;
        previewAutor.textContent = `Por: ${postAutor.value || 'Anónimo'}`;
        previewFecha.textContent = `Fecha: ${formatearFecha(postFecha.value)}`;
        previewContenidoText.textContent = postContenido.value.substring(0, 400) + '...';
    }
}

btnPreview.addEventListener('click', () => {
    actualizarPreview();
    postPreview.classList.toggle('show');
    btnPreview.textContent = postPreview.classList.contains('show') ? '👁️ Ocultar Vista Previa' : '👁️ Previsualizar';
});

btnClearForm.addEventListener('click', () => {
    postTitulo.value = '';
    postAutor.value = '';
    postFecha.valueAsDate = new Date();
    postCategoria.value = 'reflexion';
    postContenido.value = '';
    postExcerpt.value = '';
    postPreview.classList.remove('show');
    statusEdit.classList.remove('show');
    mostrarStatus('info', 'Formulario limpiado', statusEdit);
});

btnPublish.addEventListener('click', publicarPost);

/**
 * Publicar post
 */
async function publicarPost() {
    // Validación
    if (!postTitulo.value.trim()) {
        mostrarStatus('error', 'El título es obligatorio', statusEdit);
        return;
    }
    
    if (!postAutor.value.trim()) {
        mostrarStatus('error', 'El autor es obligatorio', statusEdit);
        return;
    }
    
    if (!postContenido.value.trim()) {
        mostrarStatus('error', 'El contenido es obligatorio', statusEdit);
        return;
    }
    
    try {
        btnPublish.disabled = true;
        mostrarStatus('info', '⏳ Publicando post...', statusEdit);
        
        const formData = new FormData();
        formData.append('action', 'publish');
        formData.append('titulo', postTitulo.value);
        formData.append('autor', postAutor.value);
        formData.append('fecha', postFecha.value);
        formData.append('categoria', postCategoria.value);
        formData.append('contenido', postContenido.value);
        formData.append('excerpt', postExcerpt.value);
        
        const response = await fetch(`${window.location.origin}/api/upload-blog.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Guardar datos del post actual para compartir
            state.postActual = data.post;
            
            // Generar código para copiar
            const codigoPost = generarCodigoPost(data.post);
            
            // Crear el mensaje de éxito con botones de share
            const successMsg = `✅ Post publicado exitosamente!\n\nℹ️ ${data.instruccion}\n\n📋 Copia este código en blog.js:\n\n${codigoPost}`;
            
            mostrarStatus('success', successMsg, statusEdit);
            
            // Agregar botones de compartir
            mostrarBotonesCompartir(data.post);
            
            // Limpiar formulario después de un delay
            setTimeout(() => btnClearForm.click(), 1500);
            
        } else {
            mostrarStatus('error', data.error || 'Error al publicar el post', statusEdit);
        }
        
        btnPublish.disabled = false;
        
    } catch (error) {
        console.error('Error:', error);
        mostrarStatus('error', 'Error al publicar: ' + error.message, statusEdit);
        btnPublish.disabled = false;
    }
}

/**
 * Generar código del post para copiar
 */
function generarCodigoPost(post) {
    return `{
    id: ${post.id},
    titulo: "${post.titulo.replace(/"/g, '\\"')}",
    fecha: "${post.fecha}",
    autor: "${post.autor.replace(/"/g, '\\"')}",
    categoria: "${post.categoria}",
    contenido: \`${post.contenido.replace(/`/g, '\\`')}\`,
    excerpt: "${post.excerpt.replace(/"/g, '\\"')}"
}`;
}

// ========================
// TAB 3: HISTORIAL
// ========================

btnLoadPosts.addEventListener('click', cargarPostsPublicados);

async function cargarPostsPublicados() {
    try {
        btnLoadPosts.disabled = true;
        mostrarStatus('info', '🔄 Cargando posts...', statusHistory);
        
        const formData = new FormData();
        formData.append('action', 'get_posts');
        
        const response = await fetch(`${window.location.origin}/api/upload-blog.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.posts && data.posts.length > 0) {
            postsContainer.innerHTML = '';
            
            data.posts.forEach(post => {
                const postEl = document.createElement('div');
                postEl.className = 'post-item';
                const codigoPost = escapeHTML(generarCodigoPost(post).replace(/'/g, "\\'"));
                postEl.innerHTML = `
                    <div class="post-item-titulo">${escapeHTML(post.titulo)}</div>
                    <div class="post-item-meta">
                        📝 ${escapeHTML(post.autor)} • 📅 ${formatearFecha(post.fecha)} • 🏷️ ${escapeHTML(post.categoria)}
                    </div>
                    <div class="post-item-excerpt">${escapeHTML(post.excerpt)}</div>
                    <button class="copy-btn" onclick="copiarAlPortapapeles('${codigoPost}')">
                        📋 Copiar Código
                    </button>
                `;
                postsContainer.appendChild(postEl);
            });
            
            mostrarStatus('success', `✅ ${data.posts.length} posts cargados`, statusHistory);
        } else {
            postsContainer.innerHTML = '<p style="color: #4EFC22; text-align: center; padding: 20px;">No hay posts publicados aún.</p>';
            mostrarStatus('info', 'No hay posts publicados aún', statusHistory);
        }
        
        btnLoadPosts.disabled = false;
        
    } catch (error) {
        console.error('Error:', error);
        mostrarStatus('error', 'Error al cargar posts: ' + error.message, statusHistory);
        btnLoadPosts.disabled = false;
    }
}

// ========================
// FUNCIONES AUXILIARES
// ========================

/**
 * Mostrar mensaje de estado
 */
function mostrarStatus(tipo, mensaje, elemento) {
    elemento.className = `status show ${tipo}`;
    elemento.innerHTML = mensaje.replace(/\n/g, '<br>');
    
    // Auto-ocultar después de 8 segundos si es éxito
    if (tipo === 'success') {
        setTimeout(() => {
            elemento.classList.remove('show');
        }, 8000);
    }
}

/**
 * Mostrar botones para compartir el post publicado
 */
function mostrarBotonesCompartir(post) {
    // Crear el elemento contenedor para los botones
    const shareContainer = document.createElement('div');
    shareContainer.className = 'share-buttons-container';
    shareContainer.innerHTML = `
        <div class="share-buttons-header">
            <h3>🚀 Comparte el Artículo</h3>
            <p>Acceso directo: <strong id="share-url">${window.location.origin}/?post=${post.id}</strong></p>
        </div>
        <div class="share-buttons-grid">
            <button class="share-btn share-copy" onclick="copiarURL('${window.location.origin}/?post=${post.id}')">
                <i class="fas fa-link"></i> Copiar Link
            </button>
            <button class="share-btn share-whatsapp" onclick="compartirWhatsApp('${post.titulo}', '${window.location.origin}/?post=${post.id}')">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
            <button class="share-btn share-facebook" onclick="compartirFacebook('${window.location.origin}/?post=${post.id}')">
                <i class="fab fa-facebook"></i> Facebook
            </button>
            <button class="share-btn share-twitter" onclick="compartirTwitter('${post.titulo}', '${window.location.origin}/?post=${post.id}')">
                <i class="fab fa-twitter"></i> Twitter
            </button>
        </div>
    `;
    
    // Insertar después del status
    statusEdit.parentNode.insertBefore(shareContainer, statusEdit.nextSibling);
    
    // Auto-remover después de 15 segundos
    setTimeout(() => {
        if (shareContainer.parentNode) {
            shareContainer.remove();
        }
    }, 15000);
}

/**
 * Copiar URL al portapapeles
 */
window.copiarURL = function(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('✅ URL copiada al portapapeles:\n\n' + url);
    }).catch(() => {
        prompt('Copia esta URL:', url);
    });
};

/**
 * Compartir en WhatsApp
 */
window.compartirWhatsApp = function(titulo, url) {
    const mensaje = encodeURIComponent(`¡Mira este artículo de La Cueva del Güero!\n\n📰 "${titulo}"\n\n${url}`);
    window.open(`https://wa.me/?text=${mensaje}`, '_blank');
};

/**
 * Compartir en Facebook
 */
window.compartirFacebook = function(url) {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
};

/**
 * Compartir en Twitter
 */
window.compartirTwitter = function(titulo, url) {
    const texto = encodeURIComponent(`"${titulo}" - La Cueva del Güero ${url}`);
    window.open(`https://twitter.com/intent/tweet?text=${texto}`, '_blank', 'width=600,height=400');
};

/**
 * Escapar HTML para evitar XSS
 */
function escapeHTML(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

/**
 * Formatear fecha a formato legible
 */
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(fecha + 'T00:00:00').toLocaleDateString('es-MX', opciones);
}

/**
 * Copiar texto al portapapeles
 */
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        alert('✅ Código copiado al portapapeles');
    }).catch(err => {
        console.error('Error al copiar:', err);
        alert('Error al copiar. Intenta seleccionar manualmente.');
    });
}

// ========================
// INICIALIZACIÓN
// ========================
console.log('✅ Gestor de Blog cargado correctamente');
console.log('📝 Esperando a que el usuario suba un PDF...');
