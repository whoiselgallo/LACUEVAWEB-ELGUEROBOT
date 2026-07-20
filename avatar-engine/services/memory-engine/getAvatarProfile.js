import fs from 'fs';
import path from 'path';

export const getAvatarProfile = async (name) => {
    const profilePath = path.join('storage', 'avatars', name, 'profile.json');

    if (!fs.existsSync(profilePath)) {
        throw new Error(`No existe el avatar "${name}".`);
    }

    const profile = JSON.parse(fs.readFileSync(profilePath));

    // Convertir buffer base64 a buffer real
    profile.avatarBaseBuffer = fs.readFileSync(
        path.join('storage', 'avatars', name, 'base.png')
    );

    return profile;
};

// integracion del avatar memory engine con el almacenamiento de avatares MySQL
import db from '../../config/database.js';
import fs from 'fs';
import path from 'path';

export const getAvatarProfile = async (name) => {
    const [rows] = await db.query(
        `SELECT * FROM avatars WHERE name = ? LIMIT 1`,
        [name]
    );

    if (!rows.length) throw new Error(`Avatar "${name}" no existe.`);

    const profile = rows[0];

    const basePath = path.join('storage', 'avatars', name, 'base.png');
    const avatarBaseBuffer = fs.readFileSync(basePath);

    const [versions] = await db.query(
        `SELECT * FROM avatar_versions WHERE avatar_name = ? ORDER BY created_at DESC`,
        [name]
    );

    return {
        ...profile,
        traits: JSON.parse(profile.traits),
        embeddings: JSON.parse(profile.embeddings),
        avatarBaseBuffer,
        versions
    };
};
