import { AvatarMemoryEngine } from '../../services/memory-engine/index.js';
import { GenerationService } from '../../services/generation/index.js';
import { postprocessAvatar } from '../../services/postprocess/index.js';

export const generateAction = async (req, res, next) => {
    try {
        const { name } = req.params;
        const { action } = req.body;

        if (!action) {
            return res.status(400).json({
                error: true,
                message: 'Falta el parámetro action.'
            });
        }

        // 1. Obtener perfil del avatar
        const profile = await AvatarMemoryEngine.getAvatarProfile(name);

        // 2. Generar nueva actividad
        const generated = await GenerationService.generateActionAvatar(
            profile.avatarBaseBuffer,
            profile.traits,
            action
        );

        // 3. Postprocesado
        const finalAvatar = await postprocessAvatar(generated.avatar);

        // 4. Guardar versión
        const version = await AvatarMemoryEngine.saveVersion(
            name,
            action,
            finalAvatar
        );

        return res.status(200).json({
            message: `Nueva actividad generada para ${name}`,
            version
        });

    } catch (error) {
        next(error);
    }
};
