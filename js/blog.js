    /**
 * La Cueva del Güero - Sistema de Blog
 * Carga y renderiza artículos del blog con soporte para posts.json
 */

// Datos de ejemplo - se pueden reemplazar con posts.json del servidor
const DEFAULT_BLOG_POSTS = [
    {
        id: 1,
        titulo: "La Verdad Duele, Pero Libera",
        fecha: "2026-04-28",
        autor: "El Güero",
        categoria: "reflexion",
        contenido: "En la calle aprendimos que la verdad es el único activo que no se deprecia. Mientras otros juegan a aparentar, nosotros construimos con lo real.",
        excerpt: "La autenticidad es el mejor filtro de la vida."
    },
    {
        id: 2,
        titulo: "Del Almacén al Micrófono",
        fecha: "2026-04-25",
        autor: "El Junior",
        categoria: "historia",
        contenido: "No todos entienden el camino desde la incertidumbre hasta la certeza. Tres cabezas, una misma visión: demostrar que de donde vienes no define a dónde llegas.",
        excerpt: "La determinación es el único pasaporte que funciona en todos lados."
    },
    {
        id: 3,
        titulo: "El Signo no te Salva, tu Actitud Sí",
        fecha: "2026-04-20",
        autor: "El Güero",
        categoria: "horoscopo",
        contenido: "Horóscopo de la Cueva: olvida lo que dice el zodiaco. Tu signo es tu energía, tu determinación y tu capacidad de levantarte después de caer.",
        excerpt: "El mejor horóscopo es el que tú escribes con tus acciones."
    }
];

/**
 * Cargar posts del servidor o usar datos por defecto
 */
async function loadBlogPosts() {
    try {
        const response = await fetch(`${window.location.origin}/posts.json`, { method: 'GET' });
        if (response.ok) {
            const data = await response.json();
            return Array.isArray(data) ? data : data.posts || DEFAULT_BLOG_POSTS;
        }
    } catch (error) {
        console.warn('Usando posts por defecto');
    }
    return DEFAULT_BLOG_POSTS;
}

/**
 * Renderizar un post individual
 */
function renderBlogPost(post) {
    const postElement = document.createElement('article');
    postElement.className = 'blog-post-card';
    postElement.innerHTML = `
        <div class="blog-post-header">
            <h3 class="blog-post-title">${escapeHtml(post.titulo)}</h3>
            <div class="blog-post-meta">
                <span class="blog-post-author">✍️ ${escapeHtml(post.autor)}</span>
                <span class="blog-post-date">📅 ${formatDate(post.fecha)}</span>
                <span class="blog-post-category">#${escapeHtml(post.categoria)}</span>
            </div>
        </div>
        <div class="blog-post-excerpt">
            ${escapeHtml(post.excerpt || post.contenido.substring(0, 150))}...
        </div>
        <div class="blog-post-footer">
            <button class="blog-read-more" data-post-id="${post.id}">
                Leer Completo →
            </button>
        </div>
    `;

    return postElement;
}

/**
 * Renderizar todos los posts
 */
async function renderAllBlogPosts() {
    const container = document.getElementById('posts-container');
    if (!container) return;

    try {
        container.innerHTML = '<p class="blog-loading">Cargando reflexiones...</p>';
        const posts = await loadBlogPosts();

        if (!posts || posts.length === 0) {
            container.innerHTML = '<p class="blog-empty">Próximamente más reflexiones de la Cueva...</p>';
            return;
        }

        container.innerHTML = '';
        posts.forEach(post => {
            container.appendChild(renderBlogPost(post));
        });

        attachBlogPostListeners(posts);
    } catch (error) {
        console.error('Error blog:', error);
        container.innerHTML = '<p class="blog-error">Error cargando posts</p>';
    }
}

/**
 * Adjuntar event listeners
 */
function attachBlogPostListeners(posts) {
    document.querySelectorAll('.blog-read-more').forEach(button => {
        button.addEventListener('click', (e) => {
            const postId = parseInt(e.target.getAttribute('data-post-id'));
            const post = posts.find(p => p.id === postId);
            if (post) showBlogPostModal(post);
        });
    });
}

/**
 * Mostrar modal con post completo
 */
function showBlogPostModal(post) {
    const modal = document.createElement('div');
    modal.className = 'blog-post-modal';
    modal.innerHTML = `
        <div class="blog-post-modal-content">
            <button class="blog-modal-close" aria-label="Cerrar">×</button>
            <div class="blog-post-modal-header">
                <h2>${escapeHtml(post.titulo)}</h2>
                <div class="blog-post-modal-meta">
                    <span>✍️ ${escapeHtml(post.autor)}</span>
                    <span>📅 ${formatDate(post.fecha)}</span>
                    <span>#${escapeHtml(post.categoria)}</span>
                </div>
            </div>
            <div class="blog-post-modal-body">
                ${escapeHtml(post.contenido).replace(/\n/g, '<br>')}
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('blog-post-modal-open'), 10);

    const closeButton = modal.querySelector('.blog-modal-close');
    const closeModal = () => {
        modal.classList.remove('blog-post-modal-open');
        setTimeout(() => modal.remove(), 300);
    };

    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
}

/**
 * Escapar HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    try {
        return new Intl.DateTimeFormat('es-MX', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(dateString));
    } catch {
        return dateString;
    }
}

/**
 * Obtener parámetro de URL
 */
function getURLParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Mostrar post automáticamente si viene con parámetro ?post=ID
 */
async function checkAndShowPostFromURL() {
    const postId = getURLParameter('post');
    if (postId) {
        try {
            const posts = await loadBlogPosts();
            const post = posts.find(p => p.id === parseInt(postId));
            if (post) {
                showBlogPostModal(post);
            }
        } catch (error) {
            console.error('Error mostrando post de URL:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderAllBlogPosts();
    checkAndShowPostFromURL();
});
