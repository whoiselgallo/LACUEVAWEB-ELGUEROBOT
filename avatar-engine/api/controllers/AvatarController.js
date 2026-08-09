import { validateImage } from '../../services/image-validation/index.js';
import { preprocessImage } from '../../services/preprocess/index.js';
import { GenerationService } from '../../services/generation/index.js';
import { postprocessAvatar } from '../../services/postprocess/index.js';
import { AvatarMemoryEngine } from '../../services/memory-engine/index.js';
import db from '../../config/database.js';

// ===============================
// 1. CREAR AVATAR BASE
// ===============================
export const createAvatar = async (req, res, next) => {
    try {
        const { image, traits, name } = req.body;

        if (!image || !name) {
            return res.status(400).json({
                error: true,
                message: 'Faltan parámetros: image, name.'
            });
        }

        const imageBuffer = Buffer.from(image, 'base64');

        // 1. Validación
        const validation = await validateImage(imageBuffer);

        // 2. Preprocesamiento
        const preprocessed = await preprocessImage(imageBuffer, validation.faceData);

        // 3. Generación IA (RunPod)
        const generated = await GenerationService.generateBaseAvatar(preprocessed, traits);

        // 4. Postprocesado
        const finalAvatar = await postprocessAvatar(generated.avatar);

        // 5. Guardar en Memory Engine
        const profile = await AvatarMemoryEngine.saveAvatarBase(
            name,
            finalAvatar,
            traits
        );

        return res.status(200).json({
            message: 'Avatar base generado exitosamente.',
            profile
        });

    } catch (error) {
        next(error);
    }
};

// ===============================
// 2. OBTENER UN AVATAR POR NOMBRE
// ===============================
export const getAvatar = async (req, res) => {
    try {
        const { name } = req.params;

        const [rows] = await db.query(
            'SELECT * FROM avatars WHERE name = ? LIMIT 1',
            [name]
        );

        if (!rows.length) {
            return res.status(404).json({ error: 'Avatar no encontrado.' });
        }

        // Obtener versiones
        const [versions] = await db.query(
            'SELECT * FROM avatar_versions WHERE avatar_name = ? ORDER BY created_at DESC',
            [name]
        );

        return res.json({
            ...rows[0],
            versions
        });

    } catch (error) {
        console.error(error);
        res.status(500).json({ error: 'Error al obtener avatar.' });
    }
};

// ===============================
// 3. OBTENER TODOS LOS AVATARES
// ===============================
export const getAllAvatars = async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT name, avatar_base_url FROM avatars ORDER BY created_at DESC'
        );

        res.json({ avatars: rows });

    } catch (error) {
        console.error('Error al obtener avatares:', error);
        res.status(500).json({ error: 'Error al obtener avatares.' });
    }
};
