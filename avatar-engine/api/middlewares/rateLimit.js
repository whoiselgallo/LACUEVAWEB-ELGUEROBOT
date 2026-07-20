// rateLimit.js — Middleware de limitación de solicitudes
// Compatible con Hostinger, VPS, Render, Vercel o cualquier backend Node.

const buckets = new Map();

/**
 * rateLimit({
 *   windowMs: 60000,     // 1 minuto
 *   max: 60,             // 60 solicitudes por ventana
 *   message: "Demasiadas solicitudes"
 * })
 */
export const rateLimit = ({ windowMs, max, message }) => {
    return (req, res, next) => {
        const ip = req.ip || req.connection.remoteAddress;

        const now = Date.now();
        const bucket = buckets.get(ip) || { count: 0, start: now };

        // Reiniciar ventana si expiró
        if (now - bucket.start > windowMs) {
            bucket.count = 0;
            bucket.start = now;
        }

        bucket.count++;
        buckets.set(ip, bucket);

        if (bucket.count > max) {
            return res.status(429).json({
                error: true,
                message: message || "Demasiadas solicitudes, intenta más tarde."
            });
        }

        next();
    };
};
