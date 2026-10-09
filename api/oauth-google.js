/**
 * OAUTH INITIATOR: GOOGLE DRIVE & YOUTUBE EN NODE.JS NATIVO
 * Endpoint: /api/auth-google.php -> /api/auth-google.js
 */

module.exports = async function handler(req, res) {
    const clientId = process.env.GOOGLE_CLIENT_ID || '1002821313945-p639cinbousa83r5sqjrkt386tsqdovp.apps.googleusercontent.com';
    const host = req.headers.host || 'lacuevadelguero.com';
    const protocol = req.headers['x-forwarded-proto'] || 'https';
    const redirectUri = `${protocol}://${host}/api/auth-google-callback.php`;

    const scopes = [
        'https://www.googleapis.com/auth/youtube.readonly',
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/drive'
    ];

    const params = new URLSearchParams({
        client_id: clientId,
        redirect_uri: redirectUri,
        response_type: 'code',
        scope: scopes.join(' '),
        access_type: 'offline',
        prompt: 'consent'
    });

    const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?${params.toString()}`;

    res.writeHead(302, { Location: authUrl });
    res.end();
};
