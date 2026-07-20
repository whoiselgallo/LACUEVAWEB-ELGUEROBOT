/* ===============================
   SCRIPTS GENERALES
   VERSIÓN CORREGIDA
   - ✅ Compatibilidad cross-browser
   - ✅ Polyfills incluidos
   - ✅ Protección DOM mejorada
   =============================== */

/* ============================================================
   CARGAR IFRAMES (SPOTIFY, YOUTUBE)
   ============================================================ */

function cargarIframe(id, src) {
    const contenedor = document.getElementById(id);
    if (!contenedor) {
        console.warn(`No se encontró elemento con id: ${id}`);
        return;
    }

    const iframe = document.createElement('iframe');
    iframe.src = src;
    iframe.style.width = '100%';
    iframe.style.height = '380px';
    iframe.style.border = 'none';
    iframe.style.borderRadius = '12px';
    iframe.allow = 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture';

    contenedor.appendChild(iframe);
}

function cargarSpotify(id, playlistId) {
    const src = `https://open.spotify.com/embed/playlist/${playlistId}`;
    cargarIframe(id, src);
}

function cargarYouTube(id, videoId) {
    const src = `https://www.youtube.com/embed/${videoId}`;
    cargarIframe(id, src);
}

/* ============================================================
   EFECTOS VISUALES DE FORMULARIOS
   ============================================================ */

function agregarEfectoFoco() {
    const inputs = document.querySelectorAll('input, textarea, select');

    inputs.forEach((input) => {
        input.addEventListener('focus', () => {
            input.classList.add('focused');
        });

        input.addEventListener('blur', () => {
            input.classList.remove('focused');
        });
    });
}

/* ============================================================
   SMOOTH SCROLL
   ============================================================ */

function habilitarSmoothScroll() {
    const enlaces = document.querySelectorAll('a[href^="#"]');

    enlaces.forEach((enlace) => {
        enlace.addEventListener('click', (e) => {
            const href = enlace.getAttribute('href');
            const target = document.querySelector(href);

            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

/* ============================================================
   MENÚ MÓVIL Y DROPDOWNS
   ============================================================ */

function habilitarMenuMovil() {
    const btnMenu = document.getElementById('btnMenuMovil');
    const menu = document.getElementById('menuMovil');

    if (!btnMenu || !menu) return;

    btnMenu.addEventListener('click', () => {
        menu.classList.toggle('active');
    });

    // Cerrar menú al hacer clic en un enlace
    const enlaces = menu.querySelectorAll('a');
    enlaces.forEach((enlace) => {
        enlace.addEventListener('click', () => {
            menu.classList.remove('active');
        });
    });
}

function habilitarDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (!toggle || !menu) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('active');
        });
    });

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.active').forEach((menu) => {
            menu.classList.remove('active');
        });
    });

    // Cerrar dropdowns con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.active').forEach((menu) => {
                menu.classList.remove('active');
            });
        }
    });
}

/* ============================================================
   POLYFILLS PARA COMPATIBILIDAD
   ============================================================ */

// Polyfill para Object.fromEntries (IE 11)
if (!Object.fromEntries) {
    Object.fromEntries = function (iterable) {
        return [...iterable].reduce((obj, [key, val]) => {
            obj[key] = val;
            return obj;
        }, {});
    };
}

// Polyfill para Array.prototype.includes (IE 11)
if (!Array.prototype.includes) {
    Object.defineProperty(Array.prototype, 'includes', {
        value: function (searchElement, fromIndex) {
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }
            const O = Object(this);
            const len = parseInt(O.length) || 0;
            if (len === 0) {
                return false;
            }
            const n = parseInt(fromIndex) || 0;
            let k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);
            while (k < len) {
                if (O[k] === searchElement) {
                    return true;
                }
                k++;
            }
            return false;
        }
    });
}

/* ============================================================
   INICIALIZACIÓN
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    console.log('✓ [SCRIPTS] Módulo general cargado');

    // Habilitar efectos
    agregarEfectoFoco();
    habilitarSmoothScroll();
    habilitarMenuMovil();
    habilitarDropdowns();
});