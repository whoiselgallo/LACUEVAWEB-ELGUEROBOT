import { Router } from 'express';
import { systemStatus } from '../controllers/SystemController.js';

const router = Router();

// Endpoint interno para monitoreo
router.get('/status', systemStatus);

export default router;
