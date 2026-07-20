import express from 'express';
import cors from 'cors';
import rateLimit from 'express-rate-limit';
import { errorHandler } from './middlewares/errorHandler.js';
import avatarRoutes from './routes/avatars.routes.js';
import actionRoutes from './routes/actions.routes.js';
import systemRoutes from './routes/system.routes.js';

const app = express();

// Middlewares globales
app.use(cors());
app.use(express.json({ limit: '10mb' }));

// Rate limiting básico
app.use(
    rateLimit({
        windowMs: 60 * 1000,
        max: 60,
        message: 'Demasiadas solicitudes, intenta más tarde.'
    })
);

// Rutas principales
app.use('/api/v1/avatars', avatarRoutes);
app.use('/api/v1/actions', actionRoutes);
app.use('/api/v1/system', systemRoutes);

// Manejo de errores global
app.use(errorHandler);

const PORT = process.env.PORT || 4000;
app.listen(PORT, () => {
    console.log(`🚀 API Avatar Engine corriendo en puerto ${PORT}`);
});
