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
    const btnMenu = document.querySelector('.nav-toggle');
    const menu = document.querySelector('.nav-links');

    if (!btnMenu || !menu) return;

    btnMenu.addEventListener('click', (e) => {
        e.stopPropagation();
        const expanded = btnMenu.getAttribute('aria-expanded') === 'true';
        btnMenu.setAttribute('aria-expanded', !expanded);
        menu.classList.toggle('active');
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !btnMenu.contains(e.target)) {
            menu.classList.remove('active');
            btnMenu.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar menú al hacer clic en un enlace
    const enlaces = menu.querySelectorAll('a');
    enlaces.forEach((enlace) => {
        enlace.addEventListener('click', () => {
            menu.classList.remove('active');
            btnMenu.setAttribute('aria-expanded', 'false');
        });
    });
}

function habilitarDropdowns() {
    const toggles = document.querySelectorAll('.dropdown-toggle');

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const parent = toggle.closest('.dropdown, .nav-item');
            if (!parent) return;
            const menu = parent.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.toggle('dropdown-open');
            }
        });
    });

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.dropdown-open').forEach((menu) => {
            menu.classList.remove('dropdown-open');
        });
    });

    // Cerrar dropdowns con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.dropdown-open').forEach((menu) => {
                menu.classList.remove('dropdown-open');
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

    // Cargar Auto-Sync de YouTube/Spotify y Galería Pública
    cargarEpisodiosAutoSync();
    cargarGaleriaPublica();
});

/* ============================================================
   SINCRONIZACIÓN AUTOMÁTICA DE CANALES (YOUTUBE & SPOTIFY) & GALERÍA
   ============================================================ */

async function cargarEpisodiosAutoSync() {
    try {
        const resp = await fetch('/api/api-episodes-sync.php');
        const data = await resp.json();

        if (!data.success) return;

        // 1. CONTENEDOR YOUTUBE: VIDEO COMPLETO
        const ytBox = document.querySelector('.contenedor-youtube-video');
        if (ytBox && data.youtube && data.youtube.embed_id) {
            ytBox.innerHTML = `
                <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:12px; box-shadow:0 0 20px rgba(0,255,255,0.2);">
                    <iframe src="https://www.youtube.com/embed/${data.youtube.embed_id}?autoplay=0&rel=0" 
                            title="${data.youtube.titulo || 'Episodio Completo'}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen 
                            style="position:absolute; top:0; left:0; width:100%; height:100%; border-radius:12px;">
                    </iframe>
                </div>
                <h4 style="margin:12px 0 0; color:#00FFFF; font-size:1rem;"><i class="fab fa-youtube"></i> ${data.youtube.titulo}</h4>
            `;
        }

        // 2. CONTENEDOR SPOTIFY: AUDIO & PORTADA DEL CAPÍTULO
        const spBox = document.querySelector('.contenedor-spotify-capitulo');
        if (spBox && data.spotify) {
            const portada = data.spotify.portada_url || 'https://img.youtube.com/vi/ScMzIvxBSi4/maxresdefault.jpg';
            const audioSrc = data.spotify.audio_url || 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';

            spBox.innerHTML = `
                <div style="background:rgba(10,10,18,0.8); border:1px solid var(--neon-magenta); border-radius:16px; padding:18px; display:flex; flex-direction:column; gap:12px;">
                    <img src="${portada}" alt="Portada del Capítulo" style="width:100%; max-height:220px; object-fit:cover; border-radius:12px; box-shadow:0 0 15px rgba(255,0,255,0.3);">
                    <h4 style="margin:0; color:#FF00FF; font-size:1rem;"><i class="fab fa-spotify"></i> ${data.spotify.titulo || 'Audio & Portada Oficial'}</h4>
                    <audio controls style="width:100%; border-radius:30px; filter:invert(1) hue-rotate(90deg);">
                        <source src="${audioSrc}" type="audio/mpeg">
                        Tu navegador no soporta el reproductor de audio.
                    </audio>
                </div>
            `;
        }
    } catch (e) {
        console.error("Error cargando auto-sync de canales:", e);
    }
}

async function cargarGaleriaPublica() {
    try {
        const resp = await fetch('/api/api-galeria.php');
        const data = await resp.json();
        const galleryGrid = document.querySelector('.gallery');

        if (!data.success || !data.fotos || !galleryGrid) return;

        if (data.fotos.length > 0) {
            let html = "";
            data.fotos.forEach((f) => {
                html += `
                    <div class="card-cueva" style="overflow:hidden; border-radius:16px;">
                        <img src="${f.imagen_url}" alt="${f.titulo}" style="width:100%; height:200px; object-fit:cover;">
                        <div style="padding:10px; text-align:center;">
                            <h4 style="margin:0; font-size:0.9rem; color:#00FFFF;">${f.titulo}</h4>
                            <span style="font-size:0.75rem; color:#aaa;">${f.categoria}</span>
                        </div>
                    </div>
                `;
            });
            galleryGrid.innerHTML = html;
        }
    } catch (e) {
        console.error("Error cargando galería:", e);
    }
}