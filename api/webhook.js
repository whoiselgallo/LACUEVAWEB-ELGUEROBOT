/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API: WEBHOOK & CENTRO DE AVISOS EN VIVO (LA CUEVA DEL GÜERO PRO)
 * Endpoint Nativo Vercel Serverless Function (Node.js)
 * Atiende: /api/api-webhook.php, /api/api-webhook y /api/webhook
 * ═════════════════════════════════════════════════════════════════════════════════
 */

const fs = require('fs');
const path = require('path');

// Archivos de almacenamiento (resilientes entre local y entorno Vercel Serverless)
const LOCAL_STORAGE_DIR = path.resolve(__dirname, '../images/formularios');
const TMP_STORAGE_DIR = '/tmp';

const LOCAL_CONFIG_FILE = path.join(LOCAL_STORAGE_DIR, 'webhook_config.json');
const TMP_CONFIG_FILE = path.join(TMP_STORAGE_DIR, 'webhook_config.json');

const LOCAL_EVENTOS_FILE = path.join(LOCAL_STORAGE_DIR, 'eventos_webhook.json');
const TMP_EVENTOS_FILE = path.join(TMP_STORAGE_DIR, 'eventos_webhook.json');

// Memoria volátil en caliente para la instancia
let inMemoryConfig = {
    webhook_url: process.env.WEBHOOK_NOTIFICACIONES_URL || process.env.DISCORD_WEBHOOK_URL || '',
    discord_webhook: process.env.DISCORD_WEBHOOK_URL || '',
    activo: true
};

let inMemoryEventos = null;

// Helper para leer JSON con múltiples fallbacks seguros
function safeReadJson(filePath, defaultValue) {
    try {
        if (fs.existsSync(filePath)) {
            const raw = fs.readFileSync(filePath, 'utf8');
            return JSON.parse(raw);
        }
    } catch (_) {}
    return defaultValue;
}

// Helper para escribir JSON de forma resiliente
function safeWriteJson(filePath, data) {
    try {
        const dir = path.dirname(filePath);
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }
        fs.writeFileSync(filePath, JSON.stringify(data, null, 2), 'utf8');
        return true;
    } catch (_) {
        return false;
    }
}

// Obtener Configuración
function obtenerConfig() {
    let config = { ...inMemoryConfig };

    // Intentar leer de /tmp primero (cambios en runtime en Vercel)
    const tmpCfg = safeReadJson(TMP_CONFIG_FILE, null);
    if (tmpCfg && typeof tmpCfg === 'object') {
        config = { ...config, ...tmpCfg };
    } else {
        // Intentar leer de repo local
        const localCfg = safeReadJson(LOCAL_CONFIG_FILE, null);
        if (localCfg && typeof localCfg === 'object') {
            config = {
                ...config,
                webhook_url: localCfg.webhook_url || localCfg.url || config.webhook_url,
                discord_webhook: localCfg.discord_webhook || config.discord_webhook,
                activo: localCfg.activo !== undefined ? localCfg.activo : (localCfg.habilitado !== undefined ? localCfg.habilitado : true)
            };
        }
    }

    inMemoryConfig = config;
    return config;
}

// Guardar Configuración
function guardarConfig(nuevosDatos) {
    const current = obtenerConfig();
    const updated = {
        ...current,
        ...nuevosDatos,
        fecha_actualizacion: new Date().toISOString()
    };

    inMemoryConfig = updated;

    // Intentar persistir en /tmp (funciona siempre en Lambda/Vercel)
    safeWriteJson(TMP_CONFIG_FILE, updated);
    // Intentar persistir en local repo si el filesystem lo permite
    safeWriteJson(LOCAL_CONFIG_FILE, updated);

    return updated;
}

