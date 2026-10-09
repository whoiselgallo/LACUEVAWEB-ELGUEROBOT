/**
 * API: TEST DE CONEXIÓN A BASE DE DATOS (NEON POSTGRESQL NATIVO)
 * Endpoint: /api/api-db-test.php -> /api/db-test.js
 */

module.exports = async function handler(req, res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    const startTime = Date.now();
    const dbUrl = process.env.DATABASE_URL || process.env.DATABASE_URL_UNPOOLED || '';

    if (!dbUrl || !dbUrl.includes('@')) {
        return res.status(200).json({
            success: false,
            connected: false,
            error: 'DATABASE_URL no configurada en las variables de entorno de Vercel.',
            latency_ms: 0
        });
    }

    try {
        const match = dbUrl.match(/postgresql:\/\/([^:]+):([^@]+)@([^/]+)\/(.+)/);
        if (!match) {
            throw new Error('Formato de DATABASE_URL inválido.');
        }

        const [, user, pass, host, db] = match;
        const cleanHost = host.replace(/-pooler\./, '.');
        const sqlEndpoint = `https://${cleanHost}/sql`;

        const testRes = await fetch(sqlEndpoint, {
            method: 'POST',
            headers: {
                'Neon-Connection-String': dbUrl,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                query: "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
            }),
            signal: AbortSignal.timeout(5000)
        });

        const latency = Date.now() - startTime;

        if (testRes.ok) {
            const data = await testRes.json();
            const tables = (data.rows || []).map(r => r.table_name);
            return res.status(200).json({
                success: true,
                connected: true,
                driver: 'PostgreSQL (Neon Serverless)',
                host: cleanHost,
                database: db.split('?')[0],
                latency_ms: latency,
                tables_count: tables.length,
                tables: tables,
                message: `Conexión exitosa con Neon PostgreSQL (${latency}ms)`
            });
        } else {
            const errData = await testRes.json().catch(() => ({}));
            throw new Error(errData.message || `HTTP ${testRes.status} desde Neon`);
        }
    } catch (err) {
        return res.status(200).json({
            success: false,
            connected: false,
            error: err.message,
            latency_ms: Date.now() - startTime
        });
    }
};
