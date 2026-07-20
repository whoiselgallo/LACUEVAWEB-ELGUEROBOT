import fs from 'fs';
import path from 'path';
import { generateEmbeddings } from './generateEmbeddings.js';

export const saveAvatarBase = async (name, avatarBuffer, traits) => {
    const folder = path.join('storage', 'avatars', name);
    if (!fs.existsSync(folder)) fs.mkdirSync(folder, { recursive: true });

    const basePath = path.join(folder, 'base.png');
    fs.writeFileSync(basePath, avatarBuffer);

    const embeddings = await generateEmbeddings(avatarBuffer);

    const profile = {
        name,
        avatarBaseURL: `/storage/avatars/${name}/base.png`,
        avatarBaseBuffer: avatarBuffer,
        embeddings,
        traits,
        versions: []
    };

    const profilePath = path.join(folder, 'profile.json');
    fs.writeFileSync(profilePath, JSON.stringify(profile, null, 2));

    return profile;
};

// integracion del avatar memory engine con el almacenamiento de avatares MySQL
import db from '../../config/database.js';
import fs from 'fs';
import path from 'path';
import { generateEmbeddings } from './generateEmbeddings.js';

export const saveAvatarBase = async (name, avatarBuffer, traits) => {
    const folder = path.join('storage', 'avatars', name);
    if (!fs.existsSync(folder)) fs.mkdirSync(folder, { recursive: true });

    const basePath = path.join(folder, 'base.png');
    fs.writeFileSync(basePath, avatarBuffer);

    const url = `/storage/avatars/${name}/base.png`;
    const embeddings = await generateEmbeddings(avatarBuffer);

    await db.query(
        `INSERT INTO avatars (name, avatar_base_url, traits, embeddings)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
         avatar_base_url = VALUES(avatar_base_url),
         traits = VALUES(traits),
         embeddings = VALUES(embeddings)`,
        [name, url, JSON.stringify(traits), JSON.stringify(embeddings)]
    );

    return {
        name,
        avatar_base_url: url,
        traits,
        embeddings
    };
};