// Obtener Historial de Eventos
function obtenerEventos(limit = 50) {
    if (inMemoryEventos && Array.isArray(inMemoryEventos)) {
        return inMemoryEventos.slice(0, limit);
    }

    // Intentar de /tmp
    let eventos = safeReadJson(TMP_EVENTOS_FILE, null);
    if (!eventos || !Array.isArray(eventos)) {
        // Intentar de local repo
        eventos = safeReadJson(LOCAL_EVENTOS_FILE, []);
    }

    if (!Array.isArray(eventos) || eventos.length === 0) {
        // Evento semilla por defecto
        eventos = [
            {
                id: 'EVT-SYS-001',
                tipo: 'sistema_iniciado',
                titulo: '🚀 Monitor de Webhooks y Centro de Avisos Activo',
                icono: 'fa-satellite-dish',
                color: '#00FFFF',
                timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
                origen: 'sistema_cueva',
                datos: {
                    estado: 'Monitoreo en vivo activado',
                    plataforma: 'La Cueva del Güero PRO'
                },
                leido: false
            }
        ];
    }

    inMemoryEventos = eventos;
    return eventos.slice(0, limit);
}

// Guardar Evento
function guardarEvento(evento) {
    const lista = obtenerEventos(100);
    lista.unshift(evento);
    const capped = lista.slice(0, 80);
    inMemoryEventos = capped;

    safeWriteJson(TMP_EVENTOS_FILE, capped);
    safeWriteJson(LOCAL_EVENTOS_FILE, capped);
    return capped;
}

// Metadatos por tipo de evento
const META_EVENTOS = {
    test_webhook: {
        titulo: '🧪 Webhook de Prueba - La Cueva del Güero',
        icono: 'fa-paper-plane',
        color: '#00FFFF',
        discord_color: 65535
    },
    entrevista_completada: {
        titulo: '🎉 Entrevista Completada y Confirmada',
        icono: 'fa-clipboard-check',
        color: '#39FF14',
        discord_color: 3800852
    },
    entrevista_error_reportado: {
        titulo: '⚠️ Respuesta Modificada / Error Corregido',
        icono: 'fa-pen-to-square',
        color: '#FFA500',
        discord_color: 16753920
    },
    tracking_actualizado: {
        titulo: '🎯 Avance de Capítulo Actualizado',
        icono: 'fa-satellite-dish',
        color: '#00FFFF',
        discord_color: 65535
    },
    task_kanban: {
        titulo: '📋 Tarea de Producción Actualizada (Kanban)',
        icono: 'fa-list-check',
        color: '#FF00FF',
        discord_color: 16711935
    },
    blog_publicado: {
        titulo: '📰 Nuevo Artículo Publicado en Blog',
        icono: 'fa-newspaper',
        color: '#00E5FF',
        discord_color: 58879
    },
    analitica_actualizada: {
        titulo: '📊 Métricas / Analítica de Redes Sincronizada',
        icono: 'fa-chart-line',
        color: '#39FF14',
        discord_color: 3800852
    },
    decision_votada: {
        titulo: '🗳️ Decisión de Equipo Votada',
        icono: 'fa-check-double',
        color: '#FFD700',
        discord_color: 16766720
    },
    solicitud_correccion_enviada: {
        titulo: '⚠️ Solicitud de Corrección Enviada a Invitado',
        icono: 'fa-triangle-exclamation',
        color: '#FF6600',
        discord_color: 16737792
    },
    correccion_invitado_enviada: {
        titulo: '✍️ Nuevas Respuestas Enviadas por Invitado',
        icono: 'fa-pen-nib',
        color: '#00FFFF',
        discord_color: 65535
    },
    cuestionario_aprobado_produccion: {
        titulo: '✅ Cuestionario Aprobado por Producción',
        icono: 'fa-circle-check',
        color: '#39FF14',
        discord_color: 3800852
    }
};

