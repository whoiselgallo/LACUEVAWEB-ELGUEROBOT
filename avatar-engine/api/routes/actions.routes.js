import { Router } from 'express';
import { generateAction } from '../controllers/ActionController.js';

const router = Router();

// Generar nueva actividad del avatar
router.post('/:name', generateAction);

export default router;
