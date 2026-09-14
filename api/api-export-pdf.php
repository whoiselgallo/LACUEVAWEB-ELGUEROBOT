<?php
/**
 * EXPORTADOR DE DOCUMENTOS PDF PRO - LA CUEVA DEL GÜERO
 * Formatos disponibles: Guión Técnico, Cue Cards de Set, Escaleta Ejecutiva
 * Endpoint: /api/api-export-pdf.php
 */

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

$tipo     = sanitize_input($_REQUEST['tipo'] ?? 'guion');
$invitado = sanitize_input($_REQUEST['invitado'] ?? 'Invitado Especial');
$episodio = sanitize_input($_REQUEST['episodio'] ?? 'Temporada 2');
$content  = $_REQUEST['content'] ?? '';

// Títulos y metadatos por tipo
$titulos = [
    'guion'     => 'GUIÓN TÉCNICO BROADCAST',
    'cuecards'  => 'CUE CARDS DE CABINA Y SET',
    'escaleta'  => 'ESCALETA TÉCNICA Y PRODUCCIÓN EJECUTIVA',
    'tema-blog' => 'PROPUESTA EDITORIAL Y TEMA PARA BLOG',
    'tema_blog' => 'PROPUESTA EDITORIAL Y TEMA PARA BLOG'
];
$docTitulo = $titulos[$tipo] ?? 'DOCUMENTO DE PRODUCCIÓN';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($docTitulo) ?> - <?= htmlspecialchars($invitado) ?> | La Cueva del Güero</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --neon-magenta: #FF00FF;
            --neon-cyan: #00FFFF;
            --dark-bg: #0b0b14;
            --card-bg: #141424;
            --text-main: #f0f0f5;
            --text-muted: #9a9ab0;
            --border-color: rgba(255, 0, 255, 0.3);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--dark-bg);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            padding: 30px;
            line-height: 1.6;
        }

        /* HEADER DEL DOCUMENTO */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--neon-cyan);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand-block h1 {
            font-size: 1.8rem;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .brand-block h1 span { color: var(--neon-magenta); }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--neon-cyan);
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .meta-block {
            text-align: right;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .meta-badge {
            display: inline-block;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        /* ESTILOS ESPECÍFICOS SEGÚN EL TIPO DE DOCUMENTO */

        /* 1. GUIÓN TÉCNICO (Estilo Broadcast / Guión cinematográfico) */
        <?php if ($tipo === 'guion'): ?>
        .doc-body {
            font-family: 'Courier Prime', monospace;
            background: #fff;
            color: #111;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 25px rgba(0,0,0,0.5);
            line-height: 1.5;
            white-space: pre-wrap;
            font-size: 0.95rem;
        }
        <?php endif; ?>

        /* 2. CUE CARDS (Tarjetas de Mano para Set Físico) */
        <?php if ($tipo === 'cuecards'): ?>
        body { background: #1a1a2e; }
        .cuecards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
        }
        .cue-card {
            background: #fff;
            color: #111;
            border: 3px solid var(--neon-magenta);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.4);
            min-height: 240px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .cue-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
        }
        .cue-body {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.4;
            color: #000;
            flex-grow: 1;
        }
        .cue-footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px dashed #ccc;
            font-size: 0.75rem;
            color: #666;
            text-align: right;
        }
        <?php endif; ?>

        /* 3. ESCALETA TÉCNICA (Producción Ejecutiva) */
        <?php if ($tipo === 'escaleta'): ?>
        .escaleta-wrapper {
            background: #fff;
            color: #111;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 25px rgba(0,0,0,0.5);
        }
        .escaleta-content {
            white-space: pre-wrap;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        <?php endif; ?>

        /* BOTONES FLOTANTES DE ACCIÓN */
        .floating-bar {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: flex;
            gap: 12px;
            z-index: 1000;
        }

        .btn-print {
            background: var(--neon-magenta);
            color: #fff;
            border: none;
            padding: 14px 24px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 0, 255, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 0, 255, 0.6);
        }

        .btn-close {
            background: #333;
            color: #fff;
            border: none;
            padding: 14px 20px;
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* REGLAS DE IMPRESIÓN Y EXPORTACIÓN A PDF */
        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                padding: 0 !important;
            }
            .floating-bar { display: none !important; }
            .doc-header {
                border-bottom: 2px solid #000 !important;
                margin-bottom: 20px !important;
            }
            .brand-block h1 { color: #000 !important; }
            .brand-block h1 span { color: #000 !important; }
            .meta-badge {
                border-color: #000 !important;
                color: #000 !important;
            }
            .doc-body, .escaleta-wrapper {
                box-shadow: none !important;
                padding: 0 !important;
            }
            <?php if ($tipo === 'cuecards'): ?>
            @page { size: landscape; margin: 10mm; }
            .cuecards-container {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 15px !important;
            }
            .cue-card {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
            <?php else: ?>
            @page { size: portrait; margin: 15mm; }
            <?php endif; ?>
        }
    </style>
</head>
<body>

    <!-- CABECERA DE PRODUCCIÓN -->
    <header class="doc-header">
        <div class="brand-block">
            <h1>LA CUEVA DEL <span>GÜERO</span></h1>
            <div class="brand-subtitle"><?= htmlspecialchars($docTitulo) ?></div>
        </div>
        <div class="meta-block">
            <div class="meta-badge"><i class="fa-solid fa-microphone-lines"></i> <?= htmlspecialchars($episodio) ?></div>
            <div><strong>Invitado:</strong> <?= htmlspecialchars($invitado) ?></div>
            <div><strong>Fecha:</strong> <?= date('d/m/Y - H:i') ?> | Mexicali, B.C.</div>
        </div>
    </header>

    <!-- CUERPO PRINCIPAL DEL DOCUMENTO -->
    <main>
        <?php if ($tipo === 'cuecards'): ?>
            <div class="cuecards-container">
                <?php
                // Dividir el contenido en tarjetas si es posible
                $bloques = preg_split('/\n{2,}|(?=TARJETA|\bCARD\b|^\d+\.)/m', $content);
                $bloques = array_filter(array_map('trim', $bloques));
                if (empty($bloques)) {
                    $bloques = [
                        "1. INTRODUCCIÓN Y APERTURA:\nPregunta de quiebre sobre su origen y primer gran obstáculo.",
                        "2. EL MOMENTO DECISIVO:\n¿Qué pasó en el punto más crítico de tu carrera?",
                        "3. TEMA INCÓMODO:\nPregunta directa y sin censura sobre el desmadre.",
                        "4. CIERRE Y MENSAJE:\n¿Qué le dirías a quien viene desde abajo como tú?"
                    ];
                }
                $cardNum = 1;
                foreach ($bloques as $b):
                ?>
                <div class="cue-card">
                    <div class="cue-header">
                        <span><i class="fa-solid fa-id-card"></i> TARJETA #<?= $cardNum ?></span>
                        <span>LA CUEVA DEL GÜERO</span>
                    </div>
                    <div class="cue-body"><?= nl2br(htmlspecialchars($b)) ?></div>
                    <div class="cue-footer">Conducción: El Güero & El Junior • Set en Vivo</div>
                </div>
                <?php $cardNum++; endforeach; ?>
            </div>

        <?php elseif ($tipo === 'escaleta'): ?>
            <div class="escaleta-wrapper">
                <div class="escaleta-content"><?= htmlspecialchars($content ?: "ESCALETA TÉCNICA EN DESARROLLO PARA {$invitado}.") ?></div>
            </div>

        <?php elseif ($tipo === 'tema-blog' || $tipo === 'tema_blog'): ?>
            <div class="doc-body" style="border-left-color: var(--neon-magenta); font-family: 'Outfit', sans-serif; font-size: 1rem; line-height: 1.7; background: #0c0c18;">
                <div style="margin-bottom: 20px; padding: 15px 20px; background: rgba(255,0,255,0.08); border: 1px solid rgba(255,0,255,0.3); border-radius: 8px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--neon-magenta); text-transform: uppercase; letter-spacing: 1px;">Propuesta Editorial Semidesarrollada</span>
                    <h2 style="color: #fff; margin: 6px 0; font-size: 1.4rem;"><?= htmlspecialchars($_REQUEST['titulo_tema'] ?? 'Tema Central para Blog') ?></h2>
                    <p style="color: var(--neon-cyan); margin: 0; font-size: 0.95rem;"><strong>Tesis:</strong> <?= htmlspecialchars($_REQUEST['tesis'] ?? 'Ángulo editorial de peso') ?></p>
                </div>
                <div style="white-space: pre-wrap; color: #ececf5;"><?= htmlspecialchars($content) ?></div>
            </div>

        <?php else: ?>
            <div class="doc-body"><?= htmlspecialchars($content ?: "GUIÓN TÉCNICO EN DESARROLLO PARA {$invitado}.") ?></div>
        <?php endif; ?>
    </main>

    <!-- BARRA FLOTANTE DE ACCIONES -->
    <div class="floating-bar">
        <button class="btn-print" onclick="window.print()">
            <i class="fa-solid fa-file-pdf"></i> Guardar como PDF / Imprimir
        </button>
        <a href="javascript:window.close()" class="btn-close">
            <i class="fa-solid fa-xmark"></i> Cerrar
        </a>
    </div>

</body>
</html>