// Envío HTTP de Webhook con timeout y telemetría
async function enviarWebhookExterno(url, evento, meta) {
    if (!url || !url.startsWith('http')) {
        return {
            success: false,
            code: 400,
            status: 'url_invalida',
            response: 'URL no válida o vacía'
        };
    }

    const isDiscord = url.includes('discord.com/api/webhooks');
    const isSlack = url.includes('hooks.slack.com');

    let payload = {};

    if (isDiscord) {
        const fields = [];
        if (evento.datos && typeof evento.datos === 'object') {
            for (const [k, v] of Object.entries(evento.datos)) {
                    fields.push({
                        name: k.replace(/_/g, ' ').toUpperCase(),
                        value: String(v).substring(0, 1000),
                        inline: true
                    });
            }
        }
        payload = {
            username: 'El Güero Bot 🐾 (La Cueva PRO)',
            avatar_url: 'https://lacuevadelguero.com/images/logotipo.png',
            embeds: [
                {
                    title: evento.titulo,
                    description: `Actividad en vivo registrada en **La Cueva del Güero PRO**.`,
                    color: meta.discord_color || 65535,
                    fields,
                    footer: { text: `ID: ${evento.id} • Origen: ${evento.origen}` },
                    timestamp: new Date().toISOString()
                }
            ]
        };
    } else if (isSlack) {
        let details = '';
        if (evento.datos && typeof evento.datos === 'object') {
            for (const [k, v] of Object.entries(evento.datos)) {
                details += `\n• *${k}:* ${String(v).substring(0, 300)}`;
            }
        }
        payload = {
            text: `🔔 *${evento.titulo}*\n${details}`
        };
    } else {
        // Formato Genérico JSON para TSolutions, Make, Zapier, n8n, etc.
        payload = {
            event: evento.tipo,
            titulo: evento.titulo,
            id: evento.id,
            timestamp: evento.timestamp,
            origen: evento.origen,
            datos: evento.datos || {},
            plataforma: 'La Cueva del Güero PRO'
        };
    }

    const startTime = Date.now();
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 9000);

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'User-Agent': 'LaCuevaDelGuero-Webhook/2.5'
            },
            body: JSON.stringify(payload),
            signal: controller.signal
        });
        clearTimeout(timeoutId);

        const latencyMs = Date.now() - startTime;
        const resText = await res.text();
        const isOk = res.ok;

        return {
            success: isOk,
            code: res.status,
            latency_ms: latencyMs,
            status: isOk ? 'enviado_ok' : `error_http_${res.status}`,
            response: resText.substring(0, 500),
            payload
        };
    } catch (err) {
        const latencyMs = Date.now() - startTime;
        return {
            success: false,
            code: 0,
            latency_ms: latencyMs,
            status: err.name === 'AbortError' ? 'timeout_9s' : `error_${err.message}`,
            response: err.message,
            payload
        };
    }
}

