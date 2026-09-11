<?php
$code = getenv('CODE') ?: ($_GET['code'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking de Capítulo | La Cueva del Güero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background: #050508; color: #fff; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; }
        .container { width: 100%; max-width: 800px; background: rgba(20, 10, 35, 0.75); border: 1px solid rgba(255, 0, 127, 0.4); border-radius: 16px; padding: 30px; box-shadow: 0 0 30px rgba(255, 0, 127, 0.2); backdrop-filter: blur(12px); }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo h1 { font-size: 28px; font-weight: 900; letter-spacing: 2px; color: #ffffff; }
        .logo span { color: #ff007f; text-shadow: 0 0 10px #ff007f; }
        .tracking-header { text-align: center; margin-bottom: 30px; }
        .badge-code { display: inline-block; background: rgba(0, 255, 204, 0.1); border: 1px solid #00ffcc; color: #00ffcc; padding: 6px 18px; border-radius: 20px; font-weight: 700; letter-spacing: 1px; margin-top: 10px; }
        .progress-bar-box { background: #1a1026; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 4px; margin: 25px 0; position: relative; }
        .progress-fill { background: linear-gradient(90deg, #ff007f, #00ffcc); height: 16px; border-radius: 6px; width: 65%; transition: width 1s ease; }
        .grid-tasks { display: grid; grid-template-columns: repeat(auto-fit, minMax(220px, 1fr)); gap: 15px; margin-top: 20px; }
        .task-card { background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px; text-align: center; }
        .task-card.icon { font-size: 28px; margin-bottom: 10px; color: #ff007f; }
        .status-badge { display: inline-block; font-size: 12px; padding: 4px 10px; border-radius: 6px; margin-top: 10px; font-weight: 600; }
        .status-listo { background: rgba(0,255,204,0.2); color: #00ffcc; border: 1px solid #00ffcc; }
        .status-proceso { background: rgba(255,193,7,0.2); color: #ffc107; border: 1px solid #ffc107; }
        .status-pendiente { background: rgba(255,255,255,0.1); color: #aaa; border: 1px solid #777; }
        .future-updates { margin-top: 30px; text-align: center; font-size: 13px; color: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>LA CUEVA <span>DEL GÜERO</span></h1>
            <div class="badge-code"><i class="fa-solid fa-fingerprint"></i> CÓDIGO: <span id="text-code"><?php echo htmlspecialchars($code ?: 'CUEUA-749AC3'); ?></span></div>
        </div>

        <div class="tracking-header">
            <h2 style="font-weight:700; color:#ffffff;">Tracking de Pre-Producción y Capítulo</h2>
            <p style="color:#aaa; margin-top:5px;">Hola <strong style="color:#00ffcc;" id="invitado-nombre">Invitado Especial</strong>, aquí puedes ver en vivo como va el proceso de tu video, avatares e integración de ganchos.</p>
        </div>

        <div class="progress-bar-box">
            <div class="progress-fill" id="progress-fill"></div>
        </div>

        <div class="grid-tasks">
            <div class="task-card">
                <div class="icon"><i class="fa-solid fa-robot"></i></div>
                <h4>Storytelling & IA</h4>
                <p style="font-size:12px; color:#aaa; margin-top:5px;">Cuestionario procesado y escaleta generada.</p>
                <div class="status-badge status-listo" id="status-story">✓ Completado</div>
            </div>

            <div class="task-card">
                <div class="icon" style="color:#00ffcc;"><i class="fa-solid fa-user-gear"></i></div>
                <h4>Avatar & Lipsync</h4>
                <p style="font-size:12px; color:#aaa; margin-top:5px;">Modelado virtual y animación digital.</p>
                <div class="status-badge status-proceso" id="status-avatar">⚛ En Proceso</div>
            </div>

            <div class="task-card">
                <div class="icon" style="color:#ff007f;"><i class="fa-solid fa-video"></i></div>
                <h4>Clips & Hooks Virales</h4>
                <p style="font-size:12px; color:#aaa; margin-top:5px;">Edición por texto, muletillas y Shorts.</p>
                <div class="status-badge status-proceso" id="status-hooks">)�� En Proceso</div>
            </div>
        </div>

        <div class="future-updates">
            <i id="sync-time">Sincronizado con la Cueva Pro Engine</i>
        </div>
    </div>
</body>
</html>