import { buildPrompt } from './promptBuilder.js';

export const generateActionAvatar = async (avatarBaseBuffer, traits, action) => {
    const prompt = buildPrompt({
        mode: 'action',
        traits,
        action
    });

    // TODO: integrar con modelo IA con referencia a avatar base
    console.log('🏃 Generando avatar en actividad:', action);

    // Placeholder
    return {
        avatar: avatarBaseBuffer, // reemplazar con imagen generada
        prompt
    };
};

// integracion de runpod modelo python con avatar engine node.js
import axios from 'axios';
import { buildPrompt } from './promptBuilder.js';

const RUNPOD_URL = process.env.RUNPOD_URL;

export const generateActionAvatar = async (avatarBaseBuffer, traits, action) => {
    const prompt = buildPrompt({
        mode: 'action',
        traits,
        action
    });

    const response = await axios.post(`${RUNPOD_URL}/generate-action`, {
        prompt,
        reference: avatarBaseBuffer.toString('base64'),
        steps: 30,
        guidance: 7.5
    });

    const imageBase64 = response.data.image;

    return {
        avatar: Buffer.from(imageBase64, 'base64'),
        prompt
    };
};