// MANEJADOR PRINCIPAL DE LA SERVERLESS FUNCTION
module.exports = async (req, res) => {
    // Configurar encabezados CORS
    res.setHeader('Content-Type', 'application/json; charset=utf-8');
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    try {
        const urlObj = new URL(req.url, `https://${req.headers.host || 'admin.lacuevadelguero.com'}`);
        const actionParam = urlObj.searchParams.get('action');

        let body = {};
        if (req.body) {
            body = (typeof req.body === 'string') ? (() => { try { return JSON.parse(req.body); } catch (_) { return {}; } })() : req.body;
        } else if (req.method === 'POST') {
            body = await new Promise((resolve) => {
                let data = '';
                req.on('data', chunk => { data += chunk; });
                req.on('end', () => {
                    try { resolve(JSON.parse(data)); } catch (_) { resolve({}); }
                });
                req.on('error', () => resolve({}));
            });
        }
        body = body || {};

        const action = actionParam || body.action || (req.method === 'GET' ? 'listar' : 'disparar');

        // 1. LISTAR EVENTOS
        if (action === 'listar') {
            const limit = parseInt(urlObj.searchParams.get('limit') || '50', 10);
            const eventos = obtenerEventos(limit);
            res.status(200).json({
                status: 'success',
                total: eventos.length,
                eventos
            });
            return;
        }

        // 2. OBTENER CONFIGURACIÓN
        if (action === 'obtener_config') {
            const config = obtenerConfig();
            res.status(200).json({
                status: 'success',
                config
            });
            return;
        }

        // 3. GUARDAR CONFIGURACIÓN
        if (action === 'guardar_config') {
            const webhookUrl = (body.webhook_url || '').trim();
            const activo = body.activo !== undefined ? Boolean(body.activo) : true;

            const updated = guardarConfig({ webhook_url: webhookUrl, activo });

            let testResult = 'no_probado';
            if (webhookUrl) {
                const meta = META_EVENTOS.test_webhook;
                testResult = await enviarWebhookExterno(webhookUrl, {
                    id: `TEST-${Date.now()}`,
                    tipo: 'test_webhook',
                    titulo: meta.titulo,
                    origen: 'dashboard_admin',
                    timestamp: new Date().toISOString(),
                    datos: {
                        mensaje: 'Conexión verificada exitosamente entre La Cueva del Güero PRO y tu canal de avisos en tiempo real.',
                        fecha: new Date().toLocaleString()
                    }
                }, meta);
            }

            res.status(200).json({
                status: 'success',
                message: 'Configuración de webhook guardada con éxito.',
                config: updated,
                test_result: testResult
            });
            return;
        }

        // 4. DISPARAR EVENTO (TRIGGER)
        if (action === 'disparar' || action === 'trigger') {
            const tipo = body.tipo || body.evento || 'aviso_general';
            const datos = body.datos || {};
            const origen = body.origen || 'cliente_web';
            const meta = META_EVENTOS[tipo] || {
                titulo: '📢 Aviso del Sistema',
                icono: 'fa-bell',
                color: '#00FFFF',
                discord_color: 65535
            };

            const eventoId = `EVT-${new Date().toISOString().replace(/\D/g, '').substring(0, 14)}-${Math.floor(Math.random() * 900 + 100)}`;
            const registroEvento = {
                id: eventoId,
                tipo,
                titulo: meta.titulo,
                icono: meta.icono,
                color: meta.color,
                timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
                origen,
                datos,
                leido: false
            };

            guardarEvento(registroEvento);

            // Obtener webhook URL (o usar la enviada dinámicamente en la petición de prueba)
            const config = obtenerConfig();
            const targetUrl = (body.webhook_url && body.webhook_url.trim()) ? body.webhook_url.trim() : (config.webhook_url || config.discord_webhook || '');

            let resultadoEnvio = 'no_configurado';
            if (targetUrl) {
                // Guardar la URL en la configuración si venía en el cuerpo
                if (body.webhook_url && body.webhook_url !== config.webhook_url) {
                    guardarConfig({ webhook_url: body.webhook_url });
                }
                resultadoEnvio = await enviarWebhookExterno(targetUrl, registroEvento, meta);
            }

            res.status(200).json({
                success: true,
                evento: registroEvento,
                webhook_status: resultadoEnvio
            });
            return;
        }

        // 5. MARCAR EVENTOS COMO LEÍDOS
        if (action === 'marcar_leidos') {
            const eventos = obtenerEventos(100);
            eventos.forEach(ev => { ev.leido = true; });
            inMemoryEventos = eventos;
            safeWriteJson(TMP_EVENTOS_FILE, eventos);
            res.status(200).json({
                status: 'success',
                message: 'Eventos marcados como leídos.'
            });
            return;
        }

        // 6. REENVIAR EVENTO ESPECÍFICO
        if (action === 'reenviar') {
            const eventoId = body.evento_id;
            const eventos = obtenerEventos(100);
            const encontrado = eventos.find(e => e.id === eventoId);
            if (!encontrado) {
                res.status(404).json({ status: 'error', error: 'Evento no encontrado' });
                return;
            }

            const config = obtenerConfig();
            const webhookUrl = config.webhook_url || config.discord_webhook;
            if (!webhookUrl) {
                res.status(400).json({ status: 'error', error: 'No hay webhook configurado actualmente' });
                return;
            }

            const meta = META_EVENTOS[encontrado.tipo] || { titulo: encontrado.titulo, discord_color: 65535 };
            const reenvio = await enviarWebhookExterno(webhookUrl, encontrado, meta);

            res.status(200).json({
                status: 'success',
                message: 'Evento reenviado con éxito.',
                telemetry: reenvio
            });
            return;
        }

        res.status(400).json({ status: 'error', error: `Acción '${action}' no reconocida` });
    } catch (err) {
        res.status(200).json({
            status: 'error',
            error: err.message
        });
    }
};
