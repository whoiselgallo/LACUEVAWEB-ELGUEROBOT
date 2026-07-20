import fs from 'fs';
import path from 'path';

export const saveVersion = async (name, action, avatarBuffer) => {
    const folder = path.join('storage', 'avatars', name);
    const profilePath = path.join(folder, 'profile.json');

    if (!fs.existsSync(profilePath)) {
        throw new Error(`No existe el avatar "${name}".`);
    }

    const profile = JSON.parse(fs.readFileSync(profilePath));

    const versionId = `${action.replace(/\s+/g, '_')}_${Date.now()}`;
    const versionPath = path.join(folder, `${versionId}.png`);

    fs.writeFileSync(versionPath, avatarBuffer);

    const versionData = {
        id: versionId,
        action,
        url: `/storage/avatars/${name}/${versionId}.png`,
        createdAt: new Date().toISOString()
    };

    profile.versions.push(versionData);

    fs.writeFileSync(profilePath, JSON.stringify(profile, null, 2));

    return versionData;
};

// integracion del avatar memory engine con el almacenamiento de avatares MySQL
import db from '../../config/database.js';
import fs from 'fs';
import path from 'path';

export const saveVersion = async (name, action, avatarBuffer) => {
    const folder = path.join('storage', 'avatars', name);
    const versionId = `${action.replace(/\s+/g, '_')}_${Date.now()}`;
    const versionPath = path.join(folder, `${versionId}.png`);

    fs.writeFileSync(versionPath, avatarBuffer);

    const url = `/storage/avatars/${name}/${versionId}.png`;

    await db.query(
        `INSERT INTO avatar_versions (avatar_name, action, url)
         VALUES (?, ?, ?)`,
        [name, action, url]
    );

    return {
        id: versionId,
        action,
        url
    };
};
