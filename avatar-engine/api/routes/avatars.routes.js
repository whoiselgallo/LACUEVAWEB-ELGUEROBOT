import { Router } from 'express';
import { createAvatar, getAvatar, getAllAvatars } from '../controllers/AvatarController.js';

const router = Router();

// Crear avatar base desde foto real
router.post('/', createAvatar);

// Obtener todos los avatares guardados
router.get('/', getAllAvatars);

// Obtener perfil de avatar
router.get('/:name', getAvatar);

export default router;
