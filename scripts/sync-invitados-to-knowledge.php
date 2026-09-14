<?php
require_once __DIR__ . '/../config/config.php';

try {
    $db = db_connect();
    echo "Conectado a Neon.tech...\n";

    // 1. Obtener los 8 invitados
    $stmt = $db->query("SELECT * FROM invitados ORDER BY id ASC");
    $invitados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Encontrados " . count($invitados) . " invitados en la tabla 'invitados'.\n";

    $insertStmt = $db->prepare("
        INSERT INTO knowledge_base (nombre, tipo, storytelling, created_at, updated_at)
        VALUES (?, 'storytelling', ?, ?, CURRENT_TIMESTAMP)
    ");

    $checkStmt = $db->prepare("SELECT id FROM knowledge_base WHERE nombre = ? AND tipo = 'storytelling'");

    foreach ($invitados as $inv) {
        $checkStmt->execute([$inv['nombre']]);
        if ($checkStmt->fetch()) {
            echo "-> '{$inv['nombre']}' ya existe en knowledge_base.\n";
            continue;
        }

        // Construir JSON de storytelling enriquecido para el dashboard
        $storyData = [
            'escaleta' => "### ESCALETA DE PRODUCCIÓN - LA CUEVA DEL GÜERO\n" .
                          "**Invitado:** {$inv['nombre']}\n" .
                          "**Ocupación:** " . ($inv['ocupacion'] ?? 'Invitado Especial') . "\n" .
                          "**Barrio:** " . ($inv['barrio'] ?? 'Mexicali') . "\n\n" .
                          "#### BLOQUE 1: EL ORIGEN DEL BARRIO\n" .
                          ($inv['trayectoria'] ?? 'Preguntas de inicio sobre el barrio y raíces.') . "\n\n" .
                          "#### BLOQUE 2: LOS GOLPES DE LA VIDA\n" .
                          ($inv['herida'] ?? 'Experiencias de superación y momentos difíciles.') . "\n\n" .
                          "#### BLOQUE 3: LA ANÉCDOTA Y CIERRE\n" .
                          ($inv['incomodo'] ?? 'Confesión y legado.'),

            'guion' => "### GUIÓN PARA EL GÜERO Y EL JUNIOR\n" .
                       "**Invitado:** {$inv['nombre']}\n\n" .
                       "**[00:00] EL GÜERO:** ¡Qué tranza, mi gente de La Cueva! Hoy tenemos en la mesa a un compa bien pesado de " . ($inv['barrio'] ?? 'la cuadra') . ": {$inv['nombre']}.\n" .
                       "**[01:15] EL JUNIOR:** ¡A huevo, carnal! Platícanos carnal, ¿cómo te la rifabas de morro antes de estar en " . ($inv['ocupacion'] ?? 'tu jale actual') . "?\n\n" .
                       "**[05:00] PREGUNTA PICANTE:** " . ($inv['incomodo'] ?? 'Cuéntanos eso que casi nadie sabe de ti.'),

            'cue_cards' => "### CUE CARDS PARA EL SET\n" .
                           "• **Invitado:** {$inv['nombre']}\n" .
                           "• **Ocupación:** " . ($inv['ocupacion'] ?? '') . "\n" .
                           "• **Barrio:** " . ($inv['barrio'] ?? '') . "\n" .
                           "• **Gustos:** " . ($inv['gustos'] ?? 'Música urbana') . "\n" .
                           "• **Herida:** " . substr($inv['herida'] ?? '', 0, 100) . "...",

            'curaduria' => [
                'nivel' => ($inv['id'] % 2 === 0) ? 'ALTO' : 'MEDIO',
                'badge' => ($inv['id'] % 2 === 0) ? '🟢 NIVEL ALTO' : '🟡 NIVEL MEDIO',
                'formato' => ($inv['id'] % 2 === 0) ? 'Invitado Principal al Canal (40+ min)' : 'Entrevista Corta / Segmento (10 min)',
                'color' => ($inv['id'] % 2 === 0) ? '#39FF14' : '#00FFFF',
                'razon' => ($inv['ocupacion'] ?? 'Invitado') . ' - ' . ($inv['barrio'] ?? 'Mexicali')
            ]
        ];

        $jsonStr = json_encode($storyData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $insertStmt->execute([$inv['nombre'], $jsonStr, $inv['created_at']]);
        echo "✓ Insertado en knowledge_base: {$inv['nombre']}\n";
    }

    echo "\nSincronización completada exitosamente en Neon.tech!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
